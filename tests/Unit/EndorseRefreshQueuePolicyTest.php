<?php

use PHPUnit\Framework\TestCase;

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__);
}

require_once __DIR__ . '/../../application/libraries/EndorseRefreshQueueService.php';

final class EndorseRefreshQueuePolicyTest extends TestCase
{
    public function testAllowsRustClaimsHonorsDriver(): void
    {
        putenv('ENDORSE_REFRESH_DRIVER=rust');
        self::assertTrue(EndorseRefreshQueueService::allowsRustClaims());

        putenv('ENDORSE_REFRESH_DRIVER=cron');
        self::assertFalse(EndorseRefreshQueueService::allowsRustClaims());
    }

    public function testRetryDelaySecondsBackoffIncreases(): void
    {
        self::assertSame(300, EndorseRefreshQueueService::retryDelaySeconds(1));
        self::assertSame(900, EndorseRefreshQueueService::retryDelaySeconds(2));
        self::assertSame(1800, EndorseRefreshQueueService::retryDelaySeconds(3));
    }

    public function testNormalizeTiktokUrlRemovesQueryNoise(): void
    {
        $normalized = EndorseRefreshQueueService::normalizeTiktokUrl('https://www.tiktok.com/@demo/video/1234567890?is_from_webapp=1&sender_device=pc');
        self::assertSame('https://www.tiktok.com/@demo/video/1234567890', $normalized);
    }
}
