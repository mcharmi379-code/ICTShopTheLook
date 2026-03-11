<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1725517303 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1725517303;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS `product_annotation_banner` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `media_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `product_annotation_banner_axis` (
                `id` BINARY(16) NOT NULL,
                `product_annotation_banner_id` BINARY(16) NOT NULL,
                `select_type` VARCHAR(255) NOT NULL,
                `product_id` BINARY(16) NULL,
                `product_version_id` BINARY(16) NOT NULL,
                `category_id` BINARY(16) NULL,
                `category_version_id` BINARY(16) NOT NULL,
                `product_manufacturer_id` BINARY(16) NULL,
                `product_manufacturer_version_id` BINARY(16) NOT NULL,
                `x_axis` DOUBLE NOT NULL,
                `y_axis` DOUBLE NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.product_annotation_banner_axis.product_annotation_banner_id` (`product_annotation_banner_id`),
                CONSTRAINT `fk.product_annotation_banner_axis.product_annotation_banner_id` FOREIGN KEY (`product_annotation_banner_id`) REFERENCES `product_annotation_banner` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }
}
