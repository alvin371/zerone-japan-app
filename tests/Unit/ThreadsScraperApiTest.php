<?php

use PHPUnit\Framework\TestCase;

if (!defined('BASEPATH')) define('BASEPATH', __DIR__);
if (!function_exists('env')) {
    function env($key, $default = null) { return $default; }
}

require_once __DIR__ . '/../../application/libraries/Threads_scraper_api.php';
require_once __DIR__ . '/../../application/libraries/Template.php';

final class ThreadsScraperApiTest extends TestCase
{
    public function testPostResponseMapsToEndorseContract(): void
    {
        $result = Threads_scraper_api::normalizePostResult([
            'platform' => 'threads',
            'post_id' => 'post-1',
            'permalink' => 'https://www.threads.net/@user/post/ABC',
            'likes' => 12,
            'comments' => 4,
            'shares' => 5,
            'views' => 100,
        ], 'https://threads.com/@user/post/ABC?utm_source=test');

        $this->assertTrue($result['status']);
        $this->assertSame('post-1', $result['data']['content_id']);
        $this->assertSame(100, $result['data']['view']);
    }

    public function testRawJobResultUsesEnvelopePlatformAndLegacyFieldNames(): void
    {
        $result = Threads_scraper_api::normalizePostResult([
            'id' => 'post-2',
            'url' => 'https://www.threads.net/@user/post/DEF',
            'reposts' => 3,
            'datetime' => '2026-08-19T00:00:00+00:00',
        ], 'https://www.threads.com/@user/post/DEF', 'threads');

        $this->assertTrue($result['status']);
        $this->assertSame('post-2', $result['data']['content_id']);
        $this->assertSame(3, $result['data']['share']);
    }

    public function testContractMapsZeroSavesAndCanonicalizesTrackingUrl(): void
    {
        $result = Threads_scraper_api::normalizePostResult([
            'id' => 'Dbfa5wZmvyG', 'url' => 'https://www.threads.com/@putri/post/Dbfa5wZmvyG?utm=x',
            'likes' => 3, 'views' => 209, 'comments' => 3, 'reposts' => 1,
            'saves' => 0, 'quotes' => 0, 'caption' => "Halo\n世界", 'datetime' => '2026-08-01T08:33:52+00:00',
        ], 'https://www.threads.net/@putri/post/Dbfa5wZmvyG?xmt=x', 'threads');
        self::assertTrue($result['status']);
        self::assertSame(0, $result['data']['collect']);
        self::assertSame(1, $result['data']['share']);
        self::assertSame('https://www.threads.net/@putri/post/Dbfa5wZmvyG', $result['data']['url']);
        self::assertSame("Halo\n世界", $result['data']['caption']);
    }

    public function testMalformedOrForeignThreadsUrlIsRejected(): void
    {
        self::assertSame('', Threads_scraper_api::canonicalPostUrl('https://example.com/@a/post/id'));
        self::assertSame('', Threads_scraper_api::canonicalPostUrl('https://www.threads.net/@a/profile'));
    }

    public function testThreadsPostQueueHasTwoSubmissionAttempts(): void
    {
        self::assertSame(2, Template::THREADS_POST_MAX_ATTEMPTS);
    }

    public function testMismatchedOrWrongPlatformResponsesAreRejected(): void
    {
        $mismatch = Threads_scraper_api::normalizePostResult([
            'platform' => 'threads', 'post_id' => 'post-1',
            'permalink' => 'https://www.threads.net/@user/post/OTHER',
        ], 'https://www.threads.net/@user/post/ABC');
        $wrongPlatform = Threads_scraper_api::normalizePostResult([
            'platform' => 'instagram', 'post_id' => 'post-1',
            'permalink' => 'https://www.instagram.com/p/ABC',
        ]);

        $this->assertFalse($mismatch['status']);
        $this->assertSame('permanent', $mismatch['error_class']);
        $this->assertFalse($wrongPlatform['status']);
        $this->assertSame('permanent', $wrongPlatform['error_class']);
    }
}
