<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Core\Content\Cms\DataResolver\Element;

use Customise_ShopTheLook\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use Customise_ShopTheLook\Core\Content\Cms\SalesChannel\Struct\CompanyStruct;
use Shopware\Core\Framework\Struct\Struct;

class CompanyCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-company';
    }

    public function getStruct(): Struct
    {
        return new CompanyStruct();
    }
}
