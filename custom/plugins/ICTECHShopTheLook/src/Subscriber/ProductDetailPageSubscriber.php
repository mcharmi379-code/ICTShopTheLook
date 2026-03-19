<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Subscriber;

use ICTECHShopTheLook\Struct\ShopTheLookRelatedStruct;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens to the product detail page load event and attaches
 * "Shop The Look" related products as a page extension.
 *
 * When a product detail page loads, this subscriber scans all
 * CMS slots of type 'ict-shop-the-look' to find any look that
 * contains the current product (or its parent variant), then
 * resolves the other products in that look and attaches them
 * to the page as 'shopTheLookData'.
 */
class ProductDetailPageSubscriber implements EventSubscriberInterface
{
    /**
     * @param EntityRepository<CmsSlotCollection> $cmsSlotRepository
     * @param EntityRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly EntityRepository $cmsSlotRepository,
        private readonly EntityRepository $productRepository
    ) {
    }

    /**
     * Registers the events this subscriber listens to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    /**
     * Triggered when a product detail page is loaded.
     * Fetches related "Shop The Look" products and attaches them to the page.
     */
    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        $context = $event->getSalesChannelContext();

        $productId = $product->getId();
        $parentId  = $product->getParentId();

        /** @var array<int, array<string, mixed>> $relatedProducts */
        $relatedProducts = $this->getShopTheLookDataForProduct($productId, $parentId, $context->getContext());

