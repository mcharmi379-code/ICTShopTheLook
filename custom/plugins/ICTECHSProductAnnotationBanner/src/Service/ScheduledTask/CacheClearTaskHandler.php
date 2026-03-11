<?php

declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Service\ScheduledTask;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CacheClearTask::class)]
class CacheClearTaskHandler extends ScheduledTaskHandler
{
    protected EntityRepository $scheduledTaskRepository;
    private EntityRepository $productRepository;
    private EntityRepository $categoryRepository;
    private EntityRepository $manufacturerRepository;
    private EntityRepository $productAnnotationRepository;
    private ContainerInterface $container;
    private ?\DateTimeImmutable $lastCheckTime = null;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        EntityRepository $productRepository,
        EntityRepository $categoryRepository,
        EntityRepository $manufacturerRepository,
        EntityRepository $productAnnotationRepository,
        ContainerInterface $container,
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->productAnnotationRepository = $productAnnotationRepository;
        $this->container = $container;
    }

    public static function getHandledMessages(): iterable
    {
        return [CacheClearTask::class];
    }

    /**
     * Checks for changes and clears cache only if needed.
     * @throws \Exception
     */
    public function run(): void
    {
        $context = Context::createDefaultContext();

        if (! $this->hasAnnotationChanged($context)) {
            return;
        }

        $application = new Application($this->container->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);
        $this->lastCheckTime = new \DateTimeImmutable();
    }

    private function hasAnnotationChanged(Context $context): bool
    {
        if ($this->lastCheckTime === null) {
            $this->lastCheckTime = new \DateTimeImmutable('-1 minute');
        }

        $annotationCriteria = new Criteria();
        $annotations = $this->productAnnotationRepository->search($annotationCriteria, $context)->getEntities();

        if ($annotations->count() === 0) {
            return false;
        }

        $productIds = [];
        $categoryIds = [];
        $manufacturerIds = [];

        foreach ($annotations as $annotation) {          
            if ($annotation->get('productId')) {
                $productIds[] = $annotation->get('productId');
            }
            if ($annotation->get('categoryId')) {
                $categoryIds[] = $annotation->get('categoryId');
            }
            if ($annotation->get('productManufacturerId')) {
                $manufacturerIds[] = $annotation->get('productManufacturerId');
            }
        }       
        return $this->hasEntitiesChanged($this->productRepository, $context, 'product', $productIds) ||
               $this->hasEntitiesChanged($this->categoryRepository, $context, 'category', $categoryIds) ||
               $this->hasEntitiesChanged($this->manufacturerRepository, $context, 'manufacturer', $manufacturerIds);
    }

    private function hasEntitiesChanged(EntityRepository $repository, Context $context, string $entityType, array $ids): bool
    {
        if ($ids === null || ! is_array($ids) || count($ids) === 0) {
            return false;
        }
        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter('updatedAt', [RangeFilter::GT => $this->lastCheckTime->format('Y-m-d H:i:s')]));
        $criteria->addFilter(new EqualsAnyFilter('id', $ids));

        $count = $repository->search($criteria, $context)->getTotal();

        return $count > 0;
    }
} 
