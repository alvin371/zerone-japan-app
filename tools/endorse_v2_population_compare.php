<?php
/** Read-only current-population decision evidence, not an Analytics endpoint. */
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI only\n"); exit(1); }
$options = getopt('', ['campaign:', 'summary']); $campaignId = (int) ($options['campaign'] ?? 0);
if ($campaignId <= 0) { fwrite(STDERR, "Use --campaign=<id>\n"); exit(2); }
$appRoot = rtrim((string) (getenv('ENDORSE_V2_APP_ROOT') ?: '/var/www/html'), '/');
define('BASEPATH', __DIR__); define('APPPATH', $appRoot . '/application/'); define('FCPATH', $appRoot . '/');
require APPPATH . 'helpers/env_helper.php'; require APPPATH . 'libraries/EndorseV2Identity.php';
$db = new mysqli(env('DB_HOSTNAME'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), (int) env('DB_PORT', 3306));
if ($db->connect_errno) { fwrite(STDERR, "Database connection failed\n"); exit(1); }
$db->set_charset('utf8mb4');
$result = $db->query("SELECT id,platform,link_upload,views FROM endorse WHERE id_campaign={$campaignId} AND status='Aktif' AND link_upload <> '' ORDER BY id");
if (!$result) { fwrite(STDERR, "Read query failed\n"); exit(1); }
$rows=[]; $canonical=[]; $rowViews=0;
while ($row=$result->fetch_assoc()) { $identity=EndorseV2Identity::identify((string)$row['platform'],(string)$row['link_upload']); $key=$identity['platform'].'|'.($identity['platform_content_id'] ?: $identity['canonical_url']); $rows[]=['endorse_id'=>(int)$row['id'],'key'=>$key,'views'=>(int)$row['views']]; $rowViews+=(int)$row['views']; if(!isset($canonical[$key])) $canonical[$key]=$rows[count($rows)-1]; }
$duplicates=[]; foreach($rows as $row){ $duplicates[$row['key']][]=$row['endorse_id']; } $duplicates=array_filter($duplicates,static fn($ids)=>count($ids)>1);
echo json_encode(['campaign_id'=>$campaignId,'endorse_row_count'=>count($rows),'canonical_content_count'=>count($canonical),'current_row_views_sum'=>$rowViews,'current_canonical_first_row_views_sum'=>array_sum(array_column($canonical,'views')),'duplicate_identity_group_count'=>count($duplicates),'duplicate_identity_groups'=>isset($options['summary'])?null:$duplicates,'analytics_observation_metrics'=>null,'note'=>'Current counts/metrics are decision evidence only; V2 observed analytics is not calculated from legacy current values.'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),PHP_EOL;
