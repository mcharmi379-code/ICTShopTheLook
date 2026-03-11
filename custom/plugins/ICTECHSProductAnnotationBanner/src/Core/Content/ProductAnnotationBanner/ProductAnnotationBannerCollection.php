<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @package core
 * @method void                add(ProductAnnotationBannerEntity $entity)
 * @method void                set(string $key, ProductAnnotationBannerEntity $entity)
 * @method ProductAnnotationBannerEntity[]    getIterator()
 * @method ProductAnnotationBannerEntity[]    getElements()
 * @method ProductAnnotationBannerEntity|null get(string $key)
 * @method ProductAnnotationBannerEntity|null first()
 * @method ProductAnnotationBannerEntity|null last()
 */
class ProductAnnotationBannerCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductAnnotationBannerEntity::class;
    }
}
