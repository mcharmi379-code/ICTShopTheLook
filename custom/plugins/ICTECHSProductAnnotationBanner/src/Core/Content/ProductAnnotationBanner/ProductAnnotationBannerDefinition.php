<?php

declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis\ProductAnnotationBannerAxisDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductAnnotationBannerDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'product_annotation_banner';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductAnnotationBannerCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductAnnotationBannerEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new StringField('name', 'name'))->addFlags(new Required()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new Required(), new ApiAware()),

            new OneToOneAssociationField('media', 'media_id', 'id', MediaDefinition::class, false),
            (new OneToManyAssociationField('productAnnotationBanners', ProductAnnotationBannerAxisDefinition::class, 'product_annotation_banner_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
