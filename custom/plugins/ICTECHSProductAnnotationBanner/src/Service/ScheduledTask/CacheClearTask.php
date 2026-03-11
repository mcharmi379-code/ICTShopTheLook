<?php

declare(strict_types=1);

namespace ICTECHSProductAnnotationBanner\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CacheClearTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ict_cache_clear_task';
    }

    public static function getDefaultInterval(): int
    {
        return 10;
    }
}
