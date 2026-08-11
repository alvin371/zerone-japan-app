<?php
/**
 * Read-only Endorse V2 legacy-history reconciliation manifest generator.
 *
 * Usage:
 *   php tools/endorse_v2_history_dry_run.php --campaign=24 [--output=/tmp/manifest.json]
 *
 * The tool performs SELECT statements only. It deliberately refuses an
 * unscoped production run; use a restored/staging database for --all.
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$options = getopt('', ['campaign:', 'all', 'output::', 'summary']);
if (!isset($options['campaign']) && !isset($options['all'])) {
    fwrite(STDERR, "Use --campaign=<id>; --all is reserved for restored/staging databases.\n");
    exit(2);
}
if (isset($options['all']) && getenv('ENDORSE_V2_DRY_RUN_ALLOW_ALL') !== '1') {
    fwrite(STDERR, "Refusing unscoped dry run: set ENDORSE_V2_DRY_RUN_ALLOW_ALL=1 only on a restored/staging database.\n");
    exit(2);
}

$campaignId = isset($options['campaign']) ? (int) $options['campaign'] : null;
if ($campaignId !== null && $campaignId <= 0) {
    fwrite(STDERR, "campaign must be a positive integer\n");
    exit(2);
}

$appRoot = rtrim((string) (getenv('ENDORSE_V2_APP_ROOT') ?: '/var/www/html'), '/');
define('BASEPATH', __DIR__);
define('APPPATH', $appRoot . '/application/');
define('FCPATH', $appRoot . '/');
require APPPATH . 'helpers/env_helper.php';

$db = new mysqli(env('DB_HOSTNAME'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), (int) env('DB_PORT', 3306));
if ($db->connect_errno) {
    fwrite(STDERR, "Database connection failed\n");
    exit(1);
}
$db->set_charset('utf8mb4');

$where = $campaignId === null ? '' : 'WHERE l.id_campaign = ' . $campaignId;
$sql = "SELECT
            l.id AS legacy_log_id, l.id_endorse, l.id_campaign AS legacy_campaign_id,
            l.date AS legacy_timestamp, DATE(l.date) AS observation_date,
            l.views_before, l.views, l.views_after,
            l.likes_before, l.likes, l.likes_after,
            l.comment_before, l.comment, l.comment_after,
            l.share_save_before, l.share_save, l.share_save_after,
            l.created_at, l.updated_at,
            e.id AS endorse_parent_id, e.id_campaign AS current_campaign_id
        FROM endorse_logs l
        LEFT JOIN endorse e ON e.id = l.id_endorse
        {$where}
        ORDER BY l.id_endorse ASC, DATE(l.date) ASC, l.id ASC";

$result = $db->query($sql, MYSQLI_USE_RESULT);
if (!$result) {
    fwrite(STDERR, "Read query failed\n");
    exit(1);
}

$groups = [];
while ($row = $result->fetch_assoc()) {
    $key = (string) $row['id_endorse'] . '|' . (string) $row['observation_date'];
    if (!isset($groups[$key])) {
        $groups[$key] = [];
    }
    $groups[$key][] = $row;
}
$result->free();

$manifestGroups = [];
$summary = [
    'source_groups' => 0,
    'source_rows' => 0,
    'single' => 0,
    'identical_duplicate' => 0,
    'review_required' => 0,
    'invalid' => 0,
    'baseline_candidates' => 0,
    'campaign_attribution_issues' => 0,
    'generation_issues' => 0,
];
$firstGroupByEndorse = [];

foreach ($groups as $rows) {
    $first = $rows[0];
    $endorseId = (int) $first['id_endorse'];
    $date = (string) $first['observation_date'];
    $sourceIds = array_map(static fn(array $row): int => (int) $row['legacy_log_id'], $rows);
    sort($sourceIds, SORT_NUMERIC);
    $summary['source_groups']++;
    $summary['source_rows'] += count($rows);

    $snapshots = [];
    foreach ($rows as $row) {
        $snapshot = [
            'id_endorse' => (int) $row['id_endorse'],
            'campaign_id' => (int) $row['legacy_campaign_id'],
            'date' => (string) $row['observation_date'],
            'views_before' => $row['views_before'], 'views' => $row['views'], 'views_after' => $row['views_after'],
            'likes_before' => $row['likes_before'], 'likes' => $row['likes'], 'likes_after' => $row['likes_after'],
            'comments_before' => $row['comment_before'], 'comments' => $row['comment'], 'comments_after' => $row['comment_after'],
            'share_save_before' => $row['share_save_before'], 'share_save' => $row['share_save'], 'share_save_after' => $row['share_save_after'],
        ];
        $snapshots[hash('sha256', json_encode($snapshot, JSON_UNESCAPED_SLASHES))] = $snapshot;
    }

    $issues = [];
    $classification = 'SINGLE';
    $selectedId = count($rows) === 1 ? $sourceIds[0] : null;
    if ($endorseId <= 0 || $date === '' || $first['endorse_parent_id'] === null) {
        $classification = 'INVALID';
        $issues[] = 'missing_or_invalid_endorse_parent';
        $summary['invalid']++;
    } elseif (count($snapshots) > 1) {
        $classification = 'REVIEW_REQUIRED';
        $issues[] = 'conflicting_source_rows';
        $summary['review_required']++;
    } elseif (count($rows) > 1) {
        $classification = 'IDENTICAL_DUPLICATE';
        $selectedId = $sourceIds[0];
        $summary['identical_duplicate']++;
    } else {
        $summary['single']++;
    }

    if ($first['current_campaign_id'] !== null && (int) $first['legacy_campaign_id'] !== (int) $first['current_campaign_id']) {
        $issues[] = 'campaign_attribution_requires_historical_review';
        $summary['campaign_attribution_issues']++;
    }

    $baselineCandidate = !isset($firstGroupByEndorse[$endorseId]);
    if ($baselineCandidate) {
        $firstGroupByEndorse[$endorseId] = true;
        $summary['baseline_candidates']++;
    }

    // V2 has no historical identity-generation ledger. A current content state
    // cannot safely be projected backwards, so this dry run never invents one.
    $issues[] = 'content_generation_unresolved';
    $summary['generation_issues']++;

    $representative = null;
    foreach ($rows as $row) {
        if ($selectedId !== null && (int) $row['legacy_log_id'] === $selectedId) {
            $representative = $row;
            break;
        }
    }

    $manifestGroups[] = [
        'source_row_ids' => $sourceIds,
        'endorse_id' => $endorseId,
        'legacy_campaign_id' => (int) $first['legacy_campaign_id'],
        'current_campaign_id' => $first['current_campaign_id'] === null ? null : (int) $first['current_campaign_id'],
        'legacy_date' => $date,
        'normalized_observation_date' => $date,
        'content_generation' => null,
        'generation_status' => 'REVIEW_REQUIRED',
        'classification' => $classification,
        'selected_representative_legacy_log_id' => $selectedId,
        'baseline_candidate' => $baselineCandidate,
        'provenance' => 'legacy_import',
        'issues' => $issues,
        'representative_metrics' => $representative === null ? null : [
            'views_before' => $representative['views_before'], 'views' => $representative['views'], 'views_after' => $representative['views_after'],
            'likes_before' => $representative['likes_before'], 'likes' => $representative['likes'], 'likes_after' => $representative['likes_after'],
            'comments_before' => $representative['comment_before'], 'comments' => $representative['comment'], 'comments_after' => $representative['comment_after'],
            'share_save_before' => $representative['share_save_before'], 'share_save' => $representative['share_save'], 'share_save_after' => $representative['share_save_after'],
        ],
    ];
}

$deterministicPayload = ['campaign_id' => $campaignId, 'groups' => $manifestGroups];
$fingerprint = hash('sha256', json_encode($deterministicPayload, JSON_UNESCAPED_SLASHES));
$manifest = [
    'mode' => 'dry_run_read_only',
    'import_run_id' => 'dryrun:' . $fingerprint,
    'generated_at_utc' => gmdate('c'),
    'campaign_id' => $campaignId,
    'deterministic_manifest_sha256' => $fingerprint,
    'summary' => $summary,
    'groups' => isset($options['summary']) ? null : $manifestGroups,
];

$json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
$output = $options['output'] ?? 'php://stdout';
if (@file_put_contents($output, $json) === false) {
    fwrite(STDERR, "Could not write manifest\n");
    exit(1);
}
