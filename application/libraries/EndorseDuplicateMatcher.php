<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/EndorseV2Identity.php';

/**
 * Compares endorse rows by social-post identity, independent of campaign and creator.
 */
class EndorseDuplicateMatcher
{
    public static function matches(array $current, array $candidate): bool
    {
        $currentLink = trim((string) ($current['link_upload'] ?? ''));
        $candidateLink = trim((string) ($candidate['link_upload'] ?? ''));
        if ($currentLink === '' || $candidateLink === '') {
            return false;
        }

        $currentIdentity = EndorseV2Identity::identify(
            (string) ($current['platform'] ?? ''),
            $currentLink
        );
        $candidateIdentity = EndorseV2Identity::identify(
            (string) ($candidate['platform'] ?? ''),
            $candidateLink
        );

        if (strcasecmp(
            (string) $currentIdentity['platform'],
            (string) $candidateIdentity['platform']
        ) !== 0) {
            return false;
        }

        $currentContentId = (string) ($currentIdentity['platform_content_id'] ?? '');
        $candidateContentId = (string) ($candidateIdentity['platform_content_id'] ?? '');
        if ($currentContentId !== '' && $candidateContentId !== '') {
            return $currentContentId === $candidateContentId;
        }

        return hash_equals(
            (string) $currentIdentity['canonical_url_hash'],
            (string) $candidateIdentity['canonical_url_hash']
        );
    }

    /**
     * Stable key for one social post; rows sharing a key are the same content.
     */
    public static function identityKey($platform, $link)
    {
        $platform = trim((string) $platform);
        $link = trim((string) $link);
        if ($platform === '' || $link === '') {
            return null;
        }

        $identity = EndorseV2Identity::identify($platform, $link);
        $contentId = (string) ($identity['platform_content_id'] ?? '');
        $suffix = $contentId !== ''
            ? $contentId
            : bin2hex((string) $identity['canonical_url_hash']);

        return strtolower((string) $identity['platform']) . '|' . $suffix;
    }

    /**
     * Groups endorse rows that share a post identity, keeping only real duplicates.
     * Rows of $currentCampaignId sort first inside each group; groups with the most
     * rows come first.
     */
    public static function groupDuplicates(array $rows, $currentCampaignId): array
    {
        $currentCampaignId = (int) $currentCampaignId;
        $groups = [];

        foreach ($rows as $row) {
            $key = self::identityKey($row['platform'] ?? '', $row['link_upload'] ?? '');
            if ($key === null) {
                continue;
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'platform' => (string) ($row['platform'] ?? ''),
                    'link_upload' => (string) ($row['link_upload'] ?? ''),
                    'items' => [],
                    'campaign_ids' => [],
                ];
            }

            $row['is_current_campaign'] = (int) ($row['id_campaign'] ?? 0) === $currentCampaignId;
            $groups[$key]['items'][] = $row;
            $groups[$key]['campaign_ids'][(int) ($row['id_campaign'] ?? 0)] = true;
        }

        $groups = array_filter($groups, static function ($group) {
            return count($group['items']) > 1;
        });

        foreach ($groups as $key => $group) {
            usort($groups[$key]['items'], static function ($a, $b) {
                if ($a['is_current_campaign'] !== $b['is_current_campaign']) {
                    return $a['is_current_campaign'] ? -1 : 1;
                }
                return strcmp((string) ($b['posting_at'] ?? ''), (string) ($a['posting_at'] ?? ''));
            });
            $groups[$key]['campaign_count'] = count($group['campaign_ids']);
        }

        uasort($groups, static function ($a, $b) {
            return count($b['items']) - count($a['items']);
        });

        return $groups;
    }
}
