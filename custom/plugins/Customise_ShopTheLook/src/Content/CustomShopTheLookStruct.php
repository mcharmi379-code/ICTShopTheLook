<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Content;

use Shopware\Core\Content\Cms\SalesChannel\Struct\ImageStruct;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Framework\Struct\Struct;

class CustomShopTheLookStruct extends Struct
{
    protected ImageStruct $media;

    protected ProductSliderStruct $products;

    public function getMedia(): ImageStruct
    {
        return $this->media;
    }
    public function setMedia(ImageStruct $media): void
    {
        $this->media = $media;
    }
    public function getProducts(): ProductSliderStruct
    {
        return $this->products;
    }
    public function setProducts(ProductSliderStruct $products): void
    {
        $this->products = $products;
    }
}
