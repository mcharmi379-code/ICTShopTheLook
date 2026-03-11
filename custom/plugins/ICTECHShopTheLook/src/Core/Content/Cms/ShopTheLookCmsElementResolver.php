<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Core\Content\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;

class ShopTheLookCmsElementResolver extends AbstractCmsElementResolver
{
    public function __construct(
        private readonly SalesChannelRepository $productRepository
    ) {
    }

    public function getType(): string
    {
        return 'ict-shop-the-look';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $hotspots = $config->get('hotspots')?->getValue() ?? [];
        
        if (empty($hotspots)) {
            return null;
        }

        // Extract product IDs from hotspots
        $productIds = [];
        foreach ($hotspots as $hotspot) {
            if (!empty($hotspot['productId'])) {
                $productIds[] = $hotspot['productId'];
            }
        }

        if (empty($productIds)) {
            return null;
        }

        $criteriaCollection = new CriteriaCollection();
        
        // Create criteria for products
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $productIds));
        $criteria->addAssociation('cover');
        $criteria->addAssociation('prices');
        $criteria->addAssociation('options');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('properties');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('children.cover');
        $criteria->addAssociation('children.prices');
        $criteria->addAssociation('children.options');
        $criteria->addAssociation('children.options.group');
        $criteria->addAssociation('configuratorGroupConfig');
        $criteria->addAssociation('configuratorSettings');
        $criteria->addAssociation('configuratorSettings.option');
        $criteria->addAssociation('configuratorSettings.option.group');
        $criteria->addAssociation('configuratorSettings.media');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('seoUrls');
        
        $criteriaCollection->add('product_' . $slot->getId(), ProductDefinition::class, $criteria);

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = new TextStruct();
        $config = $slot->getFieldConfig();
        
        $hotspots = $config->get('hotspots')?->getValue() ?? [];
        $lookImage = $config->get('lookImage')?->getValue();
        
        // Get products from result
        $products = $result->get('product_' . $slot->getId());
        
        // Process hotspots with product data
        $processedHotspots = [];
        if ($products) {
            foreach ($hotspots as $hotspot) {
                if (!empty($hotspot['productId'])) {
                    $product = $products->get($hotspot['productId']);
                    if ($product) {
                        // Load all variants for this product to get all available options
                        $allVariants = $this->loadAllVariantsForProduct($product->getId(), $resolverContext);
                        
                        $processedHotspots[] = [
                            'id' => $hotspot['id'] ?? uniqid(),
                            'xPosition' => $hotspot['xPosition'] ?? 50,
                            'yPosition' => $hotspot['yPosition'] ?? 50,
                            'product' => $product,
                            'allVariants' => $allVariants
                        ];
                    }
                }
            }
        }

        $data->assign([
            'lookImage' => $lookImage,
            'hotspots' => $processedHotspots,
            'imageDimension' => $config->get('imageDimension')?->getValue() ?? '300x300',
            'customWidth' => $config->get('customWidth')?->getValue() ?? 300,
            'customHeight' => $config->get('customHeight')?->getValue() ?? 300,
            'layoutStyle' => $config->get('layoutStyle')?->getValue() ?? 'image-products',
            'showPrices' => $config->get('showPrices')?->getValue() ?? true,
            'showVariantSwitch' => $config->get('showVariantSwitch')?->getValue() ?? true,
            'addAllToCart' => $config->get('addAllToCart')?->getValue() ?? true,
            'addSingleProduct' => $config->get('addSingleProduct')?->getValue() ?? true
        ]);

        $slot->setData($data);
    }
    
    private function loadAllVariantsForProduct(string $productId, ResolverContext $resolverContext): array
    {
        $allOptions = [];
        
        try {
            // First, get the main product to understand its structure
            $mainProductCriteria = new Criteria([$productId]);
            $mainProductCriteria->addAssociation('options');
            $mainProductCriteria->addAssociation('options.group');
            $mainProductCriteria->addAssociation('properties');
            $mainProductCriteria->addAssociation('properties.group');
            $mainProductCriteria->addAssociation('children');
            $mainProductCriteria->addAssociation('parent');
            
            $mainProduct = $this->productRepository->search($mainProductCriteria, $resolverContext->getSalesChannelContext())->first();
            
            if (!$mainProduct) {
                return [];
            }
            
            // Determine the parent product ID
            $parentProductId = $mainProduct->getParentId() ?? $mainProduct->getId();
            
            // Load ALL variants of the parent product (including the main product if it's a parent)
            $variantsCriteria = new Criteria();
            
            // If this product is a variant (has parent), get all siblings
            if ($mainProduct->getParentId()) {
                $variantsCriteria->addFilter(new EqualsAnyFilter('parentId', [$mainProduct->getParentId()]));
            } 
            // If this product is a parent (has children), get all children
            elseif ($mainProduct->getChildCount() > 0) {
                $variantsCriteria->addFilter(new EqualsAnyFilter('parentId', [$mainProduct->getId()]));
            }
            // If it's a simple product, just use its own data
            else {
                $variantsCriteria = new Criteria([$productId]);
            }
            
            $variantsCriteria->addAssociation('options');
            $variantsCriteria->addAssociation('options.group');
            $variantsCriteria->addAssociation('properties');
            $variantsCriteria->addAssociation('properties.group');
            $variantsCriteria->addAssociation('cover');
            
            $variants = $this->productRepository->search($variantsCriteria, $resolverContext->getSalesChannelContext());
            
            // Collect options from ALL variants
            foreach ($variants as $variant) {
                // Get variant options (like color, size from variants)
                if ($variant->getOptions()) {
                    foreach ($variant->getOptions() as $option) {
                        $group = $option->getGroup();
                        if ($group) {
                            $groupName = $group->getName();
                            if (!isset($allOptions[$groupName])) {
                                $allOptions[$groupName] = [];
                            }
                            $allOptions[$groupName][$option->getId()] = $option;
                        }
                    }
                }
                
                // Get properties (for products without variants but with properties)
                if ($variant->getProperties()) {
                    foreach ($variant->getProperties() as $property) {
                        $group = $property->getGroup();
                        if ($group) {
                            $groupName = $group->getName();
                            if (!isset($allOptions[$groupName])) {
                                $allOptions[$groupName] = [];
                            }
                            $allOptions[$groupName][$property->getId()] = $property;
                        }
                    }
                }
            }
            
            // If we still don't have options, try to get them from the main product
            if (empty($allOptions)) {
                // Get options from main product
                if ($mainProduct->getOptions()) {
                    foreach ($mainProduct->getOptions() as $option) {
                        $group = $option->getGroup();
                        if ($group) {
                            $groupName = $group->getName();
                            if (!isset($allOptions[$groupName])) {
                                $allOptions[$groupName] = [];
                            }
                            $allOptions[$groupName][$option->getId()] = $option;
                        }
                    }
                }
                
                // Get properties from main product
                if ($mainProduct->getProperties()) {
                    foreach ($mainProduct->getProperties() as $property) {
                        $group = $property->getGroup();
                        if ($group) {
                            $groupName = $group->getName();
                            if (!isset($allOptions[$groupName])) {
                                $allOptions[$groupName] = [];
                            }
                            $allOptions[$groupName][$property->getId()] = $property;
                        }
                    }
                }
            }
            
            return $allOptions;
        } catch (\Exception $e) {
            return [];
        }
    }
}