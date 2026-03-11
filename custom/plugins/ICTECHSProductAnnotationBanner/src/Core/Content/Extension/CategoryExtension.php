<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\Extension;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis\ProductAnnotationBannerAxisDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CategoryExtension extends EntityExtension
{
    /**
     * @param FieldCollection $collection
     * @return void
     */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                'ProductAnnotationBanner',
                'id',
                'category_id',
                ProductAnnotationBannerAxisDefinition::class,
                false
            ))->addFlags(new ApiAware(), new CascadeDelete(), new Inherited())
        );
    }

    public function getEntityName(): string
    {
        return CategoryDefinition::ENTITY_NAME;
    }
}
