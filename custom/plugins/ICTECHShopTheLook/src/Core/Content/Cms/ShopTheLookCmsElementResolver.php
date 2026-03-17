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
                        // For variant products, we need to get the parent product to load all variants
                        $productForVariants = $product;
                        
                        // If this is a child variant, load the parent separately
                        if ($product->getParentId()) {
                            $parentCriteria = new Criteria([$product->getParentId()]);
                            $parentCriteria->addAssociation('children');
                            $parentCriteria->addAssociation('children.options');
                            $parentCriteria->addAssociation('children.options.group');
                            $parentCriteria->addAssociation('children.cover');
                            
                            $parentResult = $this->productRepository->search($parentCriteria, $resolverContext->getSalesChannelContext());
                            $parentProduct = $parentResult->first();
                            
                            if ($parentProduct) {
                                $productForVariants = $parentProduct;
                            }
                        }
                        
                        // Load all variants for this product (or its parent)
                        $allVariants = $this->loadAllVariantsForProduct($productForVariants, $resolverContext);
                        
                        // Also get variant mapping data for JavaScript
                        $variantMappingData = [];
                        if ($productForVariants->getChildren() && $productForVariants->getChildren()->count() > 0) {
                            foreach ($productForVariants->getChildren() as $child) {
                                $childOptions = [];
                                if ($child->getOptions()) {
                                    foreach ($child->getOptions() as $option) {
                                        $childOptions[] = $option->getId();
                                    }
                                }
                                $availableStock = $child->getAvailableStock() ?? $child->getStock() ?? 0;
                                $variantMappingData[] = [
                                    'id' => $child->getId(),
                                    'name' => $child->getTranslated()['name'] ?? $child->getName(),
                                    'options' => $childOptions,
                                    'inStock' => $child->getActive() && $availableStock > 0,
                                ];
                            }
                        }
                        
                        $processedHotspots[] = [
                            'id' => $hotspot['id'] ?? uniqid(),
                            'xPosition' => $hotspot['xPosition'] ?? 50,
                            'yPosition' => $hotspot['yPosition'] ?? 50,
                            'product' => $product,
                            'allVariants' => $allVariants,
                            'variantMappingData' => $variantMappingData,
                            'parentProduct' => $productForVariants
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
    
    private function loadAllVariantsForProduct($product, ResolverContext $resolverContext): array
    {
        $allOptions = [];
        
        try {
            // If this product has children, collect options from all children
            if ($product->getChildren() && $product->getChildren()->count() > 0) {
                foreach ($product->getChildren() as $child) {
                    if ($child->getOptions()) {
                        foreach ($child->getOptions() as $option) {
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
                }
            }
            // If no children, use the product's own options and properties
            else {
                // Get options from the product
                if ($product->getOptions()) {
                    foreach ($product->getOptions() as $option) {
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
                
                // Get properties from the product
                if ($product->getProperties()) {
                    foreach ($product->getProperties() as $property) {
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