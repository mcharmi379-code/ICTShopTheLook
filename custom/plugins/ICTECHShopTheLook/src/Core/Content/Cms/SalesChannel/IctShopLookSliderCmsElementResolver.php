<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Core\Content\Cms\SalesChannel;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class IctShopLookSliderCmsElementResolver extends AbstractCmsElementResolver
{
    public function getType(): string
    {
        return 'ict-shop-look-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $sliderItemsConfig = $config->get('sliderItems');

        if (!$sliderItemsConfig || $sliderItemsConfig->isMapped()) {
            return null;
        }

        $sliderItems = $sliderItemsConfig->getArrayValue();
        /** @var list<string> $mediaIds */
        $mediaIds = array_values(array_filter(array_column($sliderItems, 'mediaId'), 'is_string'));

        if (empty($mediaIds)) {
            return null;
        }

        $criteria = new Criteria($mediaIds);

        $criteriaCollection = new CriteriaCollection();
        $criteriaCollection->add('media_' . $slot->getUniqueIdentifier(), MediaDefinition::class, $criteria);

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $sliderItemsConfig = $config->get('sliderItems');

        if (!$sliderItemsConfig) {
            return;
        }

        $sliderItemsValue = $sliderItemsConfig->getArrayValue();
        $mediaResult = $result->get('media_' . $slot->getUniqueIdentifier());

        foreach ($sliderItemsValue as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $mediaId = isset($item['mediaId']) && is_string($item['mediaId']) ? $item['mediaId'] : null;
            if ($mediaId !== null && $mediaResult) {
                $media = $mediaResult->get($mediaId);
                if ($media) {
                    $item['media'] = $media;
                }
            }
        }

        $slot->setData(new \Shopware\Core\Framework\Struct\ArrayStruct(['sliderItems' => $sliderItemsValue]));
    }
}
