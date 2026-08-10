<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Existing per-metric V2 trust policy, extracted for deterministic tests. */
class EndorseV2MetricTrustPolicy
{
    public static function trusted(array $previous, array $incoming, array $overrides): array
    {
        $views = array_key_exists('views', $overrides) ? $overrides['views'] : ($incoming['views'] === null ? (int) ($previous['views'] ?? 0) : max((int) ($previous['views'] ?? 0), $incoming['views']));
        $likes = array_key_exists('likes', $overrides) ? $overrides['likes'] : ($incoming['likes'] ?? (int) ($previous['likes'] ?? 0));
        $comments = array_key_exists('comments', $overrides) ? $overrides['comments'] : ($incoming['comments'] ?? (int) ($previous['comments'] ?? 0));
        $shares = array_key_exists('shares', $overrides) ? $overrides['shares'] : ($incoming['shares'] ?? null);
        $saves = array_key_exists('saves', $overrides) ? $overrides['saves'] : ($incoming['saves'] ?? null);
        $combined = ($shares === null && $saves === null) ? (int) ($previous['share_save'] ?? 0) : (int) ($shares ?? 0) + (int) ($saves ?? 0);
        return compact('views', 'likes', 'comments', 'shares', 'saves') + ['share_save' => $combined];
    }
}
