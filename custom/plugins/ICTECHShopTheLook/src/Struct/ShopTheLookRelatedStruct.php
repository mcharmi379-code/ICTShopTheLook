<?php declare(strict_types=1);

namespace ICTECHShopTheLook\Struct;

use Shopware\Core\Framework\Struct\Struct;

class ShopTheLookRelatedStruct extends Struct
{
    /** @var array<int, array<string, mixed>> */
    protected array $relatedLooks;

    /** @param array<int, array<string, mixed>> $relatedLooks */
    public function __construct(array $relatedLooks = [])
    {
        $this->relatedLooks = $relatedLooks;
    }

    /** @return array<int, array<string, mixed>> */
    public function getRelatedLooks(): array
    {
        return $this->relatedLooks;
    }

    /** @param array<int, array<string, mixed>> $relatedLooks */
    public function setRelatedLooks(array $relatedLooks): void
    {
        $this->relatedLooks = $relatedLooks;
    }

    public function getApiAlias(): string
    {
        return 'shop_the_look_related';
    }
}
