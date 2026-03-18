<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Core\Content\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ShopTheLookCmsElementResolver extends AbstractCmsElementResolver
{
    /**
     * @param SalesChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly CurrencyFormatter $currencyFormatter
    ) {
    }

    public function getType(): string
    {
        return 'ict-shop-the-look';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $hotspotsValue = $config->get('hotspots')?->getValue() ?? [];

        if (!is_array($hotspotsValue) || empty($hotspotsValue)) {
            return null;
        }

        /** @var string[] $productIds */
        $productIds = [];
        foreach ($hotspotsValue as $hotspot) {
            if (is_array($hotspot) && isset($hotspot['productId']) && is_string($hotspot['productId']) && $hotspot['productId'] !== '') {
                $productIds[] = $hotspot['productId'];
            }
        }

        if (empty($productIds)) {
            return null;
        }

        $criteriaCollection = new CriteriaCollection();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', array_values($productIds)));
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

        $hotspotsValue = $config->get('hotspots')?->getValue() ?? [];
        $lookImage = $config->get('lookImage')?->getValue();

        $products = $result->get('product_' . $slot->getId());

        $processedHotspots = [];
        if ($products instanceof EntitySearchResult && is_array($hotspotsValue)) {
            $productCollection = $products->getEntities();
            foreach ($hotspotsValue as $hotspot) {
                if (!is_array($hotspot) || !isset($hotspot['productId']) || !is_string($hotspot['productId']) || $hotspot['productId'] === '') {
                    continue;
                }

                $product = $productCollection->get($hotspot['productId']);
                if (!$product instanceof ProductEntity) {
                    continue;
                }

                $productForVariants = $product;

                $parentId = $product->getParentId();
                if ($parentId !== null) {
                    $parentCriteria = new Criteria([$parentId]);
                    $parentCriteria->addAssociation('children');
                    $parentCriteria->addAssociation('children.options');
                    $parentCriteria->addAssociation('children.options.group');
                    $parentCriteria->addAssociation('children.cover');

                    $parentResult = $this->productRepository->search($parentCriteria, $resolverContext->getSalesChannelContext());
                    $parentProduct = $parentResult->first();
                    if ($parentProduct instanceof ProductEntity) {
                        $productForVariants = $parentProduct;
                    }
                }

                $allVariants = $this->loadAllVariantsForProduct($productForVariants);

                $variantMappingData = [];
                $children = $productForVariants->getChildren();
                if ($children !== null && $children->count() > 0) {
                    foreach ($children as $child) {
                        /** @var ProductEntity $child */
                        $childOptions = [];
                        $childOptionCollection = $child->getOptions();
                        if ($childOptionCollection !== null) {
                            foreach ($childOptionCollection as $option) {
                                $childOptions[] = $option->getId();
                            }
                        }
                        $availableStock = $child->getAvailableStock() ?? $child->getStock();
                        $translated = $child->getTranslated();
                        $name = isset($translated['name']) && is_string($translated['name']) ? $translated['name'] : ($child->getName() ?? '');
                        $variantMappingData[] = [
                            'id' => $child->getId(),
                            'name' => $name,
                            'options' => $childOptions,
                            'inStock' => $child->getActive() && $availableStock > 0,
                        ];
                    }
                }

                $processedHotspots[] = [
                    'id' => isset($hotspot['id']) && is_string($hotspot['id']) ? $hotspot['id'] : uniqid(),
                    'xPosition' => $hotspot['xPosition'] ?? 50,
                    'yPosition' => $hotspot['yPosition'] ?? 50,
                    'product' => $product,
                    'allVariants' => $allVariants,
                    'variantMappingData' => $variantMappingData,
                    'parentProduct' => $productForVariants,
                    'formattedPrice' => $this->resolveFormattedPrice($product, $resolverContext->getSalesChannelContext()),
                ];
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
            'addSingleProduct' => $config->get('addSingleProduct')?->getValue() ?? true,
        ]);

        $slot->setData($data);
    }

    private function resolveFormattedPrice(ProductEntity $product, SalesChannelContext $salesChannelContext): string
    {
        $currency = $salesChannelContext->getCurrency();
        $currencyId = $currency->getId();
        $factor = $currency->getFactor();

        $price = $product->getCurrencyPrice($currencyId);

        // Fall back to default currency price and apply factor
        if ($price === null) {
            $priceCollection = $product->getPrice();
            $price = $priceCollection?->first();
        }

        if ($price === null) {
            return '';
        }

        $gross = $price->getGross() * ($price->getCurrencyId() === $currencyId ? 1.0 : $factor);

        return $this->currencyFormatter->formatCurrencyByLanguage(
            $gross,
            $currency->getIsoCode(),
            $salesChannelContext->getContext()->getLanguageId(),
            $salesChannelContext->getContext()
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadAllVariantsForProduct(ProductEntity $product): array
    {
        /** @var array<string, array<string, mixed>> $allOptions */
        $allOptions = [];

        try {
            $children = $product->getChildren();
            if ($children !== null && $children->count() > 0) {
                foreach ($children as $child) {
                    /** @var ProductEntity $child */
                    $options = $child->getOptions();
                    if ($options === null) {
                        continue;
                    }
                    foreach ($options as $option) {
                        $group = $option->getGroup();
                        if ($group === null) {
                            continue;
                        }
                        $groupName = $group->getName();
                        if (!isset($allOptions[$groupName])) {
                            $allOptions[$groupName] = [];
                        }
                        $allOptions[$groupName][$option->getId()] = $option;
                    }
                }
            } else {
                $options = $product->getOptions();
                if ($options !== null) {
                    foreach ($options as $option) {
                        $group = $option->getGroup();
                        if ($group === null) {
                            continue;
                        }
                        $groupName = $group->getName();
                        if (!isset($allOptions[$groupName])) {
                            $allOptions[$groupName] = [];
                        }
                        $allOptions[$groupName][$option->getId()] = $option;
                    }
                }

                $properties = $product->getProperties();
                if ($properties !== null) {
                    foreach ($properties as $property) {
                        $group = $property->getGroup();
                        if ($group === null) {
                            continue;
                        }
                        $groupName = $group->getName();
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
