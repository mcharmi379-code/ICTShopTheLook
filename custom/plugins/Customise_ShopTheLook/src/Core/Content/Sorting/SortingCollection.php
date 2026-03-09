<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Core\Content\Sorting;

use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingCollection;

class SortingCollection extends ProductSortingCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_sorting_collection';
    }
}
