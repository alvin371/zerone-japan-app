<?php

class Template
{
    public function index() {}

    function endpoint_url()
    {
        // return 'https://endpoint.acnenosystem.com/';
        return base_url();
    }

    function hex_to_rgb($hex, $opacity = 1)
    {
        // Remove '#' if present
        $hex = str_replace('#', '', $hex);

        // Extract RGB components
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Ensure opacity value is within range [0, 1]
        $opacity = max(0, min(1, $opacity));

        // Construct RGBA string
        $rgba = "rgba($r, $g, $b, $opacity)";

        return $rgba;
    }

    function generateNumber($length = 12)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomCode = '';

        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomCode;
    }

    function get_param()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $params);
        // unset($params['page']);
        $new_query_string = http_build_query($params);
        return '?' . $new_query_string;
    }
    function get_param_without($column)
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $params);
        unset($params['page']);
        unset($params[$column]);
        $new_query_string = http_build_query($params);
        return '?' . $new_query_string;
    }
    function get_param_without_page()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $params);
        unset($params['page']);
        $new_query_string = http_build_query($params);
        return '?' . $new_query_string;
    }
    function get_param_without_order_status()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $params);
        unset($params['order_status']);
        unset($params['page']);
        $new_query_string = http_build_query($params);
        return '?' . $new_query_string;
    }
    function get_param_without_status()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $params);
        unset($params['status']);
        unset($params['page']);
        $new_query_string = http_build_query($params);
        return '?' . $new_query_string;
    }
    function get_param_without_keyword_category()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $params);
        unset($params['keyword_category']);
        unset($params['page']);
        $new_query_string = http_build_query($params);
        return '?' . $new_query_string;
    }
    function month_format_indo($date)
    {
        $month = array(
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $date = $month[intval(DATE('m', strtotime($date)))];
        return $date;
    }
    function date_format_indo($date)
    {
        $month = array(
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        if ($date) {
            $date = DATE('d', strtotime($date)) . ' ' . substr($month[intval(DATE('m', strtotime($date)))], 0, 3) . ' ' . DATE('Y', strtotime($date));
        } else {
            $date = '';
        }
        return $date;
    }
    function date_format_indo_with_time($date)
    {
        $month = array(
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        if ($date) {
            $date = DATE('d', strtotime($date)) . ' ' . substr($month[intval(DATE('m', strtotime($date)))], 0, 3) . ' ' . DATE('Y', strtotime($date)) . ' ' . DATE('H:i', strtotime($date));
        } else {
            $date = '';
        }
        return $date;
    }
    function api_key_ss()
    {
        return 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI3IiwianRpIjoiNDZmNzZmMjQ2MGU0MjMwY2Q4MzZhNTIxOWMzMjNiMjFhMGVlNjUyODFjMGI4MmMzZTZlN2UwMzIxNWM1OWJmNGEyMjcyNzBlNDdhMGRlMTQiLCJpYXQiOjE2OTA1OTgwODkuMzYxNTA0LCJuYmYiOjE2OTA1OTgwODkuMzYxNTA5LCJleHAiOjE3MjIyMjA0ODkuMzMzNjkxLCJzdWIiOiIxMTE1NzEiLCJzY29wZXMiOltdfQ.JXeGlDb5EawVXIFAD9Si-GWgWqv9OF5hFPU2lUUuY_9frcQm-5jfy198czITk3aQNjMTSkRLPooXr2q8_P8VO4m3iyP6l9GZdK_oE6ttGj4hI0cIJEwy9cmT77JLqLe1s0ROLRtMINGUwHEBIauSTFYZLd34BAd6bAC_QxcbFUUsvaOacVnrmv6SdSS6tThsioSH4lZ7IAaF9A7m1yEkt4rQqqrjZhANhE5aq8BoQXQh4pMYpqR4BuydcwSVZTBJg-L19q0jA9-CTgVKON_j0rfUtOx5etvZB_oqJkfs4bHzCfctnnFiasL30ZWp9TO9VtvgsWx72osNGMVwBzILu_TizvmZLwZJGkKWLlstwsmrb9ggdbT45NJVa_Qf7MwAQRwTmWJOdy8MdPGzcdBTLGI5mC_NTFToYWRP1-5ljmeM1lllG2e77rnnnYhtRCMYrpf2yIIsGzq25n1yrzfydu_k4-ledyRus9X0vSPyiiS61fZypBamXXx1oYps-euVGFmxw4N5Tl6LY-w5m4jKHHRUQ3Yq8IbBp5Pq-gZo_HvMq0PNUx_rag9ElVTCAYo19JOSGAg_faH9sE-E_fI9tb1QRncxYKU9E20VyAmuNj-emN6tK5svU4uHaigNses8AdMgMYmJLNdPsxVVksxVTBMGVn0fdzB1VkoUqyBciw0';
    }
    function api_orders_ss($dt)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.smartseller.co.id/api/open/order?start_date=' . $dt['start_date'] . '&end_date=' . $dt['until_date'] . '&per_page=100&pagination_offset=' . $dt['page'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->api_key_ss()
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    function api_products_ss($dt)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.smartseller.co.id/api/open/product?per_page=100&pagination_offset=' . $dt['page'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->api_key_ss()
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    function pagination($page, $current_page, $url)
    {
        if ($page > 0) {
            $page_limit = 4;
            if ($page < 5) {
                $page_limit = $page - 1;
            }

            $item .= '<a class="btn btn-pagination me-1" style="margin-bottom:4px" style="margin-bottom:10px" href="' . $url . '&page=1"><i class="bi bi-chevron-double-left"></i></a>';
            if ($current_page <= 1) {
                $prev_page = 1;
            } else {
                $prev_page = $current_page - 1;
            }
            $item .= '<a class="btn btn-pagination me-1" style="margin-bottom:4px" style="margin-bottom:10px" href="' . $url . '&page=' . ($prev_page) . '"><i class="bi bi-chevron-left"></i></a>';
            $start = $current_page - 2;
            $end = $current_page + 2;
            if ($end > $page) {
                $end = $page;
                $start = $end - $page_limit;
            }
            if ($start < 1) {
                $start = 1;
                $end = $start + $page_limit;
            }
            for ($i = $start; $i <= $end; $i++) {
                if ($current_page != $i) {
                    $class = 'btn-pagination';
                } else {
                    $class = 'btn-pagination-active';
                }
                $item .= '<a class="btn ' . $class . ' me-1" style="margin-bottom:4px" style="margin-bottom:10px" href="' . $url . '&page=' . ($i) . '">' . $i . '</a>';
            }
            $next_page = $current_page + 1;
            if ($next_page > $page) {
                $next_page = $page;
            }
            $item .= '<a class="btn btn-pagination me-1" style="margin-bottom:4px" style="margin-bottom:10px" href="' . $url . '&page=' . ($next_page) . '"><i class="bi bi-chevron-right"></i></a>';
            $item .= '<a class="btn btn-pagination me-1" style="margin-bottom:4px" style="margin-bottom:10px" href="' . $url . '&page=' . ($page) . '"><i class="bi bi-chevron-double-right"></i></a>';
        } else {
            $item .= '<div class="bg-danger p-3 br-10">Hasil pencarian tidak ditemukan, silahkan gunakan filter lain!</div>';
        }
        return '<div class="col-md-12">' . $item . '</div>';
    }

    function option_pagination($page, $current_page, $url)
    {
        if ($page > 0) {
            $page_limit = 4;
            if ($page < 5) {
                $page_limit = $page - 1;
            }

            $item = '<a class="btn btn-pagination me-1" href="' . $url . '&page=1&limit=' . $limit . '"><i class="bi bi-chevron-double-left"></i></a>';

            $prev_page = ($current_page <= 1) ? 1 : $current_page - 1;
            $item .= '<a class="btn btn-pagination me-1" href="' . $url . '&page=' . $prev_page . '&limit=' . $limit . '"><i class="bi bi-chevron-left"></i></a>';

            $start = max(1, $current_page - 2);
            $end = min($page, $current_page + 2);

            for ($i = $start; $i <= $end; $i++) {
                $class = ($current_page == $i) ? 'btn-pagination-active' : 'btn-pagination';
                $item .= '<a class="btn ' . $class . ' me-1" href="' . $url . '&page=' . $i . '&limit=' . $limit . '">' . $i . '</a>';
            }

            $next_page = ($current_page >= $page) ? $page : $current_page + 1;
            $item .= '<a class="btn btn-pagination me-1" href="' . $url . '&page=' . $next_page . '&limit=' . $limit . '"><i class="bi bi-chevron-right"></i></a>';
            $item .= '<a class="btn btn-pagination me-1" href="' . $url . '&page=' . $page . '&limit=' . $limit . '"><i class="bi bi-chevron-double-right"></i></a>';
        } else {
            $item = '<div class="bg-danger p-3 br-10">Hasil pencarian tidak ditemukan, silahkan gunakan filter lain!</div>';
        }

        return '<div class="col-md-12">' . $item . '</div>';
    }

    function curlRequest($url, $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $meta = [
            "url" => $url,
            "http_code" => $info['http_code'] ?? null,
            "total_time" => isset($info['total_time']) ? round($info['total_time'], 3) : null,
            "curl_error" => $err ?: null,
            "response_snippet" => $this->shorten_text($response, 200),
        ];

        if ($err) {
            return [
                "status" => false,
                "msg" => "cURL Error: $err",
                "data" => [],
                "__meta" => $meta
            ];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return [
                "status" => false,
                "msg" => "Invalid JSON response",
                "data" => [],
                "__meta" => $meta
            ];
        }

        $decoded["__meta"] = $meta;
        return $decoded;
    }

    function getRapidApiHeaders($withAccept = false)
    {
        $rapidapi_host = env('RAPIDAPI_HOST', 'tiktok-api23.p.rapidapi.com');
        $rapidapi_key = env('RAPIDAPI_KEY', '');

        $headers = array(
            "x-rapidapi-host: {$rapidapi_host}",
            "x-rapidapi-key: {$rapidapi_key}",
        );

        if ($withAccept) {
            $headers[] = "Accept: application/json";
        }

        return $headers;
    }

    function curlRequestWithRetry($url, $headers, $isValidResponse, $maxRetry = 3, $delayMs = 300, $debugContext = array())
    {
        $lastResponse = null;
        for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
            $lastResponse = $this->curlRequest($url, $headers);
            $isValid = false;
            if (is_callable($isValidResponse)) {
                $isValid = $isValidResponse($lastResponse);
            }

            if (!empty($debugContext)) {
                $this->log_rapidapi_debug($debugContext, $lastResponse, $attempt, $maxRetry, $isValid);
            }

            if ($isValid) {
                return $lastResponse;
            }

            if ($attempt < $maxRetry) {
                usleep($delayMs * 1000);
            }
        }

        return $lastResponse;
    }

    function getDataFromFirstEndpoint($username)
    {
        $rapidapi_host = env('RAPIDAPI_HOST', 'tiktok-api23.p.rapidapi.com');
        $url = "https://{$rapidapi_host}/api/user/info?uniqueId=" . urlencode($username);
        $headers = $this->getRapidApiHeaders();

        return $this->curlRequestWithRetry($url, $headers, function ($resp) {
            $ok = isset($resp['status_code'])
                ? intval($resp['status_code']) === 0
                : (isset($resp['statusCode']) && intval($resp['statusCode']) === 0);

            return $ok && !empty($resp['userInfo']['user']['secUid']);
        }, 3, 300, array(
            'source' => 'get_account_id',
            'endpoint' => '/api/user/info',
            'username' => $username
        ));
    }

    function getDataFromSearchEndpoint($username)
    {
        $rapidapi_host = env('RAPIDAPI_HOST', 'tiktok-api23.p.rapidapi.com');
        $url = "https://{$rapidapi_host}/api/search/account?keyword="
            . urlencode($username) . "&cursor=0&search_id=0";
        $headers = $this->getRapidApiHeaders();

        return $this->curlRequestWithRetry($url, $headers, function ($resp) {
            $ok = isset($resp['status_code'])
                ? intval($resp['status_code']) === 0
                : (isset($resp['statusCode']) && intval($resp['statusCode']) === 0);

            return $ok && !empty($resp['user_list'][0]['user_info']);
        }, 3, 300, array(
            'source' => 'get_account_id',
            'endpoint' => '/api/search/account',
            'username' => $username
        ));
    }

    function getDataFromSecondEndpoint($username)
    {
        // Backward compatibility alias.
        return $this->getDataFromSearchEndpoint($username);
    }

    function shorten_text($text, $max = 160)
    {
        $text = (string) $text;
        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, $max - 3) . '...';
    }

    function extract_response_message($response)
    {
        if (!is_array($response)) {
            return '';
        }

        $message = $response['message'] ?? ($response['msg'] ?? ($response['error'] ?? ''));
        if ($message !== '') {
            return strtolower((string) $message);
        }

        $nested = $response['data']['message'] ?? ($response['data']['msg'] ?? '');
        return strtolower((string) $nested);
    }

    function is_rate_limited_response($response)
    {
        if (!is_array($response)) {
            return false;
        }

        $httpCode = intval($response['__meta']['http_code'] ?? 0);
        if ($httpCode === 429) {
            return true;
        }

        $message = $this->extract_response_message($response);
        if ($message === '') {
            return false;
        }

        return (strpos($message, 'too many requests') !== false)
            || (strpos($message, 'rate limit') !== false)
            || (strpos($message, 'quota') !== false);
    }

    function sanitize_endpoint_response($response)
    {
        if (!is_array($response)) {
            return [
                "status" => null,
                "message" => null,
                "data_type" => gettype($response),
                "data_count" => null,
                "__meta" => null
            ];
        }

        $data = $response['data'] ?? null;
        return [
            "status" => $response['status'] ?? null,
            "message" => $response['message'] ?? ($response['msg'] ?? null),
            "data_type" => gettype($data),
            "data_count" => is_array($data) ? count($data) : null,
            "__meta" => $response['__meta'] ?? null
        ];
    }

    function log_endpoint_trace($event, $data = [])
    {
        $entry = [
            "ts" => date('c'),
            "event" => $event,
            "data" => $data
        ];

        $path = APPPATH . 'logs/endpoint_trace.log';
        @file_put_contents($path, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    function is_rapidapi_debug_enabled()
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        $raw = strtolower(trim((string) env('RAPIDAPI_DEBUG', 'false')));
        $enabled = in_array($raw, array('1', 'true', 'yes', 'on'), true);
        return $enabled;
    }

    function extract_api_status_code($response)
    {
        if (!is_array($response)) {
            return null;
        }

        if (isset($response['status_code'])) {
            return intval($response['status_code']);
        }
        if (isset($response['statusCode'])) {
            return intval($response['statusCode']);
        }
        if (isset($response['data']['status_code'])) {
            return intval($response['data']['status_code']);
        }
        if (isset($response['data']['statusCode'])) {
            return intval($response['data']['statusCode']);
        }

        return null;
    }

    function log_rapidapi_debug($context, $response, $attempt = null, $maxRetry = null, $isValid = null)
    {
        if (!$this->is_rapidapi_debug_enabled()) {
            return;
        }

        $meta = is_array($response) ? ($response['__meta'] ?? array()) : array();
        $message = '';
        if (is_array($response)) {
            $message = $response['message'] ?? ($response['msg'] ?? ($response['error'] ?? ($response['data']['message'] ?? '')));
        }

        $entry = array(
            'ts' => date('c'),
            'event' => 'rapidapi_debug',
            'context' => $context,
            'attempt' => $attempt !== null ? intval($attempt) : null,
            'max_retry' => $maxRetry !== null ? intval($maxRetry) : null,
            'is_valid' => $isValid === null ? null : boolval($isValid),
            'http_code' => intval($meta['http_code'] ?? 0),
            'api_status' => is_array($response) ? ($response['status'] ?? ($response['data']['status'] ?? null)) : null,
            'api_status_code' => $this->extract_api_status_code($response),
            'rate_limited' => $this->is_rate_limited_response($response),
            'message' => $this->shorten_text((string) $message, 220),
            'curl_error' => strval($meta['curl_error'] ?? ''),
            'url' => strval($meta['url'] ?? ''),
            'response_snippet' => $this->shorten_text((string) ($meta['response_snippet'] ?? ''), 500),
        );

        $path = APPPATH . 'logs/rapidapi_debug.log';
        @file_put_contents($path, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    function format_endpoint_detail($label, $response, $missing = '')
    {
        $parts = array();

        if (!is_array($response)) {
            $parts[] = 'response tidak valid';
        } else {
            $status = $response['status'] ?? 'unknown';
            $parts[] = 'status=' . $status;

            $message = $response['message'] ?? ($response['msg'] ?? ($response['error'] ?? ''));
            if ($message !== '') {
                $message = htmlspecialchars($this->shorten_text($message), ENT_QUOTES, 'UTF-8');
                $parts[] = 'message=' . $message;
            }

            if (!empty($response['code'])) {
                $parts[] = 'code=' . $response['code'];
            }

            $data = $response['data'] ?? null;
            if (empty($data)) {
                $parts[] = 'data=kosong';
            }
        }

        if ($missing !== '') {
            $parts[] = 'missing=' . $missing;
        }

        return $label . ' (' . implode(', ', $parts) . ')';
    }

    function get_account_id($type, $url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $uri = explode("/", trim((string) $path, '/'));
        $username = $uri[0] ?? null;

        if (empty($url) || empty($username)) {
            return [
                "status" => false,
                "msg" => "Pastikan URL sudah diisi!",
                "data" => []
            ];
        }

        if ($type == "Instagram") {
            return [
                "status" => false,
                "msg" => "Instagram profile sync berjalan async via queue",
                "data" => []
            ];
        } else if ($type == "Threads") {
            return [
                "status" => false,
                "msg" => "Threads profile sync berjalan async via queue",
                "data" => []
            ];
        } else if ($type == "Facebook") {
            return [
                "status" => false,
                "msg" => "Facebook profile sync berjalan async via queue",
                "data" => []
            ];
        } else if ($type == "Tiktok") {
            $username = str_replace('@', '', $username);

            $resp1 = $this->getDataFromFirstEndpoint($username);
            $resp2 = null;

            if (!empty($resp1['userInfo']['user']['secUid'])) {
                $userInfo = $resp1['userInfo']['user'] ?? array();
                $stats = $resp1['userInfo']['stats'] ?? array();

                return [
                    "status" => true,
                    "msg" => "Data ditemukan",
                    "data" => [
                        "account_id"  => strval($userInfo['secUid'] ?? ''),
                        "follower"    => intval($stats['followerCount'] ?? 0),
                        "media_count" => intval($stats['videoCount'] ?? 0),
                        "img"         => strval($userInfo['avatarLarger'] ?? ''),
                        "full_name"   => strval($userInfo['nickname'] ?? ($userInfo['uniqueId'] ?? '')),
                        "username"    => strval($userInfo['uniqueId'] ?? ''),
                        "source"      => "first_endpoint"
                    ]
                ];
            }

            $resp2 = $this->getDataFromSearchEndpoint($username);
            if (!empty($resp2['user_list'][0]['user_info'])) {
                $userData = $resp2['user_list'][0]['user_info'];

                return [
                    "status" => true,
                    "msg"    => "Data ditemukan",
                    "data"   => [
                        "account_id"  => strval($userData['sec_uid'] ?? ''),
                        "follower"    => intval($userData['follower_count'] ?? 0),
                        "media_count" => intval($userData['item_count'] ?? ($userData['video_count'] ?? 0)),
                        "img"         => strval($userData['avatar_thumb']['url_list'][0] ?? ''),
                        "full_name"   => strval($userData['nickname'] ?? ($userData['unique_id'] ?? '')),
                        "username"    => strval($userData['unique_id'] ?? ''),
                        "source"      => "search_endpoint"
                    ]
                ];
            }

            $rateLimited1 = $this->is_rate_limited_response($resp1);
            $rateLimited2 = $this->is_rate_limited_response($resp2);
            if ($rateLimited1 || $rateLimited2) {
                $this->log_endpoint_trace('tiktok_rate_limited', [
                    "username" => $username,
                    "url" => $url,
                    "endpoint_1" => $this->sanitize_endpoint_response($resp1),
                    "endpoint_2" => $this->sanitize_endpoint_response($resp2)
                ]);

                return [
                    "status" => false,
                    "code" => "rate_limited",
                    "retryable" => true,
                    "msg" => "Request TikTok sedang dibatasi (Too many requests). Silakan refresh lagi dalam 1-2 menit.",
                    "data" => []
                ];
            }

            $detail_1 = $this->format_endpoint_detail('Endpoint 1: user/info', $resp1, 'secUid');
            $detail_2 = $this->format_endpoint_detail('Endpoint 2: search/account', $resp2, 'user_info');

            $this->log_endpoint_trace('tiktok_user_not_found', [
                "username" => $username,
                "url" => $url,
                "endpoint_1" => $this->sanitize_endpoint_response($resp1),
                "endpoint_2" => $this->sanitize_endpoint_response($resp2)
            ]);

            return [
                "status" => false,
                "msg" => "Username <b>$username</b> tidak ditemukan dari kedua endpoint.<br><small>$detail_1<br>$detail_2</small>",
                "data" => []
            ];
        } else {
            return [
                "status" => false,
                "msg" => "Platform belum tersedia",
                "data" => []
            ];
        }
    }


    function get_post_list($type, $account_id)
    {
        $response = array();
        $response["status"] = true;
        $response["msg"] = "";
        $response["data"] = array();

        if (empty($account_id)) {
            $response["status"] = false;
            $response["msg"] = "Pastikan account id sudah diisi!";
            $response["data"] = array();
        } else if ($type == "Instagram") {
            $response["status"] = false;
            $response["msg"] = "Layanan belum tersedia";
            $response["data"] = array();
            // $curl = curl_init();
            // $end_cursor = '';
            // curl_setopt_array($curl, [
            //     CURLOPT_URL => "https://api.instagapi.com/userreels/$account_id/10/$end_cursor",
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 30,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "GET",
            //     CURLOPT_HTTPHEADER => [
            //         "X-InstagAPI-Key: 0aaf6108af3c2962ff24720ffe09748b"
            //     ],
            // ]);

            // $response = curl_exec($curl);
            // $err = curl_error($curl);

            // curl_close($curl);

            // $response = json_decode($response, true);
            // if ($response['data']['items']) {
            //     $response["status"] = true;
            //     $response["msg"] = "Data ditemukan";
            //     $arr = array();
            //     foreach ($response['data']['items'] as $k => $v) {
            //         $detail = $v['media'];
            //         $arr[$k]["like"] = intval($detail['like_count']);
            //         $arr[$k]["share"] = intval($detail['stats']['shareCount']);
            //         $arr[$k]["comment"] = intval($detail['comment_count']);
            //         $arr[$k]["collect"] = intval($detail['stats']['collectCount']);
            //         $arr[$k]["view"] = intval($detail['play_count']);
            //     }
            //     $response["data"] = $arr;
            // } else {
            //     $response["status"] = false;
            //     $response["msg"] = "Data reels account id :  <b>" . $account_id . "</b> tidak ditemukan";
            //     $response["data"] = array();
            // }
        } else if ($type == "Tiktok") {
            $rapidapi_host = env('RAPIDAPI_HOST', 'tiktok-api23.p.rapidapi.com');
            $url = "https://{$rapidapi_host}/api/user/posts?secUid="
                . urlencode($account_id) . "&count=10&cursor=0";

            $resp = $this->curlRequestWithRetry($url, $this->getRapidApiHeaders(true), function ($r) {
                $dataBlock = $r['data'] ?? array();
                $ok = isset($dataBlock['status_code'])
                    ? intval($dataBlock['status_code']) === 0
                    : (isset($dataBlock['statusCode']) && intval($dataBlock['statusCode']) === 0);

                return $ok && !empty($dataBlock['itemList']);
            }, 3, 300, array(
                'source' => 'get_post_list',
                'endpoint' => '/api/user/posts',
                'account_id' => strval($account_id)
            ));

            $items = $resp['data']['itemList'] ?? array();
            if (!empty($items)) {
                $response["status"] = true;
                $response["msg"] = "Data ditemukan";

                $items = array_slice($items, 0, 10);
                $arr = array();
                foreach ($items as $k => $v) {
                    $detail = $v['stats'] ?? array();
                    $arr[$k]["like"] = intval($detail['diggCount'] ?? 0);
                    $arr[$k]["share"] = intval($detail['shareCount'] ?? 0);
                    $arr[$k]["comment"] = intval($detail['commentCount'] ?? 0);
                    $arr[$k]["collect"] = intval($detail['collectCount'] ?? 0);
                    $arr[$k]["view"] = intval($detail['playCount'] ?? 0);
                }
                $response["data"] = $arr;
            } else {
                if ($this->is_rate_limited_response($resp)) {
                    $response["status"] = false;
                    $response["code"] = "rate_limited";
                    $response["retryable"] = true;
                    $response["msg"] = "Request TikTok sedang dibatasi (Too many requests). Silakan refresh lagi dalam 1-2 menit.";
                    $response["data"] = array();
                    return $response;
                }

                $response["status"] = false;
                $response["msg"] = "Data video tiktok account id :  <b>" . $account_id . "</b> tidak ditemukan";
                $response["data"] = array();
            }

        } else {
            $response["status"] = false;
            $response["msg"] = "Platform belum tersedia";
            $response["data"] = array();
        }
        return $response;
    }

    function get_social_media($type, $url)
    {
        $response = array();
        $response["status"] = true;
        $response["msg"] = "";
        $response["data"] = array();
        if ($type == "Tiktok") {
            if (!$url) {
                $response["status"] = false;
                $response["msg"] = "URL tidak ditemukan";
                $response["data"] = array();
                return $response;
            }

            $content_id = '';
            if (preg_match('/\/video\/(\d+)/', $url, $matches)) {
                $content_id = $matches[1];
            } else if (preg_match('/\/photo\/(\d+)/', $url, $matches)) {
                $content_id = $matches[1];
            } else if (preg_match('/(\d{10,25})/', $url, $matches)) {
                $content_id = $matches[1];
            }

            if (!$content_id) {
                $response["status"] = false;
                $response["msg"] = "ID konten TikTok tidak ditemukan dari URL";
                $response["data"] = array();
                return $response;
            }

            $rapidapi_host = env('RAPIDAPI_HOST', 'tiktok-api23.p.rapidapi.com');
            $detail_url = "https://{$rapidapi_host}/api/post/detail?videoId=" . $content_id;
            $resp = $this->curlRequestWithRetry($detail_url, $this->getRapidApiHeaders(), function ($r) {
                $ok = isset($r['status_code'])
                    ? intval($r['status_code']) === 0
                    : (isset($r['statusCode']) && intval($r['statusCode']) === 0);

                return $ok && !empty($r['itemInfo']['itemStruct']);
            }, 3, 300, array(
                'source' => 'get_social_media',
                'endpoint' => '/api/post/detail',
                'content_id' => strval($content_id)
            ));

            $itemStruct = $resp['itemInfo']['itemStruct'] ?? array();
            if (empty($itemStruct)) {
                $response["status"] = false;
                $response["msg"] = "Response tiktok " . $content_id . " tidak ditemukan";
                $response["data"] = array();
                return $response;
            }

            $stats = $itemStruct['stats'] ?? array();
            $response["status"] = true;
            $response["msg"] = "";
            $response["data"]["like"] = intval($stats['diggCount'] ?? 0);
            $response["data"]["share"] = intval($stats['shareCount'] ?? 0);
            $response["data"]["comment"] = intval($stats['commentCount'] ?? 0);
            $response["data"]["collect"] = intval($stats['collectCount'] ?? 0);
            $response["data"]["view"] = intval($stats['playCount'] ?? 0);
            if (!empty($itemStruct['createTime'])) {
                $response["data"]["created_at"] = date("Y-m-d", intval($itemStruct['createTime']));
            }
        } else if ($type == "Instagram") {
            $defaultData = array(
                'like' => 0,
                'share' => 0,
                'comment' => 0,
                'collect' => 0,
                'view' => 0,
                'created_at' => '',
            );

            if (!$url) {
                $response["status"] = false;
                $response["msg"] = "URL tidak ditemukan";
                $response["data"] = $defaultData;
                return $response;
            }

            $CI =& get_instance();
            $CI->load->library('scrapingbot');

            $start = $CI->scrapingbot->scrapeInstagramPost($url);
            if (empty($start['status']) || empty($start['responseId'])) {
                $response["status"] = false;
                $response["msg"] = $start['msg'] ?? "Gagal submit scraping Instagram";
                $response["data"] = $defaultData;
                return $response;
            }

            $maxAttempts = 12;
            $pollResult = null;
            for ($i = 0; $i < $maxAttempts; $i++) {
                $pollResult = $CI->scrapingbot->pollResult('instagramPost', $start['responseId']);
                if (($pollResult['status'] ?? '') === 'success') {
                    break;
                }
                if (($pollResult['status'] ?? '') === 'error') {
                    $response["status"] = false;
                    $response["msg"] = $pollResult['msg'] ?? "Scraping Instagram gagal";
                    $response["data"] = $defaultData;
                    return $response;
                }
                usleep(1500000);
            }

            if (($pollResult['status'] ?? '') !== 'success') {
                $response["status"] = false;
                $response["msg"] = "Timeout menunggu hasil scraping Instagram";
                $response["data"] = $defaultData;
                return $response;
            }

            $parsed = $this->parseInstagramPostResponse($pollResult['data'] ?? array());
            $response["status"] = true;
            $response["msg"] = "";
            $response["data"]["like"] = intval($parsed['like'] ?? 0);
            $response["data"]["share"] = intval($parsed['share'] ?? 0);
            $response["data"]["comment"] = intval($parsed['comment'] ?? 0);
            $response["data"]["collect"] = intval($parsed['collect'] ?? 0);
            $response["data"]["view"] = intval($parsed['view'] ?? 0);
            $response["data"]["created_at"] = strval($parsed['created_at'] ?? '');
        } else if ($type == "Threads") {
            $defaultData = array(
                'like' => 0,
                'share' => 0,
                'comment' => 0,
                'collect' => 0,
                'view' => 0,
                'created_at' => '',
            );

            if (!$url) {
                $response["status"] = false;
                $response["msg"] = "URL tidak ditemukan";
                $response["data"] = $defaultData;
                return $response;
            }

            $CI =& get_instance();
            $CI->load->library('scrapingbot');

            $start = $CI->scrapingbot->scrapeThreadsPost($url);
            if (empty($start['status']) || empty($start['responseId'])) {
                $response["status"] = false;
                $response["msg"] = $start['msg'] ?? "Gagal submit scraping Threads";
                $response["data"] = $defaultData;
                return $response;
            }

            $maxAttempts = 12;
            $pollResult = null;
            for ($i = 0; $i < $maxAttempts; $i++) {
                $pollResult = $CI->scrapingbot->pollResult('threadsPost', $start['responseId']);
                if (($pollResult['status'] ?? '') === 'success') {
                    break;
                }
                if (($pollResult['status'] ?? '') === 'error') {
                    $response["status"] = false;
                    $response["msg"] = $pollResult['msg'] ?? "Scraping Threads gagal";
                    $response["data"] = $defaultData;
                    return $response;
                }
                usleep(1500000);
            }

            if (($pollResult['status'] ?? '') !== 'success') {
                $response["status"] = false;
                $response["msg"] = "Timeout menunggu hasil scraping Threads";
                $response["data"] = $defaultData;
                return $response;
            }

            $parsed = $this->parseThreadsPostResponse($pollResult['data'] ?? array());
            $response["status"] = true;
            $response["msg"] = "";
            $response["data"]["like"] = intval($parsed['like'] ?? 0);
            $response["data"]["share"] = intval($parsed['share'] ?? 0);
            $response["data"]["comment"] = intval($parsed['comment'] ?? 0);
            $response["data"]["collect"] = intval($parsed['collect'] ?? 0);
            $response["data"]["view"] = intval($parsed['view'] ?? 0);
            $response["data"]["created_at"] = strval($parsed['created_at'] ?? '');
        } else {
            $response["status"] = false;
            $response["msg"] = "Platform belum tersedia";
            $response["data"] = array();
        }
        return $response;
    }

    function enqueue_scrape($entityType, $entityId, $type, $url, $priority = 5)
    {
        $CI =& get_instance();
        $CI->load->library('scrapingbot');

        if (empty($url) || empty($entityId) || empty($entityType)) {
            return ['status' => false, 'msg' => 'Parameter enqueue tidak lengkap'];
        }

        $params = $CI->scrapingbot->buildScrapeParams($type, $url);
        if (!$params) {
            return ['status' => false, 'msg' => 'Invalid platform or URL'];
        }

        $existing = $CI->db->select('id')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where_in('status', ['pending', 'submitted'])
            ->get('scraping_queue')
            ->num_rows();

        if ($existing > 0) {
            return ['status' => true, 'msg' => 'Already in queue'];
        }

        $CI->db->insert('scraping_queue', [
            'entity_type' => $entityType,
            'entity_id' => intval($entityId),
            'scraper' => $params['scraper'],
            'scrape_url' => json_encode($params['params']),
            'status' => 'pending',
            'priority' => intval($priority),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return ['status' => true, 'msg' => 'Added to queue'];
    }

    function enqueue_post_scrape($entityType, $entityId, $type, $url, $priority = 5)
    {
        $CI =& get_instance();
        $CI->load->library('scrapingbot');

        if (empty($url) || empty($entityId) || empty($entityType)) {
            return ['status' => false, 'msg' => 'Parameter enqueue tidak lengkap'];
        }

        $params = $CI->scrapingbot->buildPostScrapeParams($type, $url);
        if (!$params) {
            return ['status' => false, 'msg' => 'Platform post scraping belum didukung'];
        }

        $existing = $CI->db->select('id')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where_in('status', ['pending', 'submitted'])
            ->get('scraping_queue')
            ->num_rows();

        if ($existing > 0) {
            return ['status' => true, 'msg' => 'Already in queue'];
        }

        $CI->db->insert('scraping_queue', [
            'entity_type' => $entityType,
            'entity_id' => intval($entityId),
            'scraper' => $params['scraper'],
            'scrape_url' => json_encode($params['params']),
            'status' => 'pending',
            'priority' => intval($priority),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return ['status' => true, 'msg' => 'Added to queue'];
    }

    function parseTiktokProfileResponse($data)
    {
        $result = [
            'profile' => [
                'account_id' => '',
                'follower' => 0,
                'media_count' => 0,
                'img' => '',
                'full_name' => '',
            ],
            'posts' => [],
        ];

        if (empty($data)) {
            return $result;
        }

        $allPosts = [];
        if (isset($data[0]) && is_array($data[0])) {
            $allPosts = $data;

            $profileItem = null;
            foreach ($data as $item) {
                if (is_array($item) && ($item['type'] ?? null) === 'profile') {
                    $profileItem = $item;
                    break;
                }
            }
            $data = $profileItem ?? $data[0];
        }

        $result['profile']['account_id'] = strval($data['sec_uid'] ?? ($data['id'] ?? ''));
        $result['profile']['follower'] = intval($data['follower_count'] ?? ($data['followers'] ?? 0));
        $result['profile']['media_count'] = intval($data['videos_count'] ?? ($data['video_count'] ?? 0));
        $result['profile']['img'] = strval($data['avatar'] ?? ($data['avatar_thumb'] ?? ''));
        $result['profile']['full_name'] = strval($data['nickname'] ?? ($data['unique_id'] ?? ''));

        $videos = $data['top_videos'] ?? ($data['videos'] ?? ($allPosts ?: []));
        $videos = array_slice($videos, 0, 10);

        foreach ($videos as $k => $v) {
            $result['posts'][$k] = [
                'like' => intval($v['diggCount'] ?? ($v['likes'] ?? ($v['stats']['diggCount'] ?? 0))),
                'share' => intval($v['shareCount'] ?? ($v['shares'] ?? ($v['stats']['shareCount'] ?? 0))),
                'comment' => intval($v['commentCount'] ?? ($v['comments'] ?? ($v['stats']['commentCount'] ?? 0))),
                'collect' => intval($v['collectCount'] ?? ($v['saves'] ?? ($v['stats']['collectCount'] ?? 0))),
                'view' => intval($v['playCount'] ?? ($v['views'] ?? ($v['stats']['playCount'] ?? 0))),
            ];
        }

        return $result;
    }

    function parseInstagramProfileResponse($data)
    {
        $result = [
            'profile' => [
                'account_id' => '',
                'follower' => 0,
                'media_count' => 0,
                'img' => '',
                'full_name' => '',
            ],
            'posts' => [],
        ];

        if (empty($data)) {
            return $result;
        }

        $allPosts = [];
        if (isset($data[0]) && is_array($data[0])) {
            $allPosts = $data;

            $profileItem = null;
            foreach ($data as $item) {
                if (is_array($item) && ($item['type'] ?? null) === 'profile') {
                    $profileItem = $item;
                    break;
                }
            }
            $data = $profileItem ?? $data[0];
        }

        $result['profile']['account_id'] = strval($data['author_id'] ?? ($data['id'] ?? ($data['pk'] ?? '')));
        $result['profile']['follower'] = intval($data['follower_count'] ?? ($data['followers'] ?? 0));
        $result['profile']['media_count'] = intval($data['posts_count'] ?? ($data['post_count'] ?? ($data['media_count'] ?? 0)));
        $result['profile']['img'] = strval($data['profile_image_link'] ?? ($data['profile_picture'] ?? ($data['profile_pic_url'] ?? '')));
        $result['profile']['full_name'] = strval($data['profile_name'] ?? ($data['full_name'] ?? ($data['username'] ?? '')));

        $posts = $data['posts'] ?? ($data['edge_owner_to_timeline_media']['edges'] ?? ($allPosts ?: []));
        $posts = array_slice($posts, 0, 12);

        foreach ($posts as $k => $v) {
            $node = $v['node'] ?? $v;
            $result['posts'][$k] = [
                'like' => intval($node['like_count'] ?? ($node['edge_media_preview_like']['count'] ?? ($node['likes'] ?? 0))),
                'share' => 0,
                'comment' => intval($node['comment_count'] ?? ($node['edge_media_to_comment']['count'] ?? ($node['comments'] ?? 0))),
                'collect' => 0,
                'view' => intval($node['video_view_count'] ?? ($node['views'] ?? 0)),
            ];
        }

        return $result;
    }

    function normalizeMetricNumber($value)
    {
        if (is_int($value) || is_float($value)) {
            return intval($value);
        }

        if (is_string($value)) {
            $raw = trim($value);
            if ($raw === '') {
                return 0;
            }

            $normalized = strtoupper(str_replace([',', ' '], ['', ''], $raw));
            if (preg_match('/^([0-9]+(?:\.[0-9]+)?)([KMB])$/', $normalized, $m)) {
                $num = floatval($m[1]);
                $suffix = $m[2];
                if ($suffix === 'K') {
                    return intval($num * 1000);
                }
                if ($suffix === 'M') {
                    return intval($num * 1000000);
                }
                if ($suffix === 'B') {
                    return intval($num * 1000000000);
                }
            }

            $digits = preg_replace('/[^0-9]/', '', $raw);
            if ($digits !== '') {
                return intval($digits);
            }
        }

        return 0;
    }

    private function firstValueByKeys($data, $keys, $default = null)
    {
        if (!is_array($data)) {
            return $default;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return $default;
    }

    private function normalizeScrapeDate($value)
    {
        if (is_numeric($value)) {
            $ts = intval($value);
            if ($ts > 9999999999) {
                $ts = intval($ts / 1000);
            }

            return $ts > 0 ? date('Y-m-d', $ts) : '';
        }

        if (is_string($value) && trim($value) !== '') {
            $ts = strtotime($value);
            return $ts ? date('Y-m-d', $ts) : '';
        }

        return '';
    }

    function parseThreadsProfileResponse($data)
    {
        $result = [
            'profile' => [
                'account_id' => '',
                'follower' => 0,
                'media_count' => 0,
                'img' => '',
                'full_name' => '',
            ],
            'posts' => [],
        ];

        if (empty($data)) {
            return $result;
        }

        $allPosts = [];
        if (isset($data[0]) && is_array($data[0])) {
            $allPosts = $data;

            $profileItem = null;
            foreach ($data as $item) {
                if (!is_array($item)) {
                    continue;
                }

                if (($item['type'] ?? null) === 'profile') {
                    $profileItem = $item;
                    break;
                }

                if (
                    isset($item['follower_count']) ||
                    isset($item['followers']) ||
                    isset($item['profile_name']) ||
                    isset($item['username'])
                ) {
                    $profileItem = $item;
                    break;
                }
            }
            $data = $profileItem ?? $data[0];
        }

        $result['profile']['account_id'] = strval($data['author_id'] ?? ($data['id'] ?? ($data['pk'] ?? ($data['username'] ?? ''))));
        $result['profile']['follower'] = $this->normalizeMetricNumber($data['follower_count'] ?? ($data['followers'] ?? ($data['followers_count'] ?? 0)));
        $result['profile']['media_count'] = $this->normalizeMetricNumber($data['posts_count'] ?? ($data['post_count'] ?? ($data['media_count'] ?? ($data['threads_count'] ?? 0))));
        $result['profile']['img'] = strval($data['profile_image_link'] ?? ($data['profile_picture'] ?? ($data['profile_pic_url'] ?? ($data['avatar'] ?? ''))));
        $result['profile']['full_name'] = strval($data['profile_name'] ?? ($data['full_name'] ?? ($data['username'] ?? ($data['name'] ?? ''))));

        $posts = $data['posts'] ?? ($data['threads'] ?? ($data['top_posts'] ?? ($allPosts ?: [])));
        $posts = array_slice($posts, 0, 12);

        foreach ($posts as $k => $v) {
            $node = $v['node'] ?? $v;
            if (($node['type'] ?? '') === 'profile') {
                continue;
            }
            $stats = $node['stats'] ?? ($node['statistics'] ?? []);

            $result['posts'][$k] = [
                'like' => $this->normalizeMetricNumber($node['like_count'] ?? ($node['likes'] ?? ($stats['like_count'] ?? ($stats['likes'] ?? 0)))),
                'share' => $this->normalizeMetricNumber($node['repost_count'] ?? ($node['share_count'] ?? ($node['shares'] ?? ($stats['repost_count'] ?? ($stats['share_count'] ?? 0))))),
                'comment' => $this->normalizeMetricNumber($node['comment_count'] ?? ($node['reply_count'] ?? ($node['comments'] ?? ($stats['comment_count'] ?? ($stats['reply_count'] ?? 0))))),
                'collect' => $this->normalizeMetricNumber($node['save_count'] ?? ($node['bookmark_count'] ?? ($stats['save_count'] ?? ($stats['bookmark_count'] ?? 0)))),
                'view' => $this->normalizeMetricNumber($node['view_count'] ?? ($node['views'] ?? ($node['play_count'] ?? ($stats['view_count'] ?? ($stats['views'] ?? 0))))),
            ];
        }

        return $result;
    }

    function parseThreadsPostResponse($data)
    {
        $item = $data;
        if (isset($data[0]) && is_array($data[0])) {
            $item = $data[0];
            foreach ($data as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                if (($candidate['type'] ?? '') === 'profile') {
                    continue;
                }
                $item = $candidate;
                break;
            }
        }

        if (isset($item['post']) && is_array($item['post'])) {
            $item = $item['post'];
        } else if (isset($item['thread']) && is_array($item['thread'])) {
            $item = $item['thread'];
        }

        $stats = $item['stats'] ?? ($item['statistics'] ?? ($item['engagement'] ?? []));

        $createdAt = $item['created_at'] ?? ($item['timestamp'] ?? ($item['date'] ?? ($item['taken_at'] ?? '')));
        if (is_numeric($createdAt)) {
            $ts = intval($createdAt);
            if ($ts > 9999999999) {
                $ts = intval($ts / 1000);
            }
            $createdAt = $ts > 0 ? date('Y-m-d', $ts) : '';
        } else if (is_string($createdAt) && trim($createdAt) !== '') {
            $ts = strtotime($createdAt);
            $createdAt = $ts ? date('Y-m-d', $ts) : '';
        } else {
            $createdAt = '';
        }

        return [
            'like' => $this->normalizeMetricNumber($item['like_count'] ?? ($item['likes'] ?? ($stats['like_count'] ?? ($stats['likes'] ?? 0)))),
            'share' => $this->normalizeMetricNumber($item['repost_count'] ?? ($item['share_count'] ?? ($item['shares'] ?? ($stats['repost_count'] ?? ($stats['share_count'] ?? 0))))),
            'comment' => $this->normalizeMetricNumber($item['comment_count'] ?? ($item['reply_count'] ?? ($item['comments'] ?? ($stats['comment_count'] ?? ($stats['reply_count'] ?? 0))))),
            'collect' => $this->normalizeMetricNumber($item['save_count'] ?? ($item['bookmark_count'] ?? ($stats['save_count'] ?? ($stats['bookmark_count'] ?? 0)))),
            'view' => $this->normalizeMetricNumber($item['view_count'] ?? ($item['views'] ?? ($item['play_count'] ?? ($stats['view_count'] ?? ($stats['views'] ?? 0))))),
            'created_at' => $createdAt,
        ];
    }

    function parseInstagramPostResponse($data)
    {
        $item = $data;
        if (isset($data[0]) && is_array($data[0])) {
            $item = $data[0];
            foreach ($data as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                if (($candidate['type'] ?? '') === 'profile') {
                    continue;
                }
                $item = $candidate;
                break;
            }
        }

        if (isset($item['post']) && is_array($item['post'])) {
            $item = $item['post'];
        } else if (isset($item['media']) && is_array($item['media'])) {
            $item = $item['media'];
        } else if (isset($item['node']) && is_array($item['node'])) {
            $item = $item['node'];
        }

        $stats = $item['stats'] ?? ($item['statistics'] ?? ($item['engagement'] ?? []));

        $createdAt = $item['created_at'] ?? ($item['timestamp'] ?? ($item['date'] ?? ($item['taken_at'] ?? '')));
        if (is_numeric($createdAt)) {
            $ts = intval($createdAt);
            if ($ts > 9999999999) {
                $ts = intval($ts / 1000);
            }
            $createdAt = $ts > 0 ? date('Y-m-d', $ts) : '';
        } else if (is_string($createdAt) && trim($createdAt) !== '') {
            $ts = strtotime($createdAt);
            $createdAt = $ts ? date('Y-m-d', $ts) : '';
        } else {
            $createdAt = '';
        }

        return [
            'like' => $this->normalizeMetricNumber($item['like_count'] ?? ($item['likes'] ?? ($stats['like_count'] ?? ($stats['likes'] ?? 0)))),
            'share' => $this->normalizeMetricNumber($item['share_count'] ?? ($item['shares'] ?? ($stats['share_count'] ?? ($stats['shares'] ?? 0)))),
            'comment' => $this->normalizeMetricNumber($item['comment_count'] ?? ($item['comments'] ?? ($stats['comment_count'] ?? ($stats['comments'] ?? 0)))),
            'collect' => $this->normalizeMetricNumber($item['save_count'] ?? ($item['bookmark_count'] ?? ($stats['save_count'] ?? ($stats['bookmark_count'] ?? 0)))),
            'view' => $this->normalizeMetricNumber($item['video_view_count'] ?? ($item['view_count'] ?? ($item['views'] ?? ($item['play_count'] ?? ($stats['view_count'] ?? ($stats['views'] ?? 0)))))),
            'created_at' => $createdAt,
        ];
    }

    function parseFacebookProfileResponse($data)
    {
        $result = [
            'profile' => [
                'account_id' => '',
                'follower' => 0,
                'media_count' => 0,
                'img' => '',
                'full_name' => '',
            ],
            'posts' => [],
        ];

        if (empty($data)) {
            return $result;
        }

        $allPosts = [];
        if (isset($data[0]) && is_array($data[0])) {
            $allPosts = $data;

            $profileItem = null;
            foreach ($data as $item) {
                if (!is_array($item)) {
                    continue;
                }

                if (($item['type'] ?? null) === 'profile') {
                    $profileItem = $item;
                    break;
                }

                if (
                    isset($item['followers']) ||
                    isset($item['followers_count']) ||
                    isset($item['follower_count']) ||
                    isset($item['page_name']) ||
                    isset($item['name'])
                ) {
                    $profileItem = $item;
                    break;
                }
            }
            $data = $profileItem ?? $data[0];
        }

        if (isset($data['profile']) && is_array($data['profile'])) {
            $data = $data['profile'];
        } else if (isset($data['page']) && is_array($data['page'])) {
            $data = $data['page'];
        }

        $stats = $data['stats'] ?? ($data['statistics'] ?? ($data['engagement'] ?? []));

        $result['profile']['account_id'] = strval($this->firstValueByKeys($data, ['author_id', 'page_id', 'id', 'profile_id', 'username'], ''));
        $result['profile']['follower'] = $this->normalizeMetricNumber(
            $this->firstValueByKeys($data, ['followers', 'followers_count', 'follower_count', 'fans', 'fans_count'], $this->firstValueByKeys($stats, ['followers', 'followers_count', 'follower_count', 'fans', 'fans_count'], 0))
        );
        $result['profile']['media_count'] = $this->normalizeMetricNumber(
            $this->firstValueByKeys($data, ['posts_count', 'post_count', 'media_count', 'videos_count'], $this->firstValueByKeys($stats, ['posts_count', 'post_count', 'media_count', 'videos_count'], 0))
        );
        $result['profile']['img'] = strval($this->firstValueByKeys($data, ['profile_image_link', 'profile_picture', 'profile_pic_url', 'avatar', 'image'], ''));
        $result['profile']['full_name'] = strval($this->firstValueByKeys($data, ['page_name', 'profile_name', 'full_name', 'name', 'username'], ''));

        $posts = $data['posts'] ?? ($data['top_posts'] ?? ($data['videos'] ?? ($allPosts ?: [])));
        $posts = array_slice($posts, 0, 12);

        foreach ($posts as $k => $v) {
            $node = $v['node'] ?? $v;
            if (!is_array($node) || ($node['type'] ?? '') === 'profile') {
                continue;
            }

            $postStats = $node['stats'] ?? ($node['statistics'] ?? ($node['engagement'] ?? []));

            $result['posts'][$k] = [
                'like' => $this->normalizeMetricNumber($this->firstValueByKeys($node, ['like_count', 'likes', 'reactions', 'reaction_count'], $this->firstValueByKeys($postStats, ['like_count', 'likes', 'reactions', 'reaction_count'], 0))),
                'share' => $this->normalizeMetricNumber($this->firstValueByKeys($node, ['share_count', 'shares', 'repost_count'], $this->firstValueByKeys($postStats, ['share_count', 'shares', 'repost_count'], 0))),
                'comment' => $this->normalizeMetricNumber($this->firstValueByKeys($node, ['comment_count', 'comments', 'reply_count'], $this->firstValueByKeys($postStats, ['comment_count', 'comments', 'reply_count'], 0))),
                'collect' => $this->normalizeMetricNumber($this->firstValueByKeys($node, ['save_count', 'bookmark_count'], $this->firstValueByKeys($postStats, ['save_count', 'bookmark_count'], 0))),
                'view' => $this->normalizeMetricNumber($this->firstValueByKeys($node, ['view_count', 'views', 'play_count', 'video_view_count'], $this->firstValueByKeys($postStats, ['view_count', 'views', 'play_count', 'video_view_count'], 0))),
            ];
        }

        return $result;
    }

    function parseFacebookPostResponse($data)
    {
        $item = $data;
        if (isset($data[0]) && is_array($data[0])) {
            $item = $data[0];
            foreach ($data as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                if (($candidate['type'] ?? '') === 'profile') {
                    continue;
                }
                $item = $candidate;
                break;
            }
        }

        if (isset($item['post']) && is_array($item['post'])) {
            $item = $item['post'];
        } else if (isset($item['media']) && is_array($item['media'])) {
            $item = $item['media'];
        } else if (isset($item['video']) && is_array($item['video'])) {
            $item = $item['video'];
        } else if (isset($item['reel']) && is_array($item['reel'])) {
            $item = $item['reel'];
        }

        $stats = $item['stats'] ?? ($item['statistics'] ?? ($item['engagement'] ?? []));
        $createdAt = $this->normalizeScrapeDate($this->firstValueByKeys($item, ['created_at', 'timestamp', 'date', 'taken_at', 'created_time'], ''));

        return [
            'like' => $this->normalizeMetricNumber($this->firstValueByKeys($item, ['like_count', 'likes', 'reactions', 'reaction_count'], $this->firstValueByKeys($stats, ['like_count', 'likes', 'reactions', 'reaction_count'], 0))),
            'share' => $this->normalizeMetricNumber($this->firstValueByKeys($item, ['share_count', 'shares', 'repost_count'], $this->firstValueByKeys($stats, ['share_count', 'shares', 'repost_count'], 0))),
            'comment' => $this->normalizeMetricNumber($this->firstValueByKeys($item, ['comment_count', 'comments', 'reply_count'], $this->firstValueByKeys($stats, ['comment_count', 'comments', 'reply_count'], 0))),
            'collect' => $this->normalizeMetricNumber($this->firstValueByKeys($item, ['save_count', 'bookmark_count'], $this->firstValueByKeys($stats, ['save_count', 'bookmark_count'], 0))),
            'view' => $this->normalizeMetricNumber($this->firstValueByKeys($item, ['view_count', 'views', 'play_count', 'video_view_count'], $this->firstValueByKeys($stats, ['view_count', 'views', 'play_count', 'video_view_count'], 0))),
            'created_at' => $createdAt,
        ];
    }

    function process_scrape_result($queueItem, $resultData)
    {
        $CI =& get_instance();
        $entityType = $queueItem['entity_type'];
        $entityId = intval($queueItem['entity_id']);
        $scraper = $queueItem['scraper'];

        if ($entityType === 'endorse') {
            return $this->process_endorse_post_result($queueItem, $resultData);
        }

        if (!in_array($entityType, ['influencer', 'influencer_dummy'])) {
            return false;
        }

        if ($scraper === 'tiktokProfile') {
            $parsed = $this->parseTiktokProfileResponse($resultData);
        } else if ($scraper === 'instagramProfile') {
            $parsed = $this->parseInstagramProfileResponse($resultData);
        } else if ($scraper === 'threadsProfile') {
            $parsed = $this->parseThreadsProfileResponse($resultData);
        } else if ($scraper === 'facebookProfile') {
            $parsed = $this->parseFacebookProfileResponse($resultData);
        } else {
            return false;
        }

        if (empty($parsed['profile']['account_id']) && $parsed['profile']['follower'] <= 0) {
            log_message('error', "ScrapingBot: Empty parse result for {$entityType}#{$entityId}, skipping update");
            return false;
        }

        $userId = strval($_SESSION['user']['id'] ?? '1');

        $profileUpdate = [
            'account_id' => $parsed['profile']['account_id'],
            'img' => $parsed['profile']['img'],
            'follower' => $parsed['profile']['follower'],
            'media_count' => $parsed['profile']['media_count'],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId,
        ];
        if (!empty($parsed['profile']['full_name'])) {
            $profileUpdate['full_name'] = $parsed['profile']['full_name'];
        }
        $CI->db->update($entityType, $profileUpdate, ['id' => $entityId]);

        if (!empty($parsed['posts'])) {
            $like = 0;
            $comment = 0;
            $collect = 0;
            $share = 0;
            $view = 0;
            $i = 0;

            foreach ($parsed['posts'] as $post) {
                $like += intval($post['like'] ?? 0);
                $comment += intval($post['comment'] ?? 0);
                $collect += intval($post['collect'] ?? 0);
                $share += intval($post['share'] ?? 0);
                $view += intval($post['view'] ?? 0);
                $i++;
                if ($i >= 10) {
                    break;
                }
            }

            $avg_view = $i ? $view / $i : 0;
            $avg_interaksi = $i ? ($like + $comment + $collect + $share) / $i : 0;
            $er = ($avg_view > 0) ? ($avg_interaksi / $avg_view * 100) : 0;

            $record = $CI->db->select('ratecard')->where('id', $entityId)->get($entityType)->row_array();
            $ratecard = floatval($record['ratecard'] ?? 0);
            $cpm = ($ratecard > 0 && $avg_view > 0) ? ($ratecard / $avg_view * 1000) : 0;

            $metricsUpdate = [
                'sync_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId,
                'frequency_2' => $i,
                'view_2' => $view,
                'like_2' => $like,
                'collect_2' => $collect,
                'share_2' => $share,
                'comment_2' => $comment,
                'avg_view_2' => $avg_view,
                'avg_interaksi_2' => $avg_interaksi,
                'er' => $er,
                'cpm_2' => $cpm,
            ];

            $CI->db->update($entityType, $metricsUpdate, ['id' => $entityId]);

            if ($entityType === 'influencer') {
                $today = date('Y-m-d');
                $existing = $CI->db->select('id')
                    ->where('id_influencer', $entityId)
                    ->where("DATE(date) = '{$today}'", null, false)
                    ->get('influencer_logs')
                    ->row_array();

                $logData = [
                    'like' => $like,
                    'comment' => $comment,
                    'collect' => $collect,
                    'share' => $share,
                    'view' => $view,
                    'avg_view' => $avg_view,
                    'avg_interaksi' => $avg_interaksi,
                    'er' => $er,
                    'sync_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($existing) {
                    $CI->db->update('influencer_logs', $logData, ['id' => $existing['id']]);
                } else {
                    $logData['id_influencer'] = $entityId;
                    $logData['date'] = $today;
                    $logData['status'] = 'Aktif';
                    $logData['created_at'] = date('Y-m-d H:i:s');
                    $CI->db->insert('influencer_logs', $logData);
                }
            }
        }

        return true;
    }

    function process_endorse_post_result($queueItem, $resultData)
    {
        $CI =& get_instance();

        $endorseId = intval($queueItem['entity_id'] ?? 0);
        if ($endorseId <= 0) {
            return false;
        }

        $scraper = strval($queueItem['scraper'] ?? '');
        if ($scraper === 'instagramPost') {
            $parsed = $this->parseInstagramPostResponse($resultData);
        } else if ($scraper === 'threadsPost') {
            $parsed = $this->parseThreadsPostResponse($resultData);
        } else if ($scraper === 'facebookPost') {
            $parsed = $this->parseFacebookPostResponse($resultData);
        } else {
            return false;
        }

        $endorse = $CI->db->where('id', $endorseId)->get('endorse')->row_array();
        if (!$endorse) {
            return false;
        }

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $userId = strval($_SESSION['user']['id'] ?? '1');

        $likesAfter = intval($parsed['like'] ?? 0);
        $commentAfter = intval($parsed['comment'] ?? 0);
        $shareSaveAfter = intval($parsed['share'] ?? 0) + intval($parsed['collect'] ?? 0);
        $viewsAfter = intval($parsed['view'] ?? 0);
        $createdAt = strval($parsed['created_at'] ?? '');

        if ($likesAfter <= 0 && $commentAfter <= 0 && $shareSaveAfter <= 0 && $viewsAfter <= 0 && $createdAt === '') {
            log_message('error', "ScrapingBot: Empty post parse result for endorse#{$endorseId}, skipping update");
            return false;
        }

        $lastLog = $CI->db->query(
            "SELECT likes_after, comment_after, share_save_after, views_after
             FROM endorse_logs
             WHERE id_endorse = ? AND date < ? AND views_after > 0
             ORDER BY date DESC
             LIMIT 1",
            [$endorseId, $today]
        )->row_array();

        $likesBefore = intval($lastLog['likes_after'] ?? 0);
        $commentBefore = intval($lastLog['comment_after'] ?? 0);
        $shareSaveBefore = intval($lastLog['share_save_after'] ?? 0);
        $viewsBefore = intval($lastLog['views_after'] ?? 0);

        $likesNow = $likesAfter - $likesBefore;
        $commentNow = $commentAfter - $commentBefore;
        $shareSaveNow = $shareSaveAfter - $shareSaveBefore;
        $viewsNow = $viewsAfter - $viewsBefore;

        $totalCost = doubleval($endorse['total_cost'] ?? 0);
        $cpmAfter = ($totalCost > 0 && $viewsAfter > 0) ? ($totalCost / $viewsAfter * 1000) : 0;
        $cpmBefore = ($totalCost > 0 && $viewsBefore > 0) ? ($totalCost / $viewsBefore * 1000) : 0;
        $cpmNow = ($totalCost > 0 && $viewsNow > 0) ? ($totalCost / $viewsNow * 1000) : 0;

        $endorseUpdate = [
            'likes' => $likesAfter,
            'comment' => $commentAfter,
            'share_save' => $shareSaveAfter,
            'views' => $viewsAfter,
            'cpm' => $cpmAfter,
            'sync_at' => $now,
            'updated_at' => $now,
            'updated_by' => $userId,
        ];

        if ($createdAt !== '') {
            $endorseUpdate['posting_at'] = $createdAt;
        }

        if ($viewsAfter >= 50000) {
            $follower = 0;
            $influencerId = intval($endorse['influencer'] ?? 0);
            if ($influencerId > 0) {
                $creator = $CI->db->select('follower')->where('id', $influencerId)->get('influencer')->row_array();
                $follower = intval($creator['follower'] ?? 0);
            }
            if ($follower <= 0 || $viewsAfter >= intval($follower * 30 / 100)) {
                $endorseUpdate['is_fyp'] = '1';
            }
        }

        $CI->db->update('endorse', $endorseUpdate, ['id' => $endorseId]);

        $logData = [
            'status' => strval($endorse['status'] ?? ''),
            'status_campaign' => strval($endorse['status_campaign'] ?? ''),
            'id_endorse' => strval($endorseId),
            'id_campaign' => strval($endorse['id_campaign'] ?? ''),
            'influencer' => strval($endorse['influencer'] ?? ''),
            'date' => $today,
            'likes' => strval($likesNow),
            'comment' => strval($commentNow),
            'share_save' => strval($shareSaveNow),
            'views' => strval($viewsNow),
            'cpm' => strval($cpmNow),
            'total_cost' => strval($totalCost),
            'link_upload' => strval($endorse['link_upload'] ?? ''),
            'platform' => strval($endorse['platform'] ?? ''),
            'likes_after' => strval($likesAfter),
            'comment_after' => strval($commentAfter),
            'share_save_after' => strval($shareSaveAfter),
            'views_after' => strval($viewsAfter),
            'cpm_after' => strval($cpmAfter),
            'likes_before' => strval($likesBefore),
            'comment_before' => strval($commentBefore),
            'share_save_before' => strval($shareSaveBefore),
            'views_before' => strval($viewsBefore),
            'cpm_before' => strval($cpmBefore),
            'brand' => strval($endorse['brand'] ?? ''),
        ];

        $existingLog = $CI->db->select('id')
            ->where('id_endorse', $endorseId)
            ->where('date', $today)
            ->get('endorse_logs')
            ->row_array();

        if ($existingLog) {
            $logData['updated_at'] = $now;
            $logData['updated_by'] = $userId;
            $CI->db->update('endorse_logs', $logData, ['id' => $existingLog['id']]);
        } else {
            $logData['created_at'] = $now;
            $logData['created_by'] = $userId;
            $CI->db->insert('endorse_logs', $logData);
        }

        $this->refresh_endorse_campaign_summary(intval($endorse['id_campaign'] ?? 0), $userId);

        return true;
    }

    private function refresh_endorse_campaign_summary($campaignId, $userId = '1')
    {
        $CI =& get_instance();

        $campaignId = intval($campaignId);
        if ($campaignId <= 0) {
            return false;
        }

        $summary = $CI->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'Aktif' THEN total_cost ELSE 0 END), 0) AS total_cost,
                COALESCE(SUM(CASE WHEN status = 'Aktif' THEN likes ELSE 0 END), 0) AS likes,
                COALESCE(SUM(CASE WHEN status = 'Aktif' THEN comment ELSE 0 END), 0) AS comment,
                COALESCE(SUM(CASE WHEN status = 'Aktif' THEN share_save ELSE 0 END), 0) AS share_save,
                COALESCE(SUM(CASE WHEN status = 'Aktif' THEN views ELSE 0 END), 0) AS views,
                COALESCE(AVG(CASE WHEN status = 'Aktif' THEN cpm END), 0) AS cpm,
                COUNT(id) AS count_endorse,
                SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) AS count_endorse_active,
                SUM(CASE WHEN status = 'Aktif' AND link_upload != '' THEN 1 ELSE 0 END) AS count_endorse_processed,
                COUNT(DISTINCT influencer) AS count_influencer,
                COUNT(DISTINCT CASE WHEN status = 'Aktif' THEN influencer END) AS count_influencer_active,
                COUNT(DISTINCT CASE WHEN status = 'Aktif' AND link_upload != '' THEN influencer END) AS count_influencer_processed
             FROM endorse
             WHERE id_campaign = ?",
            [$campaignId]
        )->row_array();

        if (!$summary) {
            return false;
        }

        $update = [
            'total_cost' => doubleval($summary['total_cost'] ?? 0),
            'likes' => doubleval($summary['likes'] ?? 0),
            'comment' => doubleval($summary['comment'] ?? 0),
            'share_save' => doubleval($summary['share_save'] ?? 0),
            'views' => doubleval($summary['views'] ?? 0),
            'cpm' => doubleval($summary['cpm'] ?? 0),
            'count_endorse' => intval($summary['count_endorse'] ?? 0),
            'count_endorse_active' => intval($summary['count_endorse_active'] ?? 0),
            'count_endorse_processed' => intval($summary['count_endorse_processed'] ?? 0),
            'count_influencer' => intval($summary['count_influencer'] ?? 0),
            'count_influencer_active' => intval($summary['count_influencer_active'] ?? 0),
            'count_influencer_processed' => intval($summary['count_influencer_processed'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => strval($userId),
        ];

        $CI->db->update('endorse_campaign', $update, ['id' => $campaignId]);
        return true;
    }

    function syncTiktokProfile($entityType, $entityId, $type, $url)
    {
        $CI =& get_instance();

        if ($type !== 'Tiktok') {
            return [
                'status' => false,
                'msg' => 'Platform bukan TikTok',
                'data' => []
            ];
        }

        if (!in_array($entityType, ['influencer', 'influencer_dummy'])) {
            return [
                'status' => false,
                'msg' => 'Entity type tidak didukung',
                'data' => []
            ];
        }

        $entityId = intval($entityId);
        if ($entityId <= 0 || empty($url)) {
            return [
                'status' => false,
                'msg' => 'Parameter tidak valid',
                'data' => []
            ];
        }

        $record = $CI->db->where('id', $entityId)->get($entityType)->row_array();
        if (!$record) {
            return [
                'status' => false,
                'msg' => 'Data tidak ditemukan',
                'data' => []
            ];
        }

        $profileResp = $this->get_account_id('Tiktok', $url);
        if (!$profileResp['status']) {
            return $profileResp;
        }

        $userId = strval($_SESSION['user']['id'] ?? '1');
        $profileData = $profileResp['data'];

        $profileUpdate = [
            'account_id' => strval($profileData['account_id'] ?? ''),
            'img' => strval($profileData['img'] ?? ''),
            'follower' => intval($profileData['follower'] ?? 0),
            'media_count' => intval($profileData['media_count'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId,
        ];
        if (!empty($profileData['full_name'])) {
            $profileUpdate['full_name'] = $profileData['full_name'];
        }
        $CI->db->update($entityType, $profileUpdate, ['id' => $entityId]);

        $postResp = $this->get_post_list('Tiktok', $profileUpdate['account_id']);
        if (!$postResp['status']) {
            return $postResp;
        }

        $like = 0;
        $comment = 0;
        $collect = 0;
        $share = 0;
        $view = 0;
        $i = 0;
        foreach ($postResp['data'] as $post) {
            $like += intval($post['like'] ?? 0);
            $comment += intval($post['comment'] ?? 0);
            $collect += intval($post['collect'] ?? 0);
            $share += intval($post['share'] ?? 0);
            $view += intval($post['view'] ?? 0);
            $i++;
            if ($i >= 10) {
                break;
            }
        }

        $avgView = $i ? $view / $i : 0;
        $avgInteraksi = $i ? ($like + $comment + $collect + $share) / $i : 0;
        $er = ($avgView > 0) ? ($avgInteraksi / $avgView * 100) : 0;
        $ratecard = floatval($record['ratecard'] ?? 0);
        $cpm = ($ratecard > 0 && $avgView > 0) ? ($ratecard / $avgView * 1000) : 0;

        $metricsUpdate = [
            'sync_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId,
            'frequency_2' => $i,
            'view_2' => $view,
            'like_2' => $like,
            'collect_2' => $collect,
            'share_2' => $share,
            'comment_2' => $comment,
            'avg_view_2' => $avgView,
            'avg_interaksi_2' => $avgInteraksi,
            'er' => $er,
            'cpm_2' => $cpm,
        ];

        $CI->db->update($entityType, $metricsUpdate, ['id' => $entityId]);

        if ($entityType === 'influencer') {
            $today = date('Y-m-d');
            $existingLog = $CI->db->select('id')
                ->where('id_influencer', $entityId)
                ->where('date', $today)
                ->get('influencer_logs')
                ->row_array();

            $logData = [
                'sync_at' => date('Y-m-d H:i:s'),
                'like' => $like,
                'comment' => $comment,
                'collect' => $collect,
                'share' => $share,
                'view' => $view,
                'avg_view' => $avgView,
                'avg_interaksi' => $avgInteraksi,
                'er' => $er,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existingLog) {
                $CI->db->update('influencer_logs', $logData, ['id' => $existingLog['id']]);
            } else {
                $logData['id_influencer'] = $entityId;
                $logData['date'] = $today;
                $logData['status'] = 'Aktif';
                $logData['created_at'] = date('Y-m-d H:i:s');
                $CI->db->insert('influencer_logs', $logData);
            }
        }

        return [
            'status' => true,
            'msg' => 'Data TikTok berhasil disinkronkan',
            'data' => [
                'profile' => $profileUpdate,
                'metrics' => $metricsUpdate,
            ]
        ];
    }

    function title()
    {
        return 'Zerone Japan App';
    }

    function hex($i)
    {
        $flat_colors = [
            "#009999",
            "#9999FF",
            "#FFD966",
            "#FF0066",
            "#5a99d4",
            "#71ad44",
            "#c4ddcb",
            "#1abc9c",
            "#2ecc71",
            "#3498db",
            "#9b59b6",
            "#34495e",
            "#16a085",
            "#27ae60",
            "#2980b9",
            "#8e44ad",
            "#2c3e50",
            "#f1c40f",
            "#e67e22",
            "#e74c3c",
            "#ecf0f1",
            "#95a5a6",
            "#f39c12",
            "#d35400",
            "#c0392b",
            "#bdc3c7",
            "#7f8c8d"
        ];
        return $flat_colors[$i];
    }
    function get_name_from_number($num)
    {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return $this->get_name_from_number($num2) . $letter;
        } else {
            return $letter;
        }
    }

    function set_session($var, $val)
    {
        $session = \Config\Services::session();
        $session->set($var, $val);
    }

    function get_session($var)
    {
        $session = \Config\Services::session();
        return $session->get($var);
    }

    function date_format($date)
    {
        return DATE("d-M-Y", strtotime($date));
    }

    function datetime_to_date($date)
    {
        return DATE("Y-m-d", strtotime($date));
    }
    function date_to_week($date)
    {
        return DATE("W", strtotime($date));
    }
    function date_to_month($date)
    {
        return DATE("M", strtotime($date));
    }
    function date_to_month_number($date)
    {
        return DATE("m", strtotime($date));
    }
    function date_to_year($date)
    {
        return DATE("Y", strtotime($date));
    }
    public function date_to_date($date)
    {
        $date = explode("-", $date);
        $arr = array();
        $arr['Jan'] = '01';
        $arr['Feb'] = '02';
        $arr['Mar'] = '03';
        $arr['Apr'] = '04';
        $arr['May'] = '05';
        $arr['Jun'] = '06';
        $arr['Jul'] = '07';
        $arr['Aug'] = '08';
        $arr['Sep'] = '09';
        $arr['Oct'] = '10';
        $arr['Nov'] = '11';
        $arr['Dec'] = '12';
        $date[1] = $arr[$date[1]];
        $date = $date[2] . '-' . $date[1] . '-' . $date[0];
        return $date;
    }

    function alert_danger($text)
    {
        $text = str_replace(array("\r", "\n"), '', $text);
        $text = '<script>
                        $( document ).ready(function() {
                        $.toast({
                            heading: "Informasi",
                            text: "' . $text . '",
                            showHideTransition: "slide",
                            icon: "error",
                            position: "top-right",
                            loaderBg: "#def7f0",
                            hideAfter: 5000, 
                        });
                    });
                </script>';
        return $text;
    }

    function alert_success($text)
    {
        $text = str_replace(array("\r", "\n"), '', $text);
        $text = '<script success type="text/javascript">
                    $( document ).ready(function() {
                    $.toast({
                        heading: "Informasi",
                        text: "' . $text . '",
                        showHideTransition: "slide",
                        icon: "success",
                        position: "top-right",
                        loaderBg: "#def7f0", 
                        hideAfter: 2500,
                    });
                });
            </script>';
        return $text;
    }

    function set_number($angka)
    {
        $angka = str_replace(',', '', $angka);
        // $angka = str_replace('.','',$angka);
        $angka = str_replace('Rp', '', $angka);
        return doubleval($angka);
    }

    public function separator_only($angka)
    {
        // echo $angka;die;
        // echo $angka;die;
        $angka = $this->set_number($angka);
        return number_format(doubleval($angka), 0, ',', '.');
    }


    public function separator($angka)
    {
        $number = $angka;
        if (floor($number) == $number) {
            // No decimal places
            return number_format($number, 0, ',', '.');
        } else {
            // With decimal places
            return number_format($number, 3, ',', '.');
        }
    }
    public function separator_1($angka)
    {
        $number = $angka;
        if (floor($number) == $number) {
            // No decimal places
            return number_format($number, 0, ',', '.');
        } else {
            // With decimal places
            return number_format($number, 1, ',', '.');
        }
    }
    public function separator_2($angka)
    {
        $number = $angka;
        if (floor($number) == $number) {
            // No decimal places
            return number_format($number, 0, ',', '.');
        } else {
            // With decimal places
            return number_format($number, 2, ',', '.');
        }
    }
    public function separator_number_only($angka)
    {
        $angka = $this->set_number($angka);
        return number_format(round($angka), 0, '.', '');
    }
    public function separator_number($angka)
    {
        $number = $angka;
        if (floor($number) == $number) {
            // No decimal places
            return number_format($number, 0, '.', '');
        } else {
            // With decimal places
            return number_format($number, 3, '.', '');
        }
    }
    public function separator_number_1($angka)
    {
        $number = $angka;
        if (floor($number) == $number) {
            // No decimal places
            return number_format($number, 0, '.', '');
        } else {
            // With decimal places
            return number_format($number, 1, '.', '');
        }
    }
    public function separator_number_2($angka)
    {
        $number = $angka;
        if (floor($number) == $number) {
            // No decimal places
            return number_format($number, 0, '.', '');
        } else {
            // With decimal places
            return number_format($number, 2, '.', '');
        }
    }
}
