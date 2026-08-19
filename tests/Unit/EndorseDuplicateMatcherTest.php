<?php

use PHPUnit\Framework\TestCase;

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__);
}

require_once __DIR__ . '/../../application/libraries/EndorseDuplicateMatcher.php';

final class EndorseDuplicateMatcherTest extends TestCase
{
    public function testTrackingParametersDoNotCreateDuplicateMismatch(): void
    {
        self::assertTrue(EndorseDuplicateMatcher::matches(
            $this->endorse('Tiktok', 'https://www.tiktok.com/@demo/video/123?utm_source=one'),
            $this->endorse('TikTok', 'https://www.tiktok.com/@demo/video/123?fbclid=two')
        ));
    }

    public function testDifferentPostIdsAreNotDuplicates(): void
    {
        self::assertFalse(EndorseDuplicateMatcher::matches(
            $this->endorse('Instagram', 'https://instagram.com/reel/first/'),
            $this->endorse('Instagram', 'https://instagram.com/reel/second/')
        ));
    }

    public function testPlatformMismatchIsNotDuplicate(): void
    {
        self::assertFalse(EndorseDuplicateMatcher::matches(
            $this->endorse('Instagram', 'https://example.com/post/123'),
            $this->endorse('Threads', 'https://example.com/post/123')
        ));
    }

    public function testCanonicalUrlFallbackMatchesWithoutPlatformContentId(): void
    {
        self::assertTrue(EndorseDuplicateMatcher::matches(
            $this->endorse('Facebook', 'https://example.com/post/123?utm_source=one&foo=bar'),
            $this->endorse('Facebook', 'http://example.com/post/123?foo=bar&utm_medium=two')
        ));
    }

    private function endorse(string $platform, string $link): array
    {
        return [
            'platform' => $platform,
            'link_upload' => $link,
        ];
    }
}
