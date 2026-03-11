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
        $criteria->addAssociation('children');
        $criteria->addAssociation('children.options');
        $criteria->addAssociation('children.options.group');
        $criteria->addAssociation('children.cover');
        $criteria->addAssociation('children.prices');
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
        // First, check if this product has variants (children)
        $childrenCriteria = new Criteria();
        $childrenCriteria->addFilter(new EqualsAnyFilter('parentId', [$productId]));
        $childrenCriteria->addAssociation('options');
        $childrenCriteria->addAssociation('options.group');
        $childrenCriteria->addAssociation('cover');
        
        // Also get the main product to check if it's a variant itself
        $mainProductCriteria = new Criteria([$productId]);
        $mainProductCriteria->addAssociation('options');
        $mainProductCriteria->addAssociation('options.group');
        $mainProductCriteria->addAssociation('properties');
        $mainProductCriteria->addAssociation('properties.group');
        $mainProductCriteria->addAssociation('parent');
        
        try {
            $children = $this->productRepository->search($childrenCriteria, $resolverContext->getSalesChannelContext());
            $mainProduct = $this->productRepository->search($mainProductCriteria, $resolverContext->getSalesChannelContext())->first();
            
            $allOptions = [];
            
            // If this product is a variant (has parent), get all siblings
            if ($mainProduct && $mainProduct->getParentId()) {
                $siblingsCriteria = new Criteria();
                $siblingsCriteria->addFilter(new EqualsAnyFilter('parentId', [$mainProduct->getParentId()]));
                $siblingsCriteria->addAssociation('options');
                $siblingsCriteria->addAssociation('options.group');
                
                $siblings = $this->productRepository->search($siblingsCriteria, $resolverContext->getSalesChannelContext());
                
                foreach ($siblings as $sibling) {
                    if ($sibling->getOptions()) {
                        foreach ($sibling->getOptions() as $option) {
                            $groupName = $option->getGroup()->getName();
                            if (!isset($allOptions[$groupName])) {
                                $allOptions[$groupName] = [];
                            }
                            $allOptions[$groupName][$option->getId()] = $option;
                        }
                    }
                }
            }
            // If this product has children (variants), collect their options
            elseif ($children->count() > 0) {
                foreach ($children as $child) {
                    if ($child->getOptions()) {
                        foreach ($child->getOptions() as $option) {
                            $groupName = $option->getGroup()->getName();
                            if (!isset($allOptions[$groupName])) {
                                $allOptions[$groupName] = [];
                            }
                            $allOptions[$groupName][$option->getId()] = $option;
                        }
                    }
                }
            }
            // If no variants found, use main product options and properties
            else {
                // Get options from main product
                if ($mainProduct && $mainProduct->getOptions()) {
                    foreach ($mainProduct->getOptions() as $option) {
                        $groupName = $option->getGroup()->getName();
                        if (!isset($allOptions[$groupName])) {
                            $allOptions[$groupName] = [];
                        }
                        $allOptions[$groupName][$option->getId()] = $option;
                    }
                }
                
                // Get properties from main product (for products without variants)
                if ($mainProduct && $mainProduct->getProperties()) {
                    foreach ($mainProduct->getProperties() as $property) {
                        $groupName = $property->getGroup()->getName();
                        if (!isset($allOptions[$groupName])) {
                            $allOptions[$groupName] = [];
                        }
                        $allOptions[$groupName][$property->getId()] = $property;
                    }
                }
            }
            
            return $allOptions;
        } catch (\Exception $e) {
            return [];
        }
    }
}