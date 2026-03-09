<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Core\Content\Cms\DataResolver\Element;

use Customise_ShopTheLook\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use Customise_ShopTheLook\Core\Content\Cms\SalesChannel\Struct\AddressStruct;
use Shopware\Core\Framework\Struct\Struct;

class AddressCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-address';
    }

    public function getStruct(): Struct
    {
        return new AddressStruct();
    }
}
