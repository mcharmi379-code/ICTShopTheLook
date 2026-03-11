<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @package core
 * @method void                add(ProductAnnotationBannerAxisEntity $entity)
 * @method void                set(string $key, ProductAnnotationBannerAxisEntity $entity)
 * @method ProductAnnotationBannerAxisEntity[]    getIterator()
 * @method ProductAnnotationBannerAxisEntity[]    getElements()
 * @method ProductAnnotationBannerAxisEntity|null get(string $key)
 * @method ProductAnnotationBannerAxisEntity|null first()
 * @method ProductAnnotationBannerAxisEntity|null last()
 */
class ProductAnnotationBannerAxisCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductAnnotationBannerAxisEntity::class;
    }
}
