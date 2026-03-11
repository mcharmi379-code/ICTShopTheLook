<?php

declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\Extension;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\ProductAnnotationBannerDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField('media', 'id', 'media_id', ProductAnnotationBannerDefinition::class, true))->addFlags(new ApiAware(), new CascadeDelete())
        );
    }

    public function getEntityName(): string
    {
        return MediaDefinition::ENTITY_NAME;
    }
}
