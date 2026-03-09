<?php declare(strict_types=1);

namespace Customise_ShopTheLook;

use Shopware\Core\Framework\Plugin;

class Customise_ShopTheLook extends Plugin
{
    public const NAME = 'Customise_ShopTheLook';
    public const DATA_CREATED_AT = '2003-03-03 23:0:02.000';
    public const PLUGIN_TABLES = [];
    public const SHOPWARE_TABLES = [
        'cms_page',
        'cms_page_translation',
        'cms_section',
        'cms_block',
        'category',
        'product'
    ];
}