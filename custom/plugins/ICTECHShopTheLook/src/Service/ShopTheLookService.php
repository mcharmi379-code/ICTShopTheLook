<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Service;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Context;

class ShopTheLookService
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

    public function getRelatedProducts(string $productId, ?string $parentId, Context $context): array
    {
        error_log('ShopTheLookService: Getting related products for: ' . $productId . ', parent: ' . ($parentId ?? 'null'));
        
        // Search for CMS slots with type 'ict-shop-the-look'
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', 'ict-shop-the-look'));
        $criteria->addAssociation('translations');
        
        $cmsSlots = $this->cmsSlotRepository->search($criteria, $context);
        error_log('Found CMS slots: ' . $cmsSlots->count());
        
        $associatedProductIds = [];
        $excludeParentId = $parentId; // Store the parent ID to exclude
        
        foreach ($cmsSlots->getElements() as $slot) {
            $config = $slot->getTranslated()['config'] ?? [];
            
            if (isset($config['hotspots']['value']) && is_array($config['hotspots']['value'])) {
                $hotspots = $config['hotspots']['value'];
                error_log('Processing slot with ' . count($hotspots) . ' hotspots');
                
                // Check if current product is in any hotspot
                $currentProductFound = false;
                foreach ($hotspots as $index => $hotspot) {
                    if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                        error_log('Hotspot ' . $index . ': ' . $hotspot['productId']);
                        // Check both product ID and parent ID
                        if ($hotspot['productId'] === $productId || ($parentId && $hotspot['productId'] === $parentId)) {
                            error_log('MATCH FOUND in hotspot ' . $index);
                            $currentProductFound = true;
                            break;
                        }
                    }
                }
                
                if ($currentProductFound) {
                    error_log('Current product found, adding other products...');
                    // Add all OTHER products from this Shop The Look
                    foreach ($hotspots as $hotspot) {
                        if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                            $hotspotProductId = $hotspot['productId'];
                            // Exclude current product and its parent
                            if ($hotspotProductId !== $productId && $hotspotProductId !== $parentId) {
                                error_log('Adding product: ' . $hotspotProductId);
                                $associatedProductIds[] = $hotspotProductId;
                            }
                        }
                    }
                    break; // Found the Shop The Look containing current product
                }
            }
        }
        
        error_log('Associated product IDs: ' . implode(', ', $associatedProductIds));
        
        if (empty($associatedProductIds)) {
            return [];
        }
        
        // Get product details with variants, excluding the current product's parent
        return $this->getProductsWithVariants($associatedProductIds, $excludeParentId, $context);
    }

    private function getProductsWithVariants(array $productIds, ?string $excludeParentId, Context $context): array
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
            // Skip if this product is the excluded parent
            if ($excludeParentId && $product->getId() === $excludeParentId) {
                error_log('Skipping excluded parent product: ' . $product->getId());
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