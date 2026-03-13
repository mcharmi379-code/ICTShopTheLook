<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Subscriber;

use ICTECHShopTheLook\Struct\ShopTheLookRelatedStruct;
use ICTECHShopTheLook\Service\ShopTheLookService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailPageLoadedEvent;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductDetailPageSubscriber implements EventSubscriberInterface
{
    private EntityRepository $cmsSlotRepository;
    private EntityRepository $productRepository;
    private ShopTheLookService $shopTheLookService;

    public function __construct(
        EntityRepository $cmsSlotRepository,
        EntityRepository $productRepository,
        ShopTheLookService $shopTheLookService
    ) {
        $this->cmsSlotRepository = $cmsSlotRepository;
        $this->productRepository = $productRepository;
        $this->shopTheLookService = $shopTheLookService;
        
        // Debug: Check if subscriber is being instantiated
        error_log('ProductDetailPageSubscriber instantiated successfully!');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
            ProductDetailPageLoadedEvent::class => 'onProductDetailPageLoaded',
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }
    
    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        // Check if this is a product detail page
        $page = $event->getParameters()['page'] ?? null;
        
        if ($page && method_exists($page, 'getProduct') && $page->getProduct()) {
            error_log('StorefrontRenderEvent: Product page detected!');
            $product = $page->getProduct();
            $context = $event->getSalesChannelContext();
            
            // Use the service directly
            $relatedProducts = $this->shopTheLookService->getRelatedProducts(
                $product->getId(),
                $product->getParentId(),
                $context->getContext()
            );
            
            if (!empty($relatedProducts)) {
                $struct = new ShopTheLookRelatedStruct($relatedProducts);
                $page->addExtension('shopTheLookProducts', $struct);
                error_log('Extension added via StorefrontRenderEvent!');
            }
        }
    }
    
    public function onProductDetailPageLoaded(ProductDetailPageLoadedEvent $event): void
    {
        error_log('ProductDetailPageSubscriber::onProductDetailPageLoaded called!');
        
        $product = $event->getPage()->getProduct();
        $context = $event->getSalesChannelContext();
        
        $this->processProductPage($product, $context, $event->getPage());
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        error_log('ProductDetailPageSubscriber::onProductPageLoaded called!');
        
        $product = $event->getPage()->getProduct();
        $context = $event->getSalesChannelContext();
        
        $this->processProductPage($product, $context, $event->getPage());
    }
    
    private function processProductPage($product, $context, $page): void
    {
        // Get current product ID and parent ID
        $productId = $product->getId();
        $parentId = $product->getParentId();
        
        error_log('Product ID: ' . $productId);
        error_log('Parent ID: ' . ($parentId ?? 'null'));
        
        // Find associated products from Shop The Look elements
        $associatedProducts = $this->findAssociatedProducts($productId, $parentId, $context->getContext());
        
        error_log('Associated products count: ' . count($associatedProducts));
        
        if (!empty($associatedProducts)) {
            $struct = new ShopTheLookRelatedStruct($associatedProducts);
            $page->addExtension('shopTheLookProducts', $struct);
            error_log('Extension added to page!');
        } else {
            error_log('No associated products found');
        }
    }

    private function findAssociatedProducts(string $productId, ?string $parentId, $context): array
    {
        error_log('=== FINDING ASSOCIATED PRODUCTS ===');
        error_log('Current Product ID: ' . $productId);
        error_log('Current Parent ID: ' . ($parentId ?? 'null'));
        
        // Search for CMS slots with type 'ict-shop-the-look'
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', 'ict-shop-the-look'));
        $criteria->addAssociation('translations');
        
        $cmsSlots = $this->cmsSlotRepository->search($criteria, $context);
        
        error_log('Found CMS Slots: ' . $cmsSlots->count());
        
        $associatedProductIds = [];
        
        foreach ($cmsSlots->getElements() as $slot) {
            $config = $slot->getTranslated()['config'] ?? [];
            
            error_log('Processing slot ID: ' . $slot->getId());
            
            if (isset($config['hotspots']['value']) && is_array($config['hotspots']['value'])) {
                $hotspots = $config['hotspots']['value'];
                
                error_log('Hotspots in this slot: ' . count($hotspots));
                
                // Check if current product is in any hotspot
                $currentProductFound = false;
                foreach ($hotspots as $index => $hotspot) {
                    if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                        error_log('Hotspot ' . $index . ' product ID: ' . $hotspot['productId']);
                        // Check both product ID and parent ID
                        if ($hotspot['productId'] === $productId || ($parentId && $hotspot['productId'] === $parentId)) {
                            error_log('MATCH FOUND! Current product found in hotspot ' . $index);
                            $currentProductFound = true;
                            break;
                        }
                    }
                }
                
                error_log('Current product found in this slot: ' . ($currentProductFound ? 'YES' : 'NO'));
                
                if ($currentProductFound) {
                    error_log('Adding other products from this Shop The Look...');
                    // Add all OTHER products from this Shop The Look
                    foreach ($hotspots as $index => $hotspot) {
                        if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                            $hotspotProductId = $hotspot['productId'];
                            error_log('Checking hotspot ' . $index . ' product: ' . $hotspotProductId);
                            // Exclude current product and its parent
                            if ($hotspotProductId !== $productId && $hotspotProductId !== $parentId) {
                                error_log('Adding product: ' . $hotspotProductId);
                                $associatedProductIds[] = $hotspotProductId;
                            } else {
                                error_log('Excluding current product: ' . $hotspotProductId);
                            }
                        }
                    }
                    break; // Found the Shop The Look containing current product
                }
            } else {
                error_log('No hotspots found in this slot');
            }
        }
        
        error_log('Raw associated product IDs: ' . implode(', ', $associatedProductIds));
        
        // Remove duplicates and get parent product IDs for any variants
        $associatedProductIds = array_unique($associatedProductIds);
        
        // Convert variant IDs to parent IDs if needed and handle exclusions properly
        $parentProductIds = [];
        if (!empty($associatedProductIds)) {
            $checkCriteria = new Criteria($associatedProductIds);
            $checkProducts = $this->productRepository->search($checkCriteria, $context);
            
            // Get the current product's parent ID for proper exclusion
            $currentParentId = $parentId; // If current product is variant, this is its parent
            if ($currentParentId === null) {
                // Current product is parent, so exclude it directly
                $currentParentId = $productId;
            }
            
            error_log('Current parent ID for exclusion: ' . $currentParentId);
            
            foreach ($checkProducts->getElements() as $checkProduct) {
                $targetParentId = null;
                
                if ($checkProduct->getParentId() !== null) {
                    // This is a variant, use parent ID
                    $targetParentId = $checkProduct->getParentId();
                    error_log('Product ' . $checkProduct->getId() . ' is variant, parent: ' . $targetParentId);
                } else {
                    // This is already a parent product
                    $targetParentId = $checkProduct->getId();
                    error_log('Product ' . $checkProduct->getId() . ' is parent product');
                }
                
                // Only add if it's not the same as current product's parent
                if ($targetParentId !== $currentParentId) {
                    error_log('Adding parent product: ' . $targetParentId);
                    $parentProductIds[] = $targetParentId;
                } else {
                    error_log('Excluding same parent: ' . $targetParentId);
                }
            }
        }
        
        $parentProductIds = array_unique($parentProductIds);
        
        error_log('Final Parent Product IDs: ' . implode(', ', $parentProductIds));
        error_log('=== END FINDING ASSOCIATED PRODUCTS ===');
        
        if (empty($parentProductIds)) {
            return [];
        }
        
        return $this->getProductsWithVariants($parentProductIds, $context);
    }

    private function getProductsWithVariants(array $productIds, $context): array
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
            // Skip if this is a variant (child product) - we only want parent products
            if ($product->getParentId() !== null) {
                continue;
            }
            
            $variants = [];
            
            // Get variants if it's a parent product
            if ($product->getChildCount() > 0 && $product->getChildren()) {
                foreach ($product->getChildren() as $variant) {
                    $variantOptions = [];
                    if ($variant->getOptions()) {
                        foreach ($variant->getOptions() as $option) {
                            $variantOptions[] = [
                                'group' => $option->getGroup()->getName(),
                                'option' => $option->getName(),
                                'groupId' => $option->getGroup()->getId(),
                                'optionId' => $option->getId()
                            ];
                        }
                    }
                    
                    $variants[] = [
                        'id' => $variant->getId(),
                        'productNumber' => $variant->getProductNumber(),
                        'name' => $variant->getName(),
                        'price' => $variant->getPrice(),
                        'cover' => $variant->getCover(),
                        'options' => $variantOptions,
                        'stock' => $variant->getStock()
                    ];
                }
            }
            
            $result[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'productNumber' => $product->getProductNumber(),
                'price' => $product->getPrice(),
                'cover' => $product->getCover(),
                'stock' => $product->getStock(),
                'variants' => $variants,
                'hasVariants' => !empty($variants)
            ];
        }
        
        error_log('Final result count: ' . count($result));
        
        return $result;
    }
}
