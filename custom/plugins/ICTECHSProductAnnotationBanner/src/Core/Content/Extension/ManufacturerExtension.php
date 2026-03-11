<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\Extension;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis\ProductAnnotationBannerAxisDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ManufacturerExtension extends EntityExtension
{
    /**
     * @param FieldCollection $collection
     * @return void
     */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField('manufacturer', 'id', 'product_manufacturer_id', ProductAnnotationBannerAxisDefinition::class, true))->addFlags(new ApiAware(), new CascadeDelete())
        );
    }
    public function getEntityName(): string
    {
        return ProductManufacturerDefinition::ENTITY_NAME;
    }
}
