<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\ProductAnnotationBannerEntity;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProductAnnotationBannerAxisEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected $productAnnotationBannerId;

    /**
     * @var string
     */
    protected $selectType;

    /**
     * @var string|null
     */
    protected $productId;

    /**
     * @var string|null
     */
    protected $categoryId;

    /**
     * @var string|null
     */
    protected $productManufacturerId;

    /**
     * @var float
     */
    protected $xAxis;

    /**
     * @var float
     */
    protected $yAxis;

    /**
     * @var ProductAnnotationBannerEntity|null
     */
    protected $product_annotation_banner;

    /**
     * @var ProductEntity|null
     */
    protected $product;

    /**
     * @var CategoryEntity|null
     */
    protected $category;

    /**
     * @var ProductManufacturerEntity|null
     */
    protected $manufacturer;

    /**
     * @var \DateTimeInterface
     */
    protected ?\DateTimeInterface $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected ?\DateTimeInterface $updatedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getProductAnnotationBannerId(): string
    {
        return $this->productAnnotationBannerId;
    }

    public function setProductAnnotationBannerId(string $productAnnotationBannerId): void
    {
        $this->productAnnotationBannerId = $productAnnotationBannerId;
    }

    public function getSelectType(): string
    {
        return $this->selectType;
    }

    public function setSelectType(string $selectType): void
    {
        $this->selectType = $selectType;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getProductManufacturerId(): ?string
    {
        return $this->productManufacturerId;
    }

    public function setProductManufacturerId(?string $productManufacturerId): void
    {
        $this->productManufacturerId = $productManufacturerId;
    }

    public function getXAxis(): float
    {
        return $this->xAxis;
    }

    public function setXAxis(float $xAxis): void
    {
        $this->xAxis = $xAxis;
    }

    public function getYAxis(): float
    {
        return $this->yAxis;
    }

    public function setYAxis(float $yAxis): void
    {
        $this->yAxis = $yAxis;
    }

    public function getProduct_annotation_banner(): ?ProductAnnotationBannerEntity
    {
        return $this->product_annotation_banner;
    }

    public function setProduct_annotation_banner(?ProductAnnotationBannerEntity $product_annotation_banner): void
    {
        $this->product_annotation_banner = $product_annotation_banner;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getManufacturer(): ?ProductManufacturerEntity
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?ProductManufacturerEntity $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
