<?php declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner;

use ICTECHSProductAnnotationBanner\Core\Content\ProductAnnotationBanner\Aggregate\ProductAnnotationBannerAxis\ProductAnnotationBannerAxisCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProductAnnotationBannerEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var string
     */
    protected $mediaId;

    /**
     * @var MediaEntity|null
     */
    protected $media;

    /**
     * @var ProductAnnotationBannerAxisCollection|null
     */
    protected $productAnnotationBanners;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getProductAnnotationBanners(): ?ProductAnnotationBannerAxisCollection
    {
        return $this->productAnnotationBanners;
    }

    public function setProductAnnotationBanners(?ProductAnnotationBannerAxisCollection $productAnnotationBanners): void
    {
        $this->productAnnotationBanners = $productAnnotationBanners;
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
