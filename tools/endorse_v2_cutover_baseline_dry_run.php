<?php
/**
 * Read-only manifest of V2 baselines that would be created at cutover.
 * Usage: php tools/endorse_v2_cutover_baseline_dry_run.php --campaign=49 --observed-at=2026-08-10T12:00:00Z
 */
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI only\n"); exit(1); }
$options = getopt('', ['campaign:', 'all', 'observed-at:', 'summary']);
if ((!isset($options['campaign']) && !isset($options['all'])) || empty($options['observed-at'])) {
    fwrite(STDERR, "Use --campaign=<id> --observed-at=<UTC ISO-8601>; --all requires ENDORSE_V2_DRY_RUN_ALLOW_ALL=1.\n"); exit(2);
}
if (isset($options['all']) && getenv('ENDORSE_V2_DRY_RUN_ALLOW_ALL') !== '1') {
    fwrite(STDERR, "Refusing unscoped dry run without ENDORSE_V2_DRY_RUN_ALLOW_ALL=1.\n"); exit(2);
}
$campaignId = isset($options['campaign']) ? (int) $options['campaign'] : null;
if ($campaignId !== null && $campaignId <= 0) { fwrite(STDERR, "campaign must be positive\n"); exit(2); }
try { $observedAt = (new DateTimeImmutable($options['observed-at']))->setTimezone(new DateTimeZone('UTC')); }
catch (Throwable $e) { fwrite(STDERR, "observed-at must be ISO-8601\n"); exit(2); }

$appRoot = rtrim((string) (getenv('ENDORSE_V2_APP_ROOT') ?: '/var/www/html'), '/');
define('BASEPATH', __DIR__); define('APPPATH', $appRoot . '/application/'); define('FCPATH', $appRoot . '/');
require APPPATH . 'helpers/env_helper.php';
require APPPATH . 'libraries/EndorseV2Identity.php';
$db = new mysqli(env('DB_HOSTNAME'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), (int) env('DB_PORT', 3306));
if ($db->connect_errno) { fwrite(STDERR, "Database connection failed\n"); exit(1); }
$db->set_charset('utf8mb4');

$where = "e.status='Aktif' AND e.link_upload <> ''";
if ($campaignId !== null) $where .= ' AND e.id_campaign=' . $campaignId;
$sql = "SELECT e.id,e.id_campaign,e.platform,e.link_upload,e.views,e.likes,e.comment,e.share_save,
               s.content_generation,s.trusted_views,s.trusted_likes,s.trusted_comments,s.trusted_share_save,
               EXISTS(SELECT 1 FROM endorse_v2_metric_observations o WHERE o.endorse_id=e.id AND o.content_generation=COALESCE(s.content_generation,1)) AS has_v2_observation
        FROM endorse e LEFT JOIN endorse_v2_content_state s ON s.endorse_id=e.id
        WHERE {$where} ORDER BY e.id";
$result = $db->query($sql, MYSQLI_USE_RESULT);
if (!$result) { fwrite(STDERR, "Read query failed\n"); exit(1); }
$rows = [];
while ($row = $result->fetch_assoc()) {
    $generation = (int) ($row['content_generation'] ?: 1);
    $identity = EndorseV2Identity::identify((string) $row['platform'], (string) $row['link_upload']);
    $metrics = [
        'views' => $row['trusted_views'] !== null ? (int) $row['trusted_views'] : (int) $row['views'],
        'likes' => $row['trusted_likes'] !== null ? (int) $row['trusted_likes'] : (int) $row['likes'],
        'comments' => $row['trusted_comments'] !== null ? (int) $row['trusted_comments'] : (int) $row['comment'],
        'share_save' => $row['trusted_share_save'] !== null ? (int) $row['trusted_share_save'] : (int) $row['share_save'],
    ];
    $rows[] = [
        'endorse_id' => (int) $row['id'], 'campaign_id' => (int) $row['id_campaign'], 'content_generation' => $generation,
        'platform' => $identity['platform'], 'platform_content_id' => $identity['platform_content_id'],
        'observation_date' => $observedAt->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d'),
        'observed_at_utc' => $observedAt->format('Y-m-d H:i:s.u'),
        'baseline_reason' => 'first_generation_observation', 'provenance' => 'cutover_baseline',
        'current_trusted_metrics' => $metrics,
        'proposed_action' => (int) $row['has_v2_observation'] ? 'skip_existing_observation' : 'create_baseline',
    ];
}
$result->free();
$hash = hash('sha256', json_encode(['campaign_id'=>$campaignId, 'observed_at'=>$observedAt->format('c'), 'rows'=>$rows], JSON_UNESCAPED_SLASHES));
$manifest = [
    'mode' => 'dry_run_read_only',
    'run_id' => 'cutover-baseline:' . $hash,
    'campaign_id' => $campaignId,
    'observed_at_utc' => $observedAt->format('c'),
    'deterministic_manifest_sha256' => $hash,
    'summary' => [
        'candidates' => count($rows),
        'create_baseline' => count(array_filter($rows, static fn($row) => $row['proposed_action'] === 'create_baseline')),
    ],
    'rows' => isset($options['summary']) ? null : $rows,
];
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
