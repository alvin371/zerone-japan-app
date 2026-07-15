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
    public $requests = array();
    public $traces = array();

    function curlRequest($url, $headers = [], $options = [])
    {
        $this->requests[] = array(
            'url' => $url,
            'headers' => $headers,
            'options' => $options,
        );
        return array_shift($this->responses);
    }

    function log_endpoint_trace($event, $data = [])
    {
        $this->traces[] = array('event' => $event, 'data' => $data);
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
        $this->assertBoundedProviderRequest($template);
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
        $this->assertBoundedProviderRequest($template);
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
        $this->assertBoundedProviderRequest($template);
    }

    public function testNonSuccessProviderResponseReturnsNoMappedData()
    {
        $template = new FixtureTemplate();
        $template->responses[] = array('code' => 1001, 'msg' => 'not found', 'data' => array());
        $result = $template->get_tiktok_video_info('https://www.tiktok.com/@tiktok/video/1');

        $this->assertFalse($result['status']);
        $this->assertSame(array(), $result['data']);
        $this->assertBoundedProviderRequest($template);
    }

    public function testSocialMediaUsesBoundedProviderAndKeepsPhotoAssets()
    {
        $template = new FixtureTemplate();
        $template->responses[] = array(
            'code' => 0,
            'data' => array(
                'id' => '7231338487075638570',
                'digg_count' => 12,
                'share_count' => 3,
                'comment_count' => 4,
                'collect_count' => 5,
                'play_count' => 100,
                'create_time' => 1683677202,
                'cover' => 'https://cdn.example.test/cover.webp',
                'images' => array('https://cdn.example.test/1.webp', 'https://cdn.example.test/2.webp'),
            ),
        );

        $result = $template->get_tiktok_social_media('https://www.tiktok.com/@tiktok/photo/7231338487075638570');

        $this->assertTrue($result['status']);
        $this->assertSame('photo', $result['data']['media_type']);
        $this->assertSame(array('https://cdn.example.test/1.webp', 'https://cdn.example.test/2.webp'), $result['data']['images']);
        $this->assertSame(json_encode($result['data']['images']), $result['data']['video_link']);
        $this->assertBoundedProviderRequest($template);
        $this->assertStringNotContainsString('www.tiktok.com/@tiktok/photo/', $template->requests[0]['url']);
    }

    public function testProviderTimeoutIsRetryableAndLogged()
    {
        $template = new FixtureTemplate();
        $template->responses[] = array(
            'status' => false,
            'msg' => 'cURL Error: Operation timed out after 2000 milliseconds',
            'data' => array(),
            '__meta' => array(
                'http_code' => 0,
                'total_time' => 2.0,
                'curl_error' => 'Operation timed out after 2000 milliseconds',
            ),
        );

        $result = $template->get_tiktok_social_media('https://www.tiktok.com/@tiktok/video/7231338487075638570');

        $this->assertFalse($result['status']);
        $this->assertSame('provider_timeout', $result['code']);
        $this->assertTrue($result['retryable']);
        $this->assertStringContainsString('TikTok sedang lambat', $result['msg']);
        $this->assertSame('tiktok_provider_failure', $template->traces[0]['event']);
        $this->assertSame(2000, $template->traces[0]['data']['elapsed_ms']);
        $this->assertBoundedProviderRequest($template);
    }

    private function assertBoundedProviderRequest(FixtureTemplate $template)
    {
        $this->assertCount(1, $template->requests);
        $this->assertStringContainsString('tiktok-video-no-watermark10.p.rapidapi.com', $template->requests[0]['url']);
        $this->assertSame(2000, $template->requests[0]['options']['timeout_ms']);
        $this->assertSame(500, $template->requests[0]['options']['connect_timeout_ms']);
    }
}
