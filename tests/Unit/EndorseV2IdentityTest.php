<?php

use PHPUnit\Framework\TestCase;

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__);
}

require_once __DIR__ . '/../../application/libraries/EndorseV2Identity.php';

final class EndorseV2IdentityTest extends TestCase
{
    public function testTikTokTrackingNoiseDoesNotCreateNewIdentity(): void
    {
        $first = EndorseV2Identity::identify('Tiktok', 'https://www.tiktok.com/@demo/video/123?utm_source=x');
        $second = EndorseV2Identity::identify('TikTok', 'https://www.tiktok.com/@demo/video/123?fbclid=y');
        self::assertSame('TikTok', $first['platform']);
        self::assertSame('123', $first['platform_content_id']);
        self::assertFalse(EndorseV2Identity::requiresNewGeneration($first, $second));
    }

    public function testActualPostChangeCreatesGeneration(): void
    {
        $first = EndorseV2Identity::identify('Instagram', 'https://instagram.com/reel/first/');
        $second = EndorseV2Identity::identify('Instagram', 'https://instagram.com/reel/second/');
        self::assertTrue(EndorseV2Identity::requiresNewGeneration($first, $second));
    }

    public function testCreatorAndCampaignDoNotParticipateInIdentity(): void
    {
        $identity = EndorseV2Identity::identify('Threads', 'https://www.threads.net/@demo/post/ABC123');
        self::assertSame('ABC123', $identity['platform_content_id']);
        self::assertFalse(EndorseV2Identity::requiresNewGeneration($identity, $identity));
    }
}
