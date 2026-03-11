<?php

declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Core\Content\AnnotationBannerImage\Cms;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Util\HtmlSanitizer;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Exception\JsonException;

#[Package('content')]
class AnnotationBannerImageCmsElementResolver extends AbstractCmsElementResolver
{
    /**
     * @internal
     */
    public function __construct(
        public readonly HtmlSanitizer $sanitizer,
        public readonly SystemConfigService $systemConfigService,
        public readonly EntityRepository $productRepository,
        public readonly EntityRepository $categoryRepository,
        public readonly EntityRepository $productAnnotationBannerRepository,
        public readonly EntityRepository $productAnnotationBannerAxisRepository
    ) {
    }

    public function getType(): string
    {
        return 'annotationBannerSelection';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    /**
     * @throws JsonException
     */
    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        if ($this->systemConfigService->get('ICTECHSProductAnnotationBanner.config.active', $resolverContext->getSalesChannelContext()->getSalesChannelId())) {
            $text = new TextStruct();
            $slot->setData($text);

            $productAnnotationBannerValue = $slot->getFieldConfig()->get('productAnnotationBanner')->getValue();

            $productAnnotationBannerAxisData = $this->getMediaFromProductBannerAxis($productAnnotationBannerValue, $resolverContext);

            if ($productAnnotationBannerAxisData !== []) {
                $slot->getData()->assign(['empty' => false]);
                try {
                    $text->setContent($this->sanitizer->sanitize($slot->getFieldConfig()->get('annotationBannerText')->getValue()));
                } catch (UniqueConstraintViolationException $exception) {
                    throw new \RuntimeException(sprintf(
                        'Error: %s',
                        $exception->getMessage()
                    ));
                }
                $slot->getData()->assign(['LayoutType' => $slot->getFieldConfig()->get('LayoutType')->getValue()]);
                $slot->getData()->assign(['salesChannelCurrency' => $resolverContext->getSalesChannelContext()->getCurrency()->get('symbol')]);
                $slot->getData()->assign(['annotationBannerUrl' => $productAnnotationBannerAxisData['annotationBannerUrl']]);
                $slot->getData()->assign(['productShowOnProductBanner' => $productAnnotationBannerAxisData['productShowOnBanner']]);
            } else {
                $slot->getData()->assign(['empty' => true]);
            }
        }
    }

