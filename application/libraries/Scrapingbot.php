<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scrapingbot
{
    private $username;
    private $apiKey;
    private $baseUrl;
    private $threadsScrapBaseUrl;
    private $threadsScrapApiKey;
    private $threadsScrapTimeout;

    public function __construct()
    {
        $this->username = env('SCRAPINGBOT_USERNAME', '');
        $this->apiKey = env('SCRAPINGBOT_API_KEY', '');
        $this->baseUrl = rtrim(env('SCRAPINGBOT_BASE_URL', 'http://api.scraping-bot.io'), '/');
        $this->threadsScrapBaseUrl = rtrim(env('THREADS_SCRAP_BASE_URL', 'http://scrap.acnenosystem.com'), '/');
        $this->threadsScrapApiKey = env('THREADS_SCRAP_API_KEY', '');
        $this->threadsScrapTimeout = intval(env('THREADS_SCRAP_TIMEOUT', '30'));
        if ($this->threadsScrapTimeout <= 0) {
            $this->threadsScrapTimeout = 30;
        }
    }

    public function startScrape($scraper, $params = array())
    {
        if ($this->isThreadsScraper($scraper)) {
            return $this->startThreadsScrape($scraper, $params);
        }

        $url = $this->baseUrl . '/scrape/data-scraper';
        $body = array_merge(array('scraper' => $scraper), $params);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
            CURLOPT_USERPWD        => $this->username . ':' . $this->apiKey,
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return array('status' => false, 'responseId' => null, 'msg' => "cURL Error: $err");
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['responseId'])) {
            return array(
                'status'     => true,
                'responseId' => $data['responseId'],
                'msg'        => 'Scrape job submitted',
            );
        }

        return array(
            'status'     => false,
            'responseId' => null,
            'msg'        => 'Failed to start scrape: ' . ($data['message'] ?? $response),
        );
    }

    public function pollResult($scraper, $responseId)
    {
        if ($this->isThreadsScraper($scraper)) {
            return $this->pollThreadsResult($scraper, $responseId);
        }

        $url = $this->baseUrl . '/scrape/data-scraper-response?scraper='
            . urlencode($scraper) . '&responseId=' . urlencode($responseId);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_USERPWD        => $this->username . ':' . $this->apiKey,
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return array('status' => 'error', 'data' => null, 'msg' => "cURL Error: $err");
        }

        $data = json_decode($response, true);

        if ($httpCode == 200 && is_array($data)) {
            if (isset($data[0]) && !isset($data['status'])) {
                $firstItem = $data[0];
                if (isset($firstItem['message']) && ($firstItem['type'] ?? null) !== 'profile') {
                    return array(
                        'status' => 'error',
                        'data'   => null,
                        'msg'    => 'Scrape returned error: ' . $firstItem['message'],
                    );
                }

                return array(
                    'status' => 'success',
                    'data'   => $data,
                    'msg'    => 'Scrape completed',
                );
            }

            if (isset($data['status']) && $data['status'] === 'success') {
                return array(
                    'status' => 'success',
                    'data'   => $data['response'] ?? $data,
                    'msg'    => 'Scrape completed',
                );
            }

            if (isset($data['status']) && $data['status'] === 'pending') {
                return array(
                    'status' => 'pending',
                    'data'   => null,
                    'msg'    => 'Scrape still processing',
                );
            }

            if (isset($data['message']) && stripos($data['message'], 'not finished') !== false) {
                return array(
                    'status' => 'pending',
                    'data'   => null,
                    'msg'    => 'Scrape still processing',
                );
            }
        }

        if (is_string($response) && stripos($response, 'not finished') !== false) {
            return array(
                'status' => 'pending',
                'data'   => null,
                'msg'    => 'Scrape still processing',
            );
        }

        return array(
            'status' => 'error',
            'data'   => null,
            'msg'    => 'Scrape failed: ' . ($data['message'] ?? $response),
        );
    }

    private function isThreadsScraper($scraper)
    {
        return in_array(strval($scraper), array('threadsProfile', 'threadsPost'), true);
    }

    private function startThreadsScrape($scraper, $params = array())
    {
        if ($this->threadsScrapApiKey === '') {
            return array(
                'status' => false,
                'responseId' => null,
                'msg' => 'THREADS_SCRAP_API_KEY belum diatur',
            );
        }

        $url = trim(strval($params['url'] ?? ''));
        if ($url === '') {
            return array(
                'status' => false,
                'responseId' => null,
                'msg' => 'URL Threads tidak ditemukan',
            );
        }

        $path = ($scraper === 'threadsPost') ? '/api/v1/posts/scrape' : '/api/v1/accounts';
        $request = $this->threadsApiRequest('POST', $path, array('link' => $url));

        if (!$request['ok']) {
            return array(
                'status' => false,
                'responseId' => null,
                'msg' => 'Failed to start scrape: ' . $request['msg'],
            );
        }

        $data = $request['data'];
        $jobId = strval($data['job_id'] ?? ($data['id'] ?? ''));
        if ($jobId === '') {
            return array(
                'status' => false,
                'responseId' => null,
                'msg' => 'Failed to start scrape: job_id tidak ditemukan pada response',
            );
        }

        return array(
            'status' => true,
            'responseId' => $jobId,
            'msg' => 'Scrape job submitted',
        );
    }

    private function pollThreadsResult($scraper, $responseId)
    {
        if ($this->threadsScrapApiKey === '') {
            return array(
                'status' => 'error',
                'data' => null,
                'msg' => 'THREADS_SCRAP_API_KEY belum diatur',
            );
        }

        $jobId = trim(strval($responseId));
        if ($jobId === '') {
            return array(
                'status' => 'error',
                'data' => null,
                'msg' => 'Job ID Threads tidak valid',
            );
        }

        $request = $this->threadsApiRequest('GET', '/api/v1/jobs/' . rawurlencode($jobId));
        if (!$request['ok']) {
            if ($request['http_code'] >= 500) {
                return array(
                    'status' => 'pending',
                    'data' => null,
                    'msg' => 'Scrape still processing',
                );
            }

            return array(
                'status' => 'error',
                'data' => null,
                'msg' => 'Scrape failed: ' . $request['msg'],
            );
        }

        $data = $request['data'];
        $status = strtolower(trim(strval($data['status'] ?? '')));

        if (in_array($status, array('completed', 'success', 'done'), true)) {
            return array(
                'status' => 'success',
                'data' => $this->normalizeThreadsJobResult($scraper, $data),
                'msg' => 'Scrape completed',
            );
        }

        if (in_array($status, array('pending', 'queued', 'processing', 'started', 'running', 'in_progress'), true)) {
            return array(
                'status' => 'pending',
                'data' => null,
                'msg' => 'Scrape still processing',
            );
        }

        if (in_array($status, array('failed', 'error', 'cancelled', 'canceled'), true)) {
            $errorMsg = strval($data['error'] ?? ($data['message'] ?? 'Scrape failed'));
            return array(
                'status' => 'error',
                'data' => null,
                'msg' => 'Scrape failed: ' . $errorMsg,
            );
        }

        if (isset($data['result'])) {
            return array(
                'status' => 'success',
                'data' => $this->normalizeThreadsJobResult($scraper, $data),
                'msg' => 'Scrape completed',
            );
        }

        return array(
            'status' => 'pending',
            'data' => null,
            'msg' => 'Scrape still processing',
        );
    }

    private function normalizeThreadsJobResult($scraper, $jobData)
    {
        $result = $jobData['result'] ?? array();

        if ($scraper === 'threadsPost') {
            return is_array($result) ? $result : array();
        }

        if ($scraper === 'threadsProfile') {
            if (isset($result['data']) && is_array($result['data'])) {
                return $result['data'];
            }
            return is_array($result) ? $result : array();
        }

        return is_array($result) ? $result : array();
    }

    private function threadsApiRequest($method, $path, $body = null)
    {
        $url = $this->threadsScrapBaseUrl . '/' . ltrim(strval($path), '/');

        $curl = curl_init();
        $headers = array(
            'Accept: application/json',
            'X-API-Key: ' . $this->threadsScrapApiKey,
        );

        $method = strtoupper(strval($method));
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->threadsScrapTimeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        );

        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_POSTFIELDS] = json_encode($body ?? array());
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $httpCode = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return array(
                'ok' => false,
                'http_code' => $httpCode,
                'data' => null,
                'msg' => 'cURL Error: ' . $err,
            );
        }

        $data = json_decode(strval($response), true);
        $isJson = is_array($data);
        $ok = ($httpCode >= 200 && $httpCode < 300 && $isJson);

        if ($ok) {
            return array(
                'ok' => true,
                'http_code' => $httpCode,
                'data' => $data,
                'msg' => '',
            );
        }

        $message = '';
        if ($isJson) {
            $message = strval($data['message'] ?? ($data['error'] ?? ''));
        }
        if ($message === '') {
            $message = trim(strval($response));
        }
        if ($message === '') {
            $message = 'HTTP ' . $httpCode;
        }

        return array(
            'ok' => false,
            'http_code' => $httpCode,
            'data' => $isJson ? $data : null,
            'msg' => $message,
        );
    }

    public function scrapeTiktokProfile($url)
    {
        return $this->startScrape('tiktokProfile', array(
            'url' => $url,
        ));
    }

    public function scrapeInstagramProfile($account, $postsNumber = 12)
    {
        return $this->startScrape('instagramProfile', array(
            'account'      => $account,
            'posts_number' => $postsNumber,
        ));
    }

    public function scrapeInstagramPost($url)
    {
        return $this->startScrape('instagramPost', array(
            'url' => $url,
        ));
    }

    public function scrapeThreadsProfile($url)
    {
        return $this->startScrape('threadsProfile', array(
            'url' => $url,
        ));
    }

    public function scrapeThreadsPost($url)
    {
        return $this->startScrape('threadsPost', array(
            'url' => $url,
        ));
    }

    private function extractUsername($url)
    {
        $raw = trim((string) $url);
        if ($raw === '') {
            return '';
        }

        $path = parse_url($raw, PHP_URL_PATH);
        if (empty($path) && strpos($raw, '/') === false) {
            $path = $raw;
        }

        $uri = explode('/', trim((string) $path, '/'));
        $username = $uri[0] ?? '';
        $username = str_replace('@', '', $username);

        return trim($username);
    }

    public function buildScrapeParams($type, $url)
    {
        $username = $this->extractUsername($url);

        if (empty($username)) {
            return false;
        }

        if ($type === 'Tiktok') {
            return array(
                'scraper' => 'tiktokProfile',
                'params'  => array(
                    'url' => 'https://www.tiktok.com/@' . $username,
                ),
            );
        }

        if ($type === 'Instagram') {
            return array(
                'scraper' => 'instagramProfile',
                'params'  => array(
                    'account'      => $username,
                    'posts_number' => 12,
                ),
            );
        }

        if ($type === 'Threads') {
            return array(
                'scraper' => 'threadsProfile',
                'params'  => array(
                    'url' => 'https://www.threads.com/@' . $username,
                ),
            );
        }

        return false;
    }

    public function buildPostScrapeParams($type, $url)
    {
        $rawUrl = trim((string)$url);
        if ($rawUrl === '') {
            return false;
        }

        if ($type === 'Instagram') {
            return array(
                'scraper' => 'instagramPost',
                'params'  => array(
                    'url' => $rawUrl,
                ),
            );
        }

        if ($type === 'Threads') {
            return array(
                'scraper' => 'threadsPost',
                'params'  => array(
                    'url' => $rawUrl,
                ),
            );
        }

        return false;
    }
}
