<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Core\Content\Cms\DataResolver\Element;

use Customise_ShopTheLook\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use Customise_ShopTheLook\Core\Content\Cms\SalesChannel\Struct\AddressStruct;
use Customise_ShopTheLook\Core\Content\Cms\SalesChannel\Struct\OpeningHoursStruct;
use Shopware\Core\Framework\Struct\Struct;

class OpeningHoursCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-opening-hours';
    }

    public function getStruct(): Struct
    {
        return new OpeningHoursStruct();
    }
}
