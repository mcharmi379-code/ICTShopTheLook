<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Subscriber;

use ICTECHShopTheLook\Struct\ShopTheLookRelatedStruct;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductDetailPageSubscriber implements EventSubscriberInterface
{
    private EntityRepository $cmsSlotRepository;
    private EntityRepository $productRepository;

    public function __construct(
        EntityRepository $cmsSlotRepository,
        EntityRepository $productRepository
    ) {
        $this->cmsSlotRepository = $cmsSlotRepository;
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded'
        ];
    }
    
    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        $context = $event->getSalesChannelContext();
        
        $productId = $product->getId();
        $parentId = $product->getParentId();
        
        // Get related products using the working logic from Twig extension
        $relatedProducts = $this->getShopTheLookDataForProduct($productId, $parentId, $context->getContext());
        
        if (!empty($relatedProducts)) {
            $struct = new ShopTheLookRelatedStruct($relatedProducts);
            $event->getPage()->addExtension('shopTheLookData', $struct);
        }
    }

    private function getShopTheLookDataForProduct(string $productId, ?string $parentId, $context): array
    {
        // Search for CMS slots with type 'ict-shop-the-look'
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', 'ict-shop-the-look'));
        $criteria->addAssociation('translations');
        
        $cmsSlots = $this->cmsSlotRepository->search($criteria, $context);
        
        $associatedProductIds = [];
        $foundByParentMatch = false;
        
        // FIRST PASS: Check ALL slots for parent ID matches (preferred)
        if ($parentId) {
            foreach ($cmsSlots->getElements() as $slot) {
                $config = $slot->getTranslated()['config'] ?? [];
                
                if (isset($config['hotspots']['value']) && is_array($config['hotspots']['value'])) {
                    $hotspots = $config['hotspots']['value'];
                    
                    // Check if parent ID is in any hotspot
                    $parentMatchFound = false;
                    foreach ($hotspots as $hotspot) {
                        if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                            if ($hotspot['productId'] === $parentId) {
                                $parentMatchFound = true;
                                break;
                            }
                        }
                    }
                    
                    if ($parentMatchFound) {
                        $foundByParentMatch = true;
                        
                        // Add all OTHER products from this Shop The Look
                        foreach ($hotspots as $hotspot) {
                            if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                                $hotspotProductId = $hotspot['productId'];
                                
                                // Exclude the parent ID (current product's parent)
                                if ($hotspotProductId !== $parentId) {
                                    $associatedProductIds[] = $hotspotProductId;
                                }
                            }
                        }
                        break; // Found the correct Shop The Look
                    }
                }
            }
        }
        
        // SECOND PASS: Only if no parent match found, check for direct product ID matches
        if (!$foundByParentMatch) {
            foreach ($cmsSlots->getElements() as $slot) {
                $config = $slot->getTranslated()['config'] ?? [];
                
                if (isset($config['hotspots']['value']) && is_array($config['hotspots']['value'])) {
                    $hotspots = $config['hotspots']['value'];
                    
                    // Check if current product ID is in any hotspot
                    $directMatchFound = false;
                    foreach ($hotspots as $hotspot) {
                        if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                            if ($hotspot['productId'] === $productId) {
                                $directMatchFound = true;
                                break;
                            }
                        }
                    }
                    
                    if ($directMatchFound) {
                        // Add all OTHER products from this Shop The Look
                        foreach ($hotspots as $hotspot) {
                            if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                                $hotspotProductId = $hotspot['productId'];
                                
                                // Exclude current product and its parent
                                if ($hotspotProductId !== $productId && $hotspotProductId !== $parentId) {
                                    $associatedProductIds[] = $hotspotProductId;
                                }
                            }
                        }
                        break; // Found a Shop The Look
                    }
                }
            }
        }
        
        // Remove duplicates and get parent product IDs for any variants
        $associatedProductIds = array_unique($associatedProductIds);
        
        // Convert variant IDs to parent IDs if needed
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
            
            foreach ($checkProducts->getElements() as $checkProduct) {
                $targetParentId = null;
                
                if ($checkProduct->getParentId() !== null) {
                    // This is a variant, use parent ID
                    $targetParentId = $checkProduct->getParentId();
                } else {
                    // This is already a parent product
                    $targetParentId = $checkProduct->getId();
                }
                
                // Only add if it's not the same as current product's parent
                if ($targetParentId !== $currentParentId) {
                    $parentProductIds[] = $targetParentId;
                }
            }
        }
        
        $parentProductIds = array_unique($parentProductIds);
        
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
        
        return $result;
    }
}