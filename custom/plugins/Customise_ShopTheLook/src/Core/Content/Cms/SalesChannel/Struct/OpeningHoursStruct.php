<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Core\Content\Cms\SalesChannel\Struct;

use Customise_ShopTheLook\Core\Framework\DataAbstractionLayer\EntityAddressTrait;
use Customise_ShopTheLook\Core\Framework\DataAbstractionLayer\EntityOpeningHoursTrait;
use Shopware\Core\Framework\Struct\Struct;

class OpeningHoursStruct extends Struct
{
    use EntityOpeningHoursTrait;

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_opening_hours';
    }
}
