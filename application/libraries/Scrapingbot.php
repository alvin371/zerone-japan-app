<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scrapingbot
{
    private $username;
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->username = env('SCRAPINGBOT_USERNAME', '');
        $this->apiKey = env('SCRAPINGBOT_API_KEY', '');
        $this->baseUrl = rtrim(env('SCRAPINGBOT_BASE_URL', 'http://api.scraping-bot.io'), '/');
    }

    public function startScrape($scraper, $params = array())
    {
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
                    'url' => 'https://www.threads.net/@' . $username,
                ),
            );
        }

        return false;
    }
}
