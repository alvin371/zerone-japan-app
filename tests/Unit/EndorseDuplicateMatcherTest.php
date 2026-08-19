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

    public function testGroupDuplicatesKeepsOnlyRepeatedContent(): void
    {
        $groups = EndorseDuplicateMatcher::groupDuplicates([
            $this->row(1, 10, 'TikTok', 'https://www.tiktok.com/@demo/video/123'),
            $this->row(2, 20, 'Tiktok', 'https://www.tiktok.com/@demo/video/123?utm_source=ig'),
            $this->row(3, 10, 'Instagram', 'https://instagram.com/reel/solo/'),
        ], 10);

        self::assertCount(1, $groups);

        $group = array_shift($groups);
        self::assertCount(2, $group['items']);
        self::assertSame(2, $group['campaign_count']);
    }

    public function testGroupDuplicatesPutsCurrentCampaignRowsFirst(): void
    {
        $groups = EndorseDuplicateMatcher::groupDuplicates([
            $this->row(1, 20, 'Instagram', 'https://instagram.com/reel/abc/'),
            $this->row(2, 10, 'Instagram', 'https://instagram.com/reel/abc/'),
        ], 10);

        $group = array_shift($groups);
        self::assertSame(2, $group['items'][0]['id']);
        self::assertTrue($group['items'][0]['is_current_campaign']);
        self::assertFalse($group['items'][1]['is_current_campaign']);
    }

    public function testGroupDuplicatesDetectsRepeatWithinSameCampaign(): void
    {
        $groups = EndorseDuplicateMatcher::groupDuplicates([
            $this->row(1, 10, 'Threads', 'https://www.threads.net/@demo/post/abc'),
            $this->row(2, 10, 'Threads', 'https://www.threads.net/@demo/post/abc?igshid=x'),
        ], 10);

        $group = array_shift($groups);
        self::assertCount(2, $group['items']);
        self::assertSame(1, $group['campaign_count']);
    }

    public function testGroupDuplicatesOrdersLargestGroupFirst(): void
    {
        $groups = EndorseDuplicateMatcher::groupDuplicates([
            $this->row(1, 10, 'Instagram', 'https://instagram.com/reel/two/'),
            $this->row(2, 20, 'Instagram', 'https://instagram.com/reel/two/'),
            $this->row(3, 10, 'TikTok', 'https://www.tiktok.com/@demo/video/1'),
            $this->row(4, 20, 'TikTok', 'https://www.tiktok.com/@demo/video/1'),
            $this->row(5, 30, 'TikTok', 'https://www.tiktok.com/@demo/video/1'),
        ], 10);

        $first = array_shift($groups);
        self::assertCount(3, $first['items']);
    }

    private function endorse(string $platform, string $link): array
    {
        return [
            'platform' => $platform,
            'link_upload' => $link,
        ];
    }

    private function row(int $id, int $campaignId, string $platform, string $link): array
    {
        return [
            'id' => $id,
            'id_campaign' => $campaignId,
            'platform' => $platform,
            'link_upload' => $link,
            'posting_at' => '2026-08-01 10:00:00',
        ];
    }
}
