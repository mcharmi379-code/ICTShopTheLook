<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Core\Content\Cms\DataResolver\Element;

use Customise_ShopTheLook\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use Customise_ShopTheLook\Core\Content\Cms\SalesChannel\Struct\LocationStruct;
use Shopware\Core\Framework\Struct\Struct;

class LocationCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-location';
    }

    public function getStruct(): Struct
    {
        return new LocationStruct();
    }
}
