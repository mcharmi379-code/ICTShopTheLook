<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Service;

use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;

/**
 * Value object that reads and exposes all 'ict-shop-the-look' CMS element
 * config fields with typed getters and sensible defaults.
 */
class ShopTheLookElementConfig
{
    private mixed $lookImage;
    private string $imageDimension;
    private int $customWidth;
    private int $customHeight;
    private string $layoutStyle;
    private bool $showPrices;
    private bool $showVariantSwitch;
    private bool $addAllToCart;
    private bool $addSingleProduct;

    public function __construct(FieldConfigCollection $config)
    {
        $this->lookImage         = $config->get('lookImage')?->getValue();
        $imageDimension = $config->get('imageDimension')?->getValue();
        $this->imageDimension = is_scalar($imageDimension) ? (string) $imageDimension : '300x300';
        $this->customWidth       = (int) ($config->get('customWidth')?->getValue() ?? 300);
        $this->customHeight      = (int) ($config->get('customHeight')?->getValue() ?? 300);
        $layoutStyle = $config->get('layoutStyle')?->getValue();
        $this->layoutStyle = is_scalar($layoutStyle) ? (string) $layoutStyle : 'image-products';
        $this->showPrices        = (bool) ($config->get('showPrices')?->getValue() ?? true);
        $this->showVariantSwitch = (bool) ($config->get('showVariantSwitch')?->getValue() ?? true);
        $this->addAllToCart      = (bool) ($config->get('addAllToCart')?->getValue() ?? true);
        $this->addSingleProduct  = (bool) ($config->get('addSingleProduct')?->getValue() ?? true);
    }

    public function getLookImage(): mixed
    {
        return $this->lookImage;
    }

    public function setLookImage(mixed $lookImage): void
    {
        $this->lookImage = $lookImage;
    }

    public function getImageDimension(): string
    {
        return $this->imageDimension;
    }

    public function setImageDimension(string $imageDimension): void
    {
        $this->imageDimension = $imageDimension;
    }

    public function getCustomWidth(): int
    {
        return $this->customWidth;
    }

    public function setCustomWidth(int $customWidth): void
    {
        $this->customWidth = $customWidth;
    }

    public function getCustomHeight(): int
    {
        return $this->customHeight;
    }

    public function setCustomHeight(int $customHeight): void
    {
        $this->customHeight = $customHeight;
    }

    public function getLayoutStyle(): string
    {
        return $this->layoutStyle;
    }

    public function setLayoutStyle(string $layoutStyle): void
    {
        $this->layoutStyle = $layoutStyle;
    }

    public function isShowPrices(): bool
    {
        return $this->showPrices;
    }

    public function setShowPrices(bool $showPrices): void
    {
        $this->showPrices = $showPrices;
    }

    public function isShowVariantSwitch(): bool
    {
        return $this->showVariantSwitch;
    }

    public function setShowVariantSwitch(bool $showVariantSwitch): void
    {
        $this->showVariantSwitch = $showVariantSwitch;
    }

    public function isAddAllToCart(): bool
    {
        return $this->addAllToCart;
    }

    public function setAddAllToCart(bool $addAllToCart): void
    {
        $this->addAllToCart = $addAllToCart;
    }

    public function isAddSingleProduct(): bool
    {
        return $this->addSingleProduct;
    }

    public function setAddSingleProduct(bool $addSingleProduct): void
    {
        $this->addSingleProduct = $addSingleProduct;
    }
}
