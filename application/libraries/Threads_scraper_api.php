<?php

defined('BASEPATH') || exit('No direct script access allowed');

/** Client for the async Threads scraper used by Forbes. */
class Threads_scraper_api
{
    protected $baseUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim(trim((string) ($config['base_url'] ?? env('SOCIAL_SCRAPER_BASE_URL', env('THREADS_SCRAP_BASE_URL', '')))), '/');
        $this->apiKey = trim((string) ($config['api_key'] ?? env('SOCIAL_SCRAPER_API_KEY', env('THREADS_SCRAP_API_KEY', ''))));
        $this->timeout = max(5, (int) ($config['timeout'] ?? env('SOCIAL_SCRAPER_TIMEOUT', env('THREADS_SCRAP_TIMEOUT', 20))));
    }

    public function createAccount(string $link): array
    {
        return $this->request('POST', '/api/v1/accounts', ['link' => trim($link)]);
    }

    public function scrapePost(string $link): array
    {
        return $this->request('POST', '/api/v1/posts/scrape', ['link' => trim($link)]);
    }

    public function job(string $jobId): array
    {
        return $this->request('GET', '/api/v1/jobs/' . rawurlencode(trim($jobId)));
    }

    public function account(string $accountId): array
    {
        return $this->request('GET', '/api/v1/accounts/' . rawurlencode(trim($accountId)));
    }

    public function posts(string $accountId, int $limit = 10): array
    {
        return $this->request('GET', '/api/v1/posts?account_id=' . rawurlencode(trim($accountId)) . '&platform=threads&limit=' . max(1, min(200, $limit)));
    }

    public static function normalizePostResult(array $post, string $expectedUrl = '', string $jobPlatform = ''): array
    {
        $platform = strtolower(trim((string) ($post['platform'] ?? $jobPlatform)));
        $postId = trim((string) ($post['post_id'] ?? $post['id'] ?? ''));
        $permalink = trim((string) ($post['permalink'] ?? $post['url'] ?? ''));
        if ($platform !== 'threads') {
            return self::failure('Hasil scraper bukan post Threads.', 'permanent');
        }
        if ($postId === '' || $permalink === '') {
            return self::failure('Hasil job Threads tidak memenuhi kontrak PostResponse.', 'transient');
        }
        $canonicalUrl = self::canonicalPostUrl($permalink);
        if ($canonicalUrl === '' || ($expectedUrl !== '' && self::canonicalPostUrl($expectedUrl) !== $canonicalUrl)) {
            return self::failure('Permalink hasil job Threads tidak cocok dengan konten yang diminta.', 'permanent');
        }

        return [
            'status' => true,
            'msg' => 'Statistik Threads ditemukan.',
            'data' => [
                'content_id' => $postId,
                'like' => (int) ($post['likes'] ?? 0),
                'comment' => (int) ($post['comments'] ?? 0),
                // Legacy endorse stores reposts and saves in share_save. Keep the
                // individual values in the job result as well so zero is not lost.
                'share' => (int) ($post['reposts'] ?? $post['shares'] ?? 0),
                'collect' => (int) ($post['saves'] ?? 0),
                'view' => (int) ($post['views'] ?? 0),
                'media_type' => (string) ($post['media_type'] ?? ''),
                'cover' => (string) ($post['media_url'] ?? $post['image_url'] ?? ''),
                'video_link' => '',
                'created_at' => (string) ($post['posted_at'] ?? $post['datetime'] ?? ''),
                'url' => $canonicalUrl,
                'reposts' => (int) ($post['reposts'] ?? $post['shares'] ?? 0),
                'saves' => (int) ($post['saves'] ?? 0),
                'quotes' => (int) ($post['quotes'] ?? 0),
                'caption' => (string) ($post['caption'] ?? $post['message'] ?? ''),
                'account' => (string) ($post['account'] ?? ''),
                'author_id' => (string) ($post['author_id'] ?? ''),
            ],
            'stats_fields' => ['like', 'comment', 'share', 'view'],
            'stats_source' => 'threads_scraper',
            'http_status' => 200,
        ];
    }

    protected function request(string $method, string $path, ?array $body = null): array
    {
        if ($this->baseUrl === '' || $this->apiKey === '') {
            return self::failure('Konfigurasi Social Scraper belum lengkap.', 'config');
        }
        if (stripos($this->baseUrl, 'https://') !== 0) {
            return self::failure('Social Scraper harus memakai HTTPS.', 'config');
        }

        $curl = curl_init($this->baseUrl . $path);
        $headers = ['Accept: application/json', 'X-API-Key: ' . $this->apiKey];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => min(10, $this->timeout),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ];
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $retryAfter = defined('CURLINFO_RETRY_AFTER') ? (int) (curl_getinfo($curl, CURLINFO_RETRY_AFTER) ?: 0) : 0;
        curl_close($curl);

        if ($errno !== 0) {
            $class = $errno === CURLE_OPERATION_TIMEDOUT ? 'infra_stall' : ($errno === CURLE_COULDNT_RESOLVE_HOST ? 'infra_dns' : 'infra_connect');
            return self::failure('Social Scraper transport error: ' . $error, $class);
        }
        $data = json_decode((string) $response, true);
        if ($status < 200 || $status >= 300 || !is_array($data)) {
            $class = in_array($status, [401, 403], true) ? 'config' : (($status === 400 || $status === 404) ? 'permanent' : 'transient');
            $result = self::failure((string) ($data['detail'] ?? ('Social Scraper gagal (HTTP ' . $status . ').')), $class, $status);
            if ($status === 429 && $retryAfter > 0) {
                $result['retry_after'] = $retryAfter;
            }
            return $result;
        }

        return ['status' => true, 'msg' => 'OK', 'data' => $data, 'http_status' => $status];
    }

    public static function canonicalPostUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        if (!is_array($parts)) return '';
        $host = strtolower(preg_replace('#^www\\.#', '', (string) ($parts['host'] ?? '')));
        if (!in_array($host, ['threads.com', 'threads.net'], true)) return '';
        $path = rtrim((string) ($parts['path'] ?? ''), '/');
        if (!preg_match('#^/@[^/]+/post/[^/]+$#', $path)) return '';
        return 'https://www.threads.net' . $path;
    }

    protected static function failure(string $msg, string $class, int $httpStatus = 0): array
    {
        return ['status' => false, 'msg' => $msg, 'data' => [], 'error_class' => $class, 'http_status' => $httpStatus];
    }
}
