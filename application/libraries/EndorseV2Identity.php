<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Pure social-content identity rules; campaign and creator metadata never change identity. */
class EndorseV2Identity
{
    public static function identify(string $platform, string $url): array
    {
        $platform = self::platform($platform);
        $canonical = self::canonicalUrl($url);
        return [
            'platform' => $platform,
            'canonical_url' => $canonical,
            'canonical_url_hash' => hash('sha256', $canonical, true),
            'platform_content_id' => self::contentId($platform, $canonical),
        ];
    }

    public static function requiresNewGeneration(array $old, array $new): bool
    {
        if (($old['platform'] ?? '') !== ($new['platform'] ?? '')) return true;
        $oldId = (string) ($old['platform_content_id'] ?? '');
        $newId = (string) ($new['platform_content_id'] ?? '');
        if ($oldId !== '' && $newId !== '') return $oldId !== $newId;
        return !hash_equals((string) ($old['canonical_url_hash'] ?? ''), (string) ($new['canonical_url_hash'] ?? ''));
    }

    public static function canonicalUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') return '';
        if (!preg_match('#^https?://#i', $url)) $url = 'https://' . ltrim($url, '/');
        $p = parse_url($url);
        if (!$p || empty($p['host'])) return $url;
        $scheme = 'https';
        $host = strtolower($p['host']);
        $path = preg_replace('#/+#', '/', $p['path'] ?? '/');
        $path = rtrim($path, '/') ?: '/';
        parse_str($p['query'] ?? '', $query);
        foreach (array_keys($query) as $key) if (preg_match('/^(utm_|fbclid$|gclid$|igsh)/i', $key)) unset($query[$key]);
        ksort($query);
        return $scheme . '://' . $host . $path . (empty($query) ? '' : '?' . http_build_query($query));
    }

    private static function platform(string $platform): string
    {
        $value = strtolower(trim($platform));
        if ($value === 'tiktok') return 'TikTok';
        if ($value === 'instagram') return 'Instagram';
        if ($value === 'threads') return 'Threads';
        return trim($platform);
    }

    private static function contentId(string $platform, string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if ($platform === 'TikTok' && preg_match('#/(?:video|photo)/(\d+)#', $path, $m)) return $m[1];
        if ($platform === 'Instagram' && preg_match('#/(?:p|reel)/([^/]+)#', $path, $m)) return $m[1];
        if ($platform === 'Threads' && preg_match('#/post/([^/]+)#', $path, $m)) return $m[1];
        return null;
    }
}
