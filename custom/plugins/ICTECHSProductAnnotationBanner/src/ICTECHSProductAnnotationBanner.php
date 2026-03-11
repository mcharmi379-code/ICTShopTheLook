<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner;

use Doctrine\DBAL\Connection;
use ICTECHSProductAnnotationBanner\Util\MediaFolder;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class ICTECHSProductAnnotationBanner extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $this->getMediaFolder()->installMedia($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS `product_annotation_banner_axis`');
        $connection->executeStatement('DROP TABLE IF EXISTS `product_annotation_banner`');
        $this->getMediaFolder()->uninstallMedia($uninstallContext->getContext());
    }

    private function getMediaFolder(): Mediafolder
    {
        /** @var EntityRepository $mediaFolderRepository */
        $mediaFolderRepository = $this->container->get('media_folder.repository');

        /** @var EntityRepository $mediaDefaultFolderRepository */
        $mediaDefaultFolderRepository = $this->container->get('media_default_folder.repository');

        /** @var EntityRepository $mediaFolderRepository */
        return new Mediafolder(
            $mediaFolderRepository,
            $mediaDefaultFolderRepository
        );
    }
}
