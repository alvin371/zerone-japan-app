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
}
