<?php

use PHPUnit\Framework\TestCase;

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $default;
    }
}

require_once __DIR__ . '/../application/libraries/Template.php';

class FixtureTemplate extends Template
{
    public $responses = array();

    function curlRequestWithRetry($url, $headers, $isValidResponse, $maxRetry = 3, $delayMs = 300, $debugContext = array())
    {
        return array_shift($this->responses);
    }
}

class TemplateTikTokProviderTest extends TestCase
{
    public function testVideoInfoMapsEndorseMetricsAndCreationDate()
    {
        $template = new FixtureTemplate();
        $template->responses[] = array(
            'code' => 0,
            'msg' => 'success',
            'data' => array(
                'digg_count' => 96326,
                'comment_count' => 1230,
                'share_count' => 931,
                'collect_count' => 59838,
                'play_count' => 583719,
                'create_time' => 1683677202,
            ),
        );

        $result = $template->get_tiktok_video_info('https://www.tiktok.com/@tiktok/video/7231338487075638570');

        $this->assertTrue($result['status']);
        $this->assertSame(96326, $result['data']['like']);
        $this->assertSame(931, $result['data']['share']);
        $this->assertSame(1230, $result['data']['comment']);
        $this->assertSame(59838, $result['data']['collect']);
        $this->assertSame(583719, $result['data']['view']);
        $this->assertSame('2023-05-09', $result['data']['created_at']);
    }

    public function testProfileMapsSecUidAndLargerAvatar()
    {
        $template = new FixtureTemplate();
        $template->responses[] = array(
            'code' => 0,
            'msg' => 'success',
            'data' => array(
                'user' => array(
                    'secUid' => 'MS4wLjABAAAAExample',
                    'uniqueId' => 'tiktok',
                    'nickname' => 'TikTok',
                    'avatarLarger' => 'https://example.test/avatar-larger.webp',
                ),
                'stats' => array(
                    'followerCount' => 94822355,
                    'videoCount' => 1550,
                ),
            ),
        );

        $result = $template->get_tiktok_profile_info('https://www.tiktok.com/@tiktok');

        $this->assertTrue($result['status']);
        $this->assertSame('MS4wLjABAAAAExample', $result['data']['account_id']);
        $this->assertSame('tiktok', $result['data']['username']);
        $this->assertSame(94822355, $result['data']['follower']);
        $this->assertSame(1550, $result['data']['media_count']);
        $this->assertSame('https://example.test/avatar-larger.webp', $result['data']['img']);
    }

    public function testRecentVideosMapsOnlyTheFirstTenPosts()
    {
        $videos = array();
        for ($i = 1; $i <= 11; $i++) {
            $videos[] = array(
                'digg_count' => $i,
                'share_count' => $i + 10,
                'comment_count' => $i + 20,
                'collect_count' => $i + 30,
                'play_count' => $i + 40,
            );
        }

        $template = new FixtureTemplate();
        $template->responses[] = array('code' => 0, 'msg' => 'success', 'data' => array('videos' => $videos));
        $result = $template->get_tiktok_user_videos('tiktok', '107955');

        $this->assertTrue($result['status']);
        $this->assertCount(10, $result['data']);
        $this->assertSame(1, $result['data'][0]['like']);
        $this->assertSame(50, $result['data'][9]['view']);
    }

    public function testNonSuccessProviderResponseReturnsNoMappedData()
    {
        $template = new FixtureTemplate();
        $template->responses[] = array('code' => 1001, 'msg' => 'not found', 'data' => array());
        $result = $template->get_tiktok_video_info('https://www.tiktok.com/@tiktok/video/1');

        $this->assertFalse($result['status']);
        $this->assertSame(array(), $result['data']);
    }
}
