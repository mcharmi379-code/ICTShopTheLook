<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Twig;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ShopTheLookExtension extends AbstractExtension
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getShopTheLookDataForProduct', [$this, 'getShopTheLookDataForProduct'])
        ];
    }

    public function getShopTheLookDataForProduct(string $productId, ?string $parentId, SalesChannelContext $context): array
    {
        error_log('Twig Extension: Getting Shop The Look data for product: ' . $productId . ', parent: ' . ($parentId ?? 'null'));
        
        // Search for CMS slots with type 'ict-shop-the-look'
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', 'ict-shop-the-look'));
        $criteria->addAssociation('translations');
        
        $cmsSlots = $this->cmsSlotRepository->search($criteria, $context->getContext());
        
        $associatedProductIds = [];
        
        foreach ($cmsSlots->getElements() as $slot) {
            $config = $slot->getTranslated()['config'] ?? [];
            
            if (isset($config['hotspots']['value']) && is_array($config['hotspots']['value'])) {
                $hotspots = $config['hotspots']['value'];
                
                // Check if current product is in any hotspot
                $currentProductFound = false;
                foreach ($hotspots as $hotspot) {
                    if (isset($hotspot['productId']) && !empty($hotspot['productId'])) {
                        // Check both product ID and parent ID
                        if ($hotspot['productId'] === $productId || ($parentId && $hotspot['productId'] === $parentId)) {
                            $currentProductFound = true;
                            break;
                        }
                    }
                }
                
                if ($currentProductFound) {
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
                    break; // Found the Shop The Look containing current product
                }
            }
        }
        
        // Remove duplicates and get parent product IDs for any variants
        $associatedProductIds = array_unique($associatedProductIds);
        
        // Convert variant IDs to parent IDs if needed
        $parentProductIds = [];
        if (!empty($associatedProductIds)) {
            $checkCriteria = new Criteria($associatedProductIds);
            $checkProducts = $this->productRepository->search($checkCriteria, $context->getContext());
            
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
        
        error_log('Twig Extension: Found parent product IDs: ' . implode(', ', $parentProductIds));
        
        if (empty($parentProductIds)) {
            return [];
        }
        
        return $this->getProductsWithVariants($parentProductIds, $context->getContext());
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