    public function getMediaFromProductBannerAxis($productAnnotationBannerValue, ResolverContext $context): array
    {
        $productShowOnBanner = [];
        if ($productAnnotationBannerValue !== null) {
            $productBannerAxisCriteria = new Criteria();
            $productBannerAxisCriteria->addAssociation('product.media.media');
            $productBannerAxisCriteria->addAssociation('product.cover.media');
            $productBannerAxisCriteria->addAssociation('category');
            $productBannerAxisCriteria->addAssociation('category.media');
            $productBannerAxisCriteria->addAssociation('manufacturer');
            $productBannerAxisCriteria->addAssociation('manufacturer.media');
            $productBannerAxisCriteria->addAssociation('product_annotation_banner.media');
            $productBannerAxisCriteria->addFilter(new EqualsFilter('productAnnotationBannerId', $productAnnotationBannerValue));
            $productBannerAxisCriteria->addFilter(new EqualsFilter('product_annotation_banner.active', true));
            $productBannerCriteriaAxisData = $this->productAnnotationBannerAxisRepository->search($productBannerAxisCriteria, $context->getSalesChannelContext()->getContext());
            if ($productBannerCriteriaAxisData->getTotal() > 0) {
                $productShowOnBanner = [];

                foreach ($productBannerCriteriaAxisData->getElements() as $key => $BannerAxisData) {
                    $categoryId = null;
                    $categoryImageUrl = null;
                    $categoryName = null;
                    $category = null;
                    $manufacturerData = null;
                    $productManufacturerId = null;
                    $productMediaElements = [];
                    $productPriceElements = [];
                    $productTitleName = '';
                    $getProductPrice = '';
                    $productDescription = '';
                    $productCoverUrl = '';

                    if ($BannerAxisData->getSelectType() === 'product') {
                        if ($BannerAxisData->getProduct()->getParentId() !== null) {
                            $parentProductCriteriaData = $this->getChildIdToParentIdData($BannerAxisData, $context);
                            $childProductVariationDataString = $this->getChildProductVariations($BannerAxisData, $context);

                            if ($BannerAxisData->getProduct()->getMedia()->getElements() === []) {
                                $productMediaElements = $parentProductCriteriaData->getMedia()->getElements();
                            } else {
                                $productMediaElements = $BannerAxisData->getProduct()->getMedia()->getElements();
                            }

                            if ($BannerAxisData->getProduct()->getPrice() === null) {
                                $productPriceElements = $parentProductCriteriaData->getPrice()->first();
                            } else {
                                $productPriceElements = $BannerAxisData->getProduct()->getPrice()->first();
                            }

                            $productTitleName = $parentProductCriteriaData->getTranslated()['name'] . ' ' . $childProductVariationDataString;
                            $productDescription = $parentProductCriteriaData->getTranslated()['description'];
                        } else {
                            $productMediaElements = $BannerAxisData->getProduct()->getMedia()->getElements();
                            $productPriceElements = $BannerAxisData->getProduct()->getPrice()->first();
                            $productTitleName = $BannerAxisData->getProduct()->getTranslated()['name'];
                            $productDescription = $BannerAxisData->getProduct()->getTranslated()['description'];
                        }
                    } elseif ($BannerAxisData->getSelectType() === 'category') {
                        $categoryId = $BannerAxisData->getCategory()->getId();
                        $category = $BannerAxisData->getCategory();
                        // dump($BannerAxisData->getCategory()->getId());
                        if ($BannerAxisData->getCategory()->getTranslated()) {
                            $categoryName = $BannerAxisData->getCategory()->getTranslated()['name'];
                        }
                        if ($BannerAxisData->getCategory()->getMedia()) {
                            $categoryImageUrl = $BannerAxisData->getCategory()->getMedia()->getUrl();
                        }
                    } elseif ($BannerAxisData->getSelectType() === 'manufacturer') {
                        $manufacturerData = $BannerAxisData->getManufacturer();
                        $productManufacturerId = $BannerAxisData->getProductManufacturerId();
                    }

                    $getProductAllImagesUrl = null;
                    if ($BannerAxisData->get('product') !== null) {
                        if ($BannerAxisData->get('product')->getCoverId()) {
                            $media = $BannerAxisData->get('product')->getCover()->getMedia();
                            $productCoverUrl = $media ? $media->getUrl() : null;
                        }
                    }

                    if ($productMediaElements !== []) {
                        $getProductAllImagesUrl = $this->getProductAllImagesUrl($productMediaElements);
                    }

                    if ($BannerAxisData->getProductId()) {
                        $productId = $BannerAxisData->getProductId();
                    } else {
                        $productId = null;
                    }

                    if ($BannerAxisData->get('product') !== null) {
                        $productNumber = $BannerAxisData->get('product')->getProductNumber();
                    } else {
                        $productNumber = null;
                    }

                    if ($productPriceElements) {
                        $getProductPrice = $productPriceElements->getGross();
                    }

                    $productShowOnBanner['productShowOnBanner'][$key] = array(
                        'id' => $BannerAxisData->getId(),
                        'productId' => $productId,
                        'categoryName' => $categoryName,
                        'categoryId' => $categoryId,
                        'category' => $category,
                        'categoryImageUrl' => $categoryImageUrl,
                        'manufacturer' => $manufacturerData,
                        'productManufacturerId' => $productManufacturerId,
                        'productName' => $productTitleName,
                        'productCoverImage' => $productCoverUrl,
                        'productDescription' => $productDescription,
                        'productNumber' => $productNumber,
                        'productPrice' => $getProductPrice,
                        'productImageUrls' => $getProductAllImagesUrl,
                        'xAxis' => $BannerAxisData->get('xAxis'),
                        'yAxis' => $BannerAxisData->get('yAxis'),
                    );

                    $productShowOnBanner['annotationBannerUrl'] = $BannerAxisData->get('product_annotation_banner')->getMedia()->getUrl();
                }
            }
        }
        return $productShowOnBanner;
    }

    public function getProductAllImagesUrl($productMediaElements): array
    {
        $productMediaUrls = [];
        $k = 0;
        foreach ($productMediaElements as $productMediaElement) {
            $productMediaUrls['images'][$k] = $productMediaElement->media?->url ?? null;
            $k += 1;
        }
        return $productMediaUrls;
    }

    public function getChildIdToParentIdData($BannerAxisData, ResolverContext $context)
    {
        $productCriteria = new Criteria();
        $productCriteria->addAssociation('media');
        $productCriteria->addFilter(new EqualsFilter('id', $BannerAxisData->get('product')->parentId));
        return $this->productRepository->search($productCriteria, $context->getSalesChannelContext()->getContext())->first();
    }

    public function getChildProductVariations($BannerAxisData, ResolverContext $context): string
    {
        $variantProductVariantName = '';
        $productVariantCriteria = new Criteria();
        $productVariantCriteria->addAssociation('options.group');
        $productVariantCriteria->addFilter(new EqualsFilter('id', $BannerAxisData->get('product')->id));
        $productVariantCriteriaData = $this->productRepository->search($productVariantCriteria, $context->getSalesChannelContext()->getContext())->first();

        foreach ($productVariantCriteriaData->getVariation() as $variationData) {
            $variantProductVariantName .= $variationData['group'] . ': ' . $variationData['option'] . ' | ';
        }
        $variantProductVariantName = rtrim($variantProductVariantName, '| ');
        return '(' . $variantProductVariantName . ')';
    }
}
