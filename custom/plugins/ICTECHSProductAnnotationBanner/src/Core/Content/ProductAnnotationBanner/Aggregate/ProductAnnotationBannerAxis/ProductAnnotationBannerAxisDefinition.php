<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\ProductAnnotationBannerDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductAnnotationBannerAxisDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'product_annotation_banner_axis';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductAnnotationBannerAxisCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductAnnotationBannerAxisEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return ProductAnnotationBannerDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('product_annotation_banner_id', 'productAnnotationBannerId', ProductAnnotationBannerDefinition::class, 'id'))->addFlags(new ApiAware(),new Required()),

            (new StringField('select_type', 'selectType'))->addFlags(new Required()),

            new FkField('product_id', 'productId', ProductDefinition::class),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),

            new FkField('category_id', 'categoryId', CategoryDefinition::class),
            (new ReferenceVersionField(CategoryDefinition::class))->addFlags(new ApiAware(), new Required()),

            new FkField('product_manufacturer_id', 'productManufacturerId', ProductManufacturerDefinition::class),
            (new ReferenceVersionField(ProductManufacturerDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FloatField('x_axis', 'xAxis'))->addFlags(new ApiAware(), new Required()),
            (new FloatField('y_axis', 'yAxis'))->addFlags(new ApiAware(), new Required()),

            new ManyToOneAssociationField('product_annotation_banner', 'product_annotation_banner_id', ProductAnnotationBannerDefinition::class, 'id'),

            new OneToOneAssociationField('product', 'product_id', 'id', ProductDefinition::class, false),
            new OneToOneAssociationField('category', 'category_id', 'id', CategoryDefinition::class, false),
            new OneToOneAssociationField('manufacturer', 'product_manufacturer_id', 'id', ProductManufacturerDefinition::class, false),
        ]);
    }
}