        if (!empty($relatedProducts)) {
            $struct = new ShopTheLookRelatedStruct($relatedProducts);
            $event->getPage()->addExtension('shopTheLookData', $struct);
        }
    }

    /**
     * Finds all products associated with the current product via Shop The Look CMS slots.
     *
     * Logic uses a two-pass approach:
     * - Pass 1: Prefer matching by parent product ID (handles variant product pages).
     *   If the current product is a variant, we look for its parent ID in hotspot configs.
     * - Pass 2: If no parent match found, fall back to matching by the direct product ID.
     *
     * This ensures that visiting any variant of a product still shows the correct look.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getShopTheLookDataForProduct(string $productId, ?string $parentId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', 'ict-shop-the-look'));
        $criteria->addAssociation('translations');

        $cmsSlots = $this->cmsSlotRepository->search($criteria, $context);

        /** @var array<int, string> $associatedProductIds */
        $associatedProductIds = [];
        $foundByParentMatch   = false;

        if ($parentId !== null) {
            foreach ($cmsSlots->getElements() as $slot) {
                $hotspots = $this->extractHotspotsFromSlot($slot);
                if ($hotspots === null) {
                    continue;
                }

                $parentMatchFound = false;
                foreach ($hotspots as $hotspot) {
                    if ($this->isValidHotspot($hotspot) && $hotspot['productId'] === $parentId) {
                        $parentMatchFound = true;
                        break;
                    }
                }

                if ($parentMatchFound) {
                    $foundByParentMatch = true;
                    foreach ($hotspots as $hotspot) {
                        if ($this->isValidHotspot($hotspot) && $hotspot['productId'] !== $parentId) {
                            $associatedProductIds[] = $hotspot['productId'];
                        }
                    }
                    break;
                }
            }
        }

        if (!$foundByParentMatch) {
            foreach ($cmsSlots->getElements() as $slot) {
                $hotspots = $this->extractHotspotsFromSlot($slot);
                if ($hotspots === null) {
                    continue;
                }

                $directMatchFound = false;
                foreach ($hotspots as $hotspot) {
                    if ($this->isValidHotspot($hotspot) && $hotspot['productId'] === $productId) {
                        $directMatchFound = true;
                        break;
                    }
                }

                if ($directMatchFound) {
                    foreach ($hotspots as $hotspot) {
                        if ($this->isValidHotspot($hotspot) && $hotspot['productId'] !== $productId && $hotspot['productId'] !== $parentId) {
                            $associatedProductIds[] = $hotspot['productId'];
                        }
                    }
                    break;
                }
            }
        }

        /** @var array<int, string> $associatedProductIds */
        $associatedProductIds = array_values(array_unique($associatedProductIds));

        if (empty($associatedProductIds)) {
            return [];
        }

        $checkCriteria = new Criteria($associatedProductIds);
        $checkProducts = $this->productRepository->search($checkCriteria, $context);

        $currentParentId = $parentId ?? $productId;

        /** @var array<int, string> $parentProductIds */
        $parentProductIds = [];
        foreach ($checkProducts->getElements() as $checkProduct) {
            $targetParentId = $checkProduct->getParentId() ?? $checkProduct->getId();
            if ($targetParentId !== $currentParentId) {
                $parentProductIds[] = $targetParentId;
            }
        }

        $parentProductIds = array_values(array_unique($parentProductIds));

        if (empty($parentProductIds)) {
            return [];
        }

        return $this->getProductsWithVariants($parentProductIds, $context);
    }

    /**
     * Extracts the hotspots array from a CMS slot's translated config.
     * Returns null if the slot has no valid hotspots config.
     *
     * @return array<int, mixed>|null
     */
    private function extractHotspotsFromSlot(mixed $slot): ?array
    {
        $translated     = $slot->getTranslated();
        $config         = isset($translated['config']) && is_array($translated['config']) ? $translated['config'] : [];
        $hotspotsConfig = isset($config['hotspots']) && is_array($config['hotspots']) ? $config['hotspots'] : [];

        if (!isset($hotspotsConfig['value']) || !is_array($hotspotsConfig['value'])) {
            return null;
        }

        return $hotspotsConfig['value'];
    }

    /**
     * Returns true if the hotspot array has a non-empty string productId.
     *
     * @param mixed $hotspot
     */
    private function isValidHotspot(mixed $hotspot): bool
    {
        return is_array($hotspot)
            && isset($hotspot['productId'])
            && is_string($hotspot['productId'])
            && $hotspot['productId'] !== '';
    }

    /**
     * Loads full product data including all variants and their options for the given parent product IDs.
     * Only returns root (non-variant) products; child products are nested under their parent.
     *
     * @param array<int, string> $productIds  Array of parent product IDs
     * @return array<int, array<string, mixed>>
     */
    private function getProductsWithVariants(array $productIds, Context $context): array
    {
        $criteria = new Criteria($productIds);
        $criteria->addAssociation('cover.media');
        $criteria->addAssociation('children.cover.media');
        $criteria->addAssociation('children.options.group');
        $criteria->addAssociation('children.prices');
        $criteria->addAssociation('prices');

        $products = $this->productRepository->search($criteria, $context);

        $result = [];
        foreach ($products->getElements() as $product) {
            if ($product->getParentId() !== null) {
                continue;
            }

            /** @var array<int, array<string, mixed>> $variants */
            $variants = [];
            $children = $product->getChildren();

            if ($product->getChildCount() > 0 && $children !== null) {
                foreach ($children as $variant) {
                    /** @var array<int, array<string, mixed>> $variantOptions */
                    $variantOptions = [];
                    $options        = $variant->getOptions();

                    if ($options !== null) {
                        foreach ($options as $option) {
                            $group = $option->getGroup();
                            if ($group === null) {
                                continue;
                            }
                            $variantOptions[] = [
                                'group'    => $group->getName(),
                                'option'   => $option->getName(),
                                'groupId'  => $group->getId(),
                                'optionId' => $option->getId(),
                            ];
                        }
                    }

                    $variants[] = [
                        'id'            => $variant->getId(),
                        'productNumber' => $variant->getProductNumber(),
                        'name'          => $variant->getName(),
                        'price'         => $variant->getPrice(),
                        'cover'         => $variant->getCover(),
                        'options'       => $variantOptions,
                        'stock'         => $variant->getStock(),
                    ];
                }
            }

            $result[] = [
                'id'            => $product->getId(),
                'name'          => $product->getName(),
                'productNumber' => $product->getProductNumber(),
                'price'         => $product->getPrice(),
                'cover'         => $product->getCover(),
                'stock'         => $product->getStock(),
                'variants'      => $variants,
                'hasVariants'   => !empty($variants),
            ];
        }

        return $result;
    }
}
