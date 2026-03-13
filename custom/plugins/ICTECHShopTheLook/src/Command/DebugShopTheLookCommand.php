<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Command;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shop-the-look:debug',
    description: 'Debug Shop The Look CMS elements'
)]
class DebugShopTheLookCommand extends Command
{
    private EntityRepository $cmsSlotRepository;
    private EntityRepository $productRepository;

    public function __construct(
        EntityRepository $cmsSlotRepository,
        EntityRepository $productRepository
    ) {
        $this->cmsSlotRepository = $cmsSlotRepository;
        $this->productRepository = $productRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = \Shopware\Core\Framework\Context::createDefaultContext();
        
        // Search for CMS slots with type 'ict-shop-the-look'
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', 'ict-shop-the-look'));
        $criteria->addAssociation('translations');
        
        $cmsSlots = $this->cmsSlotRepository->search($criteria, $context);
        
        $output->writeln('Found ' . $cmsSlots->count() . ' Shop The Look CMS slots');
        
        foreach ($cmsSlots->getElements() as $slot) {
            $output->writeln('Slot ID: ' . $slot->getId());
            $config = $slot->getTranslated()['config'] ?? [];
            
            if (isset($config['hotspots']['value']) && is_array($config['hotspots']['value'])) {
                $hotspots = $config['hotspots']['value'];
                $output->writeln('  Hotspots: ' . count($hotspots));
                
                foreach ($hotspots as $hotspot) {
                    if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                        $output->writeln('    Product ID: ' . $hotspot['productId']);
                    }
                }
            }
        }
        
        return Command::SUCCESS;
    }
}
