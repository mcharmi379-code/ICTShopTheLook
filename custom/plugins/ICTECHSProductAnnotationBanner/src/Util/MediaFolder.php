<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Util;

use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class MediaFolder
{
    private EntityRepository $mediaFolderRepository;

    private EntityRepository $mediaDefaultFolderRepository;

    public function __construct(
        EntityRepository $mediaFolderRepository,
        EntityRepository $mediaDefaultFolderRepository
    ) {
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->mediaDefaultFolderRepository = $mediaDefaultFolderRepository;
    }

    public function installMedia(Context $context): void
    {
        $this->createMediaFolderContent($context);
    }

    public function uninstallMedia(Context $context): void
    {
        $this->deleteMediaFolderContent($context);
    }

    private function createMediaFolderContent(Context $context): void
    {
        $defaultFolderId = $this->createDefaultFolder($context);

        $mediaFolderId = Uuid::randomHex();
        $mediaFolder = [
            [
                'id' => $mediaFolderId,
                'name' => 'Annotation Images',
                'defaultFolderId' => $defaultFolderId,
                'child_count' => '0',
                'configuration' => [
                    'id' => Uuid::randomHex(),
                    'createThumbnails' => true,
                    'keepAspectRatio' => true,
                    'thumbnailQuality' => 80,
                ],
                'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ],
        ];
        $deleteMediaFolderCriteria = new Criteria();
        $deleteMediaFolderCriteria->addFilter(new EqualsFilter('name', 'Annotation Images'));
        $deleteMediaFolderId = $this->mediaFolderRepository->search($deleteMediaFolderCriteria, $context)->getEntities()->first();
     
        if (! $deleteMediaFolderId) {
            try {
                $this->mediaFolderRepository->create($mediaFolder, $context);
            } catch (UniqueConstraintViolationException $exception) {
                throw new \RuntimeException(sprintf(
                    'Error: %s',
                    $exception->getMessage()
                ));
            }
        }
    }

    private function createDefaultFolder(Context $context): string
    {
        $mediaDefaultFolderId = Uuid::randomHex();
        $mediaDefaultFolder = [
            [
                'id' => $mediaDefaultFolderId,
                'associationFields' => ['media'],
                'entity' => 'product_annotation_banner',
                'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ],
        ];

        try {
            $this->mediaDefaultFolderRepository->create($mediaDefaultFolder, $context);
        } catch (UniqueConstraintViolationException $exception) {
            throw new \RuntimeException(sprintf(
                'Error: %s',
                $exception->getMessage()
            ));
        }
        return $mediaDefaultFolderId;
    }

    private function deleteMediaFolderContent(Context $context): void
    {
        $deleteMediaFolderCriteria = new Criteria();
        $deleteMediaFolderCriteria->addFilter(new EqualsFilter('name', 'Annotation Images'));
        $deleteMediaFolderId = $this->mediaFolderRepository->search($deleteMediaFolderCriteria, $context)->getEntities()->first()->getId();

        $deleteDefaultMediaFolderCriteria = new Criteria();
        $deleteDefaultMediaFolderCriteria->addFilter(new EqualsFilter('entity', 'product_annotation_banner'));
        $deleteDefaultMediaFolderId = $this->mediaDefaultFolderRepository->search($deleteDefaultMediaFolderCriteria, $context)->getEntities()->first()->getId();
     
        $this->mediaFolderRepository->delete([['id' => $deleteMediaFolderId]], $context);
        $this->mediaDefaultFolderRepository->delete([['id' => $deleteDefaultMediaFolderId]], $context);
    }
}
