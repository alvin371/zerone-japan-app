<?php
defined('BASEPATH') or exit('No direct script access allowed');

class EndorseRefreshQueueService
{
    const DEFAULT_PRIORITY = 10;
    const DEFAULT_MAX_ATTEMPTS = 3;
    const INSERT_CHUNK_SIZE = 250;

    protected $CI;
    protected $db;

    public static function allowsRustClaims(string $driver): bool
    {
        return strtolower(trim($driver)) === 'rust';
    }

    public static function retryDelaySeconds(int $attempts, int $baseSeconds = 60): int
    {
        $baseSeconds = max(1, min(3600, $baseSeconds));
        $exponent = max(0, min(10, $attempts - 1));

        return $baseSeconds * (2 ** $exponent);
    }

    public static function normalizeTiktokUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (preg_match('#^(?:[a-z0-9-]+\.)?tiktok\.com/#i', $url)) {
            return 'https://' . $url;
        }

        return $url;
    }

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('mymodel');
        $this->db = $this->CI->db;
    }

    public function enqueueCampaign(int $id_campaign, int $user_id, array $ids = []): array
    {
        if ($id_campaign <= 0) {
            return [
                'status' => false,
                'msg' => 'Campaign tidak valid.',
                'enqueued' => 0,
                'skipped_duplicates' => 0,
                'id_campaign' => $id_campaign,
            ];
        }

        $extra = '';
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!empty($ids)) {
            $extra = ' AND id IN (' . implode(',', $ids) . ')';
        }

        $rows = $this->CI->mymodel->selectWithQuery("
            SELECT id, id_campaign, platform, link_upload
            FROM endorse
            WHERE id_campaign = '" . intval($id_campaign) . "'
              AND status = 'Aktif' AND status_campaign = 'Aktif'
              AND link_upload != ''
              $extra
        ");

        if (empty($rows)) {
            return [
                'status' => false,
                'msg' => 'Tidak ada konten aktif yang bisa direfresh.',
                'enqueued' => 0,
                'skipped_duplicates' => 0,
                'id_campaign' => $id_campaign,
            ];
        }

        $stats = $this->enqueueRows($rows, $user_id);

        return [
            'status' => true,
            'msg' => $this->buildEnqueueMessage($stats['enqueued'], $stats['skipped_duplicates'], $stats['excluded_known_url']),
            'enqueued' => $stats['enqueued'],
            'skipped_duplicates' => $stats['skipped_duplicates'],
            'excluded_known_url' => $stats['excluded_known_url'],
            'count' => count($rows),
            'id_campaign' => $id_campaign,
        ];
    }

    public function enqueueAllActive(int $user_id): array
    {
        $rows = $this->CI->mymodel->selectWithQuery("
            SELECT e.id, e.id_campaign, e.platform, e.link_upload
            FROM endorse e
            INNER JOIN endorse_campaign c ON c.id = e.id_campaign
            WHERE c.status = 'Aktif'
              AND e.status = 'Aktif'
              AND e.status_campaign = 'Aktif'
              AND e.link_upload != ''
            ORDER BY e.id_campaign ASC, e.id ASC
        ");

        if (empty($rows)) {
            return [
                'status' => true,
                'msg' => 'Tidak ada konten aktif yang bisa direfresh.',
                'campaign_count' => 0,
                'candidate_count' => 0,
                'enqueued' => 0,
                'skipped_duplicates' => 0,
                'excluded_known_url' => 0,
            ];
        }

        $stats = $this->enqueueRows($rows, $user_id);

        return [
            'status' => true,
            'msg' => $this->buildEnqueueMessage($stats['enqueued'], $stats['skipped_duplicates'], $stats['excluded_known_url']),
            'campaign_count' => $stats['campaign_count'],
            'candidate_count' => count($rows),
            'enqueued' => $stats['enqueued'],
            'skipped_duplicates' => $stats['skipped_duplicates'],
            'excluded_known_url' => $stats['excluded_known_url'],
        ];
    }

    public function cloneFailedRows(array $queueIds, int $user_id): array
    {
        $queueIds = array_values(array_unique(array_filter(array_map('intval', $queueIds))));
        if (empty($queueIds)) {
            return ['status' => false, 'msg' => 'Tidak ada baris dipilih.', 'updated' => 0];
        }

        $idList = implode(',', $queueIds);
        $rows = $this->CI->mymodel->selectWithQuery("
            SELECT id, id_endorse, id_campaign, platform, link_upload, priority, max_attempts
            FROM endorse_refresh_queue
            WHERE id IN ($idList) AND status = 'failed'
        ");

        if (empty($rows)) {
            return ['status' => false, 'msg' => 'Tidak ada baris gagal yang bisa dijadwalkan ulang.', 'updated' => 0];
        }

        $active = $this->loadActiveEndorseIds(array_map(function ($row) {
            return intval($row['id_endorse']);
        }, $rows));

        $now = date('Y-m-d H:i:s');
        $batch = [];
        $skipped = 0;

        foreach ($rows as $row) {
            $id_endorse = intval($row['id_endorse']);
            if (isset($active[$id_endorse])) {
                $skipped++;
                continue;
            }

            $batch[] = [
                'id_endorse' => $id_endorse,
                'id_campaign' => intval($row['id_campaign']),
                'platform' => strval($row['platform']),
                'link_upload' => self::normalizeTiktokUrl(strval($row['link_upload'])),
                'status' => 'pending',
                'priority' => intval($row['priority']) > 0 ? intval($row['priority']) : self::DEFAULT_PRIORITY,
                'attempts' => 0,
                'max_attempts' => intval($row['max_attempts']) > 0 ? intval($row['max_attempts']) : self::DEFAULT_MAX_ATTEMPTS,
                'enqueued_by' => $user_id,
                'retry_source_id' => intval($row['id']),
                'created_at' => $now,
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch('endorse_refresh_queue', $batch);
        }

        return [
            'status' => true,
            'msg' => count($batch) . ' baris dijadwalkan ulang.' . ($skipped > 0 ? " $skipped dilewati karena masih aktif di antrian." : ''),
            'updated' => count($batch),
            'skipped_duplicates' => $skipped,
        ];
    }

    public function clearAll(): array
    {
        $attemptRows = $this->CI->mymodel->selectWithQuery("SELECT COUNT(*) AS c FROM endorse_refresh_queue_attempts");
        $queueRows = $this->CI->mymodel->selectWithQuery("SELECT COUNT(*) AS c FROM endorse_refresh_queue");
        $attemptCount = !empty($attemptRows) ? intval($attemptRows[0]['c']) : 0;
        $queueCount = !empty($queueRows) ? intval($queueRows[0]['c']) : 0;

        $this->db->trans_start();
        $this->db->query("DELETE FROM endorse_refresh_queue_attempts");
        $this->db->query("DELETE FROM endorse_refresh_queue");
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return [
                'status' => false,
                'msg' => 'Gagal menghapus data antrian.',
                'deleted_queue' => 0,
                'deleted_attempts' => 0,
            ];
        }

        return [
            'status' => true,
            'msg' => $queueCount . ' data antrian dan ' . $attemptCount . ' riwayat percobaan dihapus.',
            'deleted_queue' => $queueCount,
            'deleted_attempts' => $attemptCount,
        ];
    }

    public function resetStuck(int $staleMinutes = 5): array
    {
        $staleMinutes = max(1, intval($staleMinutes));
        $now = date('Y-m-d H:i:s');

        $this->db->query("
            UPDATE endorse_refresh_queue_attempts a
            INNER JOIN endorse_refresh_queue q ON q.id = a.queue_id
            SET a.status = 'retrying',
                a.error_class = 'transient',
                a.error_message = 'Worker stalled; item returned to pending queue',
                a.finished_at = '$now'
            WHERE a.status = 'processing'
              AND q.status = 'processing'
              AND q.started_at < (NOW() - INTERVAL $staleMinutes MINUTE)
        ");

        $this->db->query("
            UPDATE endorse_refresh_queue
            SET status = 'pending', worker_id = NULL, started_at = NULL, claimed_at = NULL
            WHERE status = 'processing'
              AND started_at < (NOW() - INTERVAL $staleMinutes MINUTE)
        ");
        $reset = $this->db->affected_rows();

        return [
            'status' => true,
            'reset_count' => $reset,
            'msg' => $reset > 0
                ? "$reset item macet dikembalikan ke antrian (menunggu)."
                : 'Tidak ada item macet untuk direset.',
        ];
    }

    public function computeHealth(int $id_campaign = 0, int $staleMinutes = 10): array
    {
        $where = '';
        if ($id_campaign > 0) {
            $where = " AND id_campaign = '" . intval($id_campaign) . "'";
        }

        $summaryRows = $this->CI->mymodel->selectWithQuery("
            SELECT status, COUNT(*) AS c
            FROM endorse_refresh_queue
            WHERE status IN ('pending','processing')
            $where
            GROUP BY status
        ");

        $pending = 0;
        $processing = 0;
        foreach ($summaryRows as $row) {
            if ($row['status'] === 'pending') {
                $pending = intval($row['c']);
            }
            if ($row['status'] === 'processing') {
                $processing = intval($row['c']);
            }
        }

        $metaRows = $this->CI->mymodel->selectWithQuery("
            SELECT
                MIN(CASE WHEN status = 'pending' THEN created_at END) AS oldest_pending_at,
                MAX(CASE WHEN status IN ('completed','failed') THEN completed_at END) AS last_completed_at,
                MAX(CASE WHEN status = 'processing' THEN started_at END) AS last_started_at
            FROM endorse_refresh_queue
            WHERE 1 = 1
            $where
        ");
        $meta = !empty($metaRows) ? $metaRows[0] : [];

        $oldestPendingAt = $meta['oldest_pending_at'] ?? null;
        $lastCompletedAt = $meta['last_completed_at'] ?? null;
        $lastStartedAt = $meta['last_started_at'] ?? null;
        $lastActivityAt = $lastStartedAt ?: $lastCompletedAt;
        $isStalled = false;

        if ($pending > 0 && $processing === 0) {
            if (empty($lastActivityAt) || strtotime($lastActivityAt) < strtotime('-' . intval($staleMinutes) . ' minutes')) {
                $isStalled = true;
            }
        }

        $stall = $isStalled ? $this->diagnoseStall() : null;

        return [
            'active_total' => $pending + $processing,
            'pending_total' => $pending,
            'processing_total' => $processing,
            'oldest_pending_at' => $oldestPendingAt,
            'last_completed_at' => $lastCompletedAt,
            'last_started_at' => $lastStartedAt,
            'is_stalled' => $isStalled,
            'stall_reason' => $stall['reason'] ?? null,
            'stall_label' => $stall['label'] ?? null,
        ];
    }

    public function claimBatch(array $opts = []): array
    {
        $this->CI->load->library('template');
        $this->CI->load->library('endorse_sync');

        $limit = intval($opts['limit'] ?? env('ENDORSE_REFRESH_BATCH_SIZE', 40));
        if ($limit <= 0) {
            $limit = 40;
        } elseif ($limit > 500) {
            $limit = 500;
        }
        $force = !empty($opts['force']);
        $staleMinutes = intval($opts['stale_minutes'] ?? 5);
        if ($staleMinutes < 1) {
            $staleMinutes = 5;
        }

        $this->resetStuck($staleMinutes);

        $worker_id = uniqid('w_', true);

        $dailyCap = intval($opts['daily_cap'] ?? env('ENDORSE_REFRESH_DAILY_CAP', 0));
        if (!$force && $dailyCap > 0) {
            $startOfDay = date('Y-m-d') . ' 00:00:00';
            $usedRow = $this->CI->mymodel->selectWithQuery("
                SELECT COUNT(*) AS c FROM endorse_refresh_queue_attempts
                WHERE started_at >= '$startOfDay'
            ");
            $usedToday = intval($usedRow[0]['c'] ?? 0);
            $remaining = $dailyCap - $usedToday;
            if ($remaining <= 0) {
                return [
                    'status' => true,
                    'claimed' => 0,
                    'items' => [],
                    'worker_id' => $worker_id,
                    'skipped' => [
                        'reason' => 'daily_cap',
                        'used' => $usedToday,
                        'cap' => $dailyCap,
                        'msg' => "Daily cap reached ($usedToday/$dailyCap) — skipping run",
                    ],
                ];
            }
            if ($remaining < $limit) {
                $limit = $remaining;
            }
        }

        $ratePerMin = intval($opts['rate_per_min'] ?? env('ENDORSE_REFRESH_RATE_PER_MIN', 0));
        if (!$force && $ratePerMin > 0) {
            $usedRow = $this->CI->mymodel->selectWithQuery("
                SELECT COUNT(*) AS c FROM endorse_refresh_queue_attempts
                WHERE started_at >= (NOW() - INTERVAL 60 SECOND)
            ");
            $usedMinute = intval($usedRow[0]['c'] ?? 0);
            $remainingMinute = $ratePerMin - $usedMinute;
            if ($remainingMinute <= 0) {
                return [
                    'status' => true,
                    'claimed' => 0,
                    'items' => [],
                    'worker_id' => $worker_id,
                    'skipped' => [
                        'reason' => 'rate_per_min',
                        'used' => $usedMinute,
                        'cap' => $ratePerMin,
                        'msg' => "Per-minute rate cap reached ($usedMinute/$ratePerMin) — skipping run",
                    ],
                ];
            }
            if ($remainingMinute < $limit) {
                $limit = $remainingMinute;
            }
        }

        if ($limit <= 0) {
            return ['status' => true, 'claimed' => 0, 'items' => [], 'worker_id' => $worker_id];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->query("
            UPDATE endorse_refresh_queue
            SET status = 'processing', worker_id = '$worker_id', claimed_at = '$now', started_at = '$now'
            WHERE status = 'pending' AND worker_id IS NULL
            ORDER BY priority DESC, attempts ASC, created_at ASC
            LIMIT $limit
        ");
        $claimed = $this->db->affected_rows();
        if ($claimed <= 0) {
            return ['status' => true, 'claimed' => 0, 'items' => [], 'worker_id' => $worker_id];
        }

        $rows = $this->CI->mymodel->selectWithQuery("
            SELECT * FROM endorse_refresh_queue
            WHERE worker_id = '$worker_id' AND status = 'processing'
        ");

        $priorAttemptMap = [];
        $queueIds = array_map(function ($r) {
            return intval($r['id']);
        }, $rows);
        if (!empty($queueIds)) {
            $queueIdList = implode(',', $queueIds);
            $priorAttempts = $this->CI->mymodel->selectWithQuery("
                SELECT queue_id, error_class
                FROM endorse_refresh_queue_attempts
                WHERE queue_id IN ($queueIdList)
                  AND status IN ('retrying', 'failed')
                ORDER BY id DESC
            ");
            foreach ($priorAttempts as $pa) {
                $qid = intval($pa['queue_id'] ?? 0);
                if ($qid > 0 && !array_key_exists($qid, $priorAttemptMap)) {
                    $priorAttemptMap[$qid] = strval($pa['error_class'] ?? '');
                }
            }
        }

        $attemptRows = [];
        foreach ($rows as $r) {
            $attemptRows[] = [
                'queue_id' => intval($r['id']),
                'attempt_no' => intval($r['attempts']) + 1,
                'worker_id' => $worker_id,
                'status' => 'processing',
                'started_at' => $now,
                'created_at' => $now,
            ];
        }
        if (!empty($attemptRows)) {
            $this->db->insert_batch('endorse_refresh_queue_attempts', $attemptRows);
        }

        $httpTimeout = intval(env('ENDORSE_REFRESH_HTTP_TIMEOUT', 30));
        if ($httpTimeout < 1) {
            $httpTimeout = 30;
        }

        $items = [];
        foreach ($rows as $r) {
            $qid = intval($r['id']);
            $priorClass = strval($priorAttemptMap[$qid] ?? '');
            $isRescue = ($priorClass === Endorse_sync::ERR_INFRA_STALL);
            $url = self::normalizeTiktokUrl(strval($r['link_upload']));
            $hd = ($isRescue && $this->CI->template->detect_tiktok_media_type_from_url($url) === 'photo') ? 1 : 0;
            $items[] = [
                'queue_id' => $qid,
                'id_endorse' => intval($r['id_endorse']),
                'platform' => $r['platform'],
                'url' => $url,
                'enqueued_by' => intval($r['enqueued_by'] ?: 0),
                'attempts' => intval($r['attempts']),
                'attempt_no' => intval($r['attempts']) + 1,
                'max_attempts' => intval($r['max_attempts']),
                'worker_id' => $worker_id,
                'rescue_lane' => $isRescue,
                'timeout_sec' => $isRescue ? max(45, $httpTimeout + 15) : $httpTimeout,
                'hd' => $hd,
            ];
        }

        return ['status' => true, 'claimed' => $claimed, 'items' => $items, 'worker_id' => $worker_id];
    }

    public function applyResults(array $items, array $responses): array
    {
        if (empty($items)) {
            return ['completed' => 0, 'failed' => 0, 'retrying' => 0, 'deferred' => 0, 'processed' => 0];
        }

        $this->CI->load->library('endorse_sync');

        $today = date('Y-m-d');
        $endorseIds = array_map(function ($it) {
            return intval($it['id_endorse']);
        }, $items);
        $endorseIdList = implode(',', array_map('intval', $endorseIds));
        $endorseRows = $this->CI->mymodel->selectWithQuery("
            SELECT * FROM endorse WHERE id IN ($endorseIdList)
        ");
        $endorseMap = [];
        foreach ($endorseRows as $er) {
            $endorseMap[intval($er['id'])] = $er;
        }
        $prevStatsMap = $this->CI->endorse_sync->load_prev_stats_batch($endorseIds, $today);

        $completed = 0;
        $failed = 0;
        $retrying = 0;
        $deferred = 0;
        $touched = [];

        foreach ($items as $i => $item) {
            $queue_id = intval($item['queue_id']);
            $id_endorse = intval($item['id_endorse']);
            $endorse = $endorseMap[$id_endorse] ?? null;
            $attempts = intval($item['attempts']) + 1;
            $maxAttempts = intval($item['max_attempts']);
            $worker_id = strval($item['worker_id'] ?? '');
            $response = $responses[$i] ?? ['status' => false, 'msg' => 'No response', 'data' => []];

            if (!empty($response['deferred'])) {
                $this->db->update('endorse_refresh_queue', [
                    'status' => 'pending',
                    'worker_id' => null,
                    'started_at' => null,
                    'claimed_at' => null,
                ], ['id' => $queue_id]);
                $this->db->delete('endorse_refresh_queue_attempts', [
                    'queue_id' => $queue_id,
                    'attempt_no' => $attempts,
                    'worker_id' => $worker_id,
                ]);
                $deferred++;
                continue;
            }

            if (!$endorse) {
                $this->markQueueFailed($queue_id, $attempts, 'Endorse row no longer exists', Endorse_sync::ERR_PERMANENT, $worker_id);
                $failed++;
                continue;
            }

            $result = $this->CI->endorse_sync->apply(
                $endorse,
                $response,
                intval($item['enqueued_by'] ?: 0),
                $prevStatsMap[$id_endorse] ?? null
            );

            if ($result['status']) {
                $completedAt = date('Y-m-d H:i:s');
                $this->db->update('endorse_refresh_queue', [
                    'status' => 'completed',
                    'attempts' => $attempts,
                    'error_message' => null,
                    'worker_id' => null,
                    'completed_at' => $completedAt,
                ], ['id' => $queue_id]);
                $this->finalizeQueueAttempt($queue_id, $attempts, $worker_id, 'completed', null, null, $completedAt);
                $touched[intval($endorse['id_campaign'])] = true;
                $completed++;
                continue;
            }

            $errorClass = $result['error_class'] ?? Endorse_sync::ERR_TRANSIENT;
            $msg = $result['msg'] ?: 'Gagal';

            if (Endorse_sync::is_terminal_class($errorClass)) {
                $this->markQueueFailed($queue_id, $attempts, $msg, $errorClass, $worker_id);
                $failed++;
                continue;
            }

            if ($attempts >= $maxAttempts) {
                $this->markQueueFailed($queue_id, $attempts, "$msg (after $attempts attempts)", $errorClass, $worker_id);
                $failed++;
            } else {
                $finishedAt = date('Y-m-d H:i:s');
                $this->db->update('endorse_refresh_queue', [
                    'status' => 'pending',
                    'attempts' => $attempts,
                    'error_message' => $msg,
                    'worker_id' => null,
                    'started_at' => null,
                    'claimed_at' => null,
                ], ['id' => $queue_id]);
                $this->finalizeQueueAttempt($queue_id, $attempts, $worker_id, 'retrying', $errorClass, $msg, $finishedAt);
                $retrying++;
            }
        }

        foreach (array_keys($touched) as $cid) {
            $this->CI->endorse_sync->update_campaign_parent($cid, 0);
        }

        return [
            'completed' => $completed,
            'failed' => $failed,
            'retrying' => $retrying,
            'deferred' => $deferred,
            'processed' => count($items),
        ];
    }

    protected function enqueueRows(array $rows, int $user_id): array
    {
        $candidateIds = array_map(function ($row) {
            return intval($row['id']);
        }, $rows);

        $already = $this->loadActiveEndorseIds($candidateIds);
        $knownUrlIssues = $this->loadKnownUrlIssueEndorseIds($candidateIds);
        $campaigns = [];
        $now = date('Y-m-d H:i:s');
        $batch = [];
        $enqueued = 0;
        $skipped = 0;
        $excludedKnownUrl = 0;

        foreach ($rows as $row) {
            $campaigns[intval($row['id_campaign'])] = true;
            $id_endorse = intval($row['id']);

            if (isset($already[$id_endorse])) {
                $skipped++;
                continue;
            }

            if (isset($knownUrlIssues[$id_endorse])) {
                $excludedKnownUrl++;
                continue;
            }

            $batch[] = [
                'id_endorse' => $id_endorse,
                'id_campaign' => intval($row['id_campaign']),
                'platform' => strval($row['platform']),
                'purpose' => 'daily',
                'link_upload' => self::normalizeTiktokUrl(strval($row['link_upload'])),
                'status' => 'pending',
                'priority' => self::DEFAULT_PRIORITY,
                'attempts' => 0,
                'max_attempts' => self::DEFAULT_MAX_ATTEMPTS,
                'enqueued_by' => $user_id,
                'created_at' => $now,
            ];

            if (count($batch) >= self::INSERT_CHUNK_SIZE) {
                $this->db->insert_batch('endorse_refresh_queue', $batch);
                $enqueued += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->db->insert_batch('endorse_refresh_queue', $batch);
            $enqueued += count($batch);
        }

        return [
            'campaign_count' => count($campaigns),
            'enqueued' => $enqueued,
            'skipped_duplicates' => $skipped,
            'excluded_known_url' => $excludedKnownUrl,
        ];
    }

    protected function buildEnqueueMessage(int $enqueued, int $skipped, int $excludedKnownUrl): string
    {
        $msg = $enqueued . ' konten ditambahkan ke antrian.';
        if ($skipped > 0) {
            $msg .= " $skipped sudah ada di antrian.";
        }
        if ($excludedKnownUrl > 0) {
            $msg .= " $excludedKnownUrl dilewati karena URL TikTok bermasalah.";
        }

        return $msg;
    }

    protected function loadActiveEndorseIds(array $endorseIds): array
    {
        $endorseIds = array_values(array_unique(array_filter(array_map('intval', $endorseIds))));
        if (empty($endorseIds)) {
            return [];
        }

        $idList = implode(',', $endorseIds);
        $existing = $this->CI->mymodel->selectWithQuery("
            SELECT id_endorse
            FROM endorse_refresh_queue
            WHERE id_endorse IN ($idList)
              AND purpose = 'daily'
              AND status IN ('pending','processing')
        ");

        $active = [];
        foreach ($existing as $row) {
            $active[intval($row['id_endorse'])] = true;
        }

        return $active;
    }

    protected function loadKnownUrlIssueEndorseIds(array $endorseIds): array
    {
        $endorseIds = array_values(array_unique(array_filter(array_map('intval', $endorseIds))));
        if (empty($endorseIds)) {
            return [];
        }

        $idList = implode(',', $endorseIds);
        $rows = $this->CI->mymodel->selectWithQuery("
            SELECT latest.id_endorse
            FROM endorse_refresh_queue latest
            INNER JOIN (
                SELECT id_endorse, MAX(id) AS max_id
                FROM endorse_refresh_queue
                WHERE id_endorse IN ($idList)
                GROUP BY id_endorse
            ) picked ON picked.max_id = latest.id
            WHERE latest.status = 'failed'
              AND (
                latest.error_message LIKE '%Stats data tidak ditemukan%'
                OR latest.error_message LIKE '%url tidak ditemukan%'
              )
        ");

        $blocked = [];
        foreach ($rows as $row) {
            $blocked[intval($row['id_endorse'])] = true;
        }

        return $blocked;
    }

    protected function diagnoseStall(): array
    {
        $dailyCap = intval(env('ENDORSE_REFRESH_DAILY_CAP', 0));
        $ratePerMin = intval(env('ENDORSE_REFRESH_RATE_PER_MIN', 0));

        if ($dailyCap > 0) {
            $startOfDay = date('Y-m-d') . ' 00:00:00';
            $row = $this->CI->mymodel->selectWithQuery("
                SELECT COUNT(*) AS c FROM endorse_refresh_queue_attempts
                WHERE started_at >= '$startOfDay'
            ");
            $usedToday = intval($row[0]['c'] ?? 0);
            if ($usedToday >= $dailyCap) {
                return ['reason' => 'daily_cap', 'label' => "batas harian tercapai ($usedToday/$dailyCap)"];
            }
        }

        if ($ratePerMin > 0) {
            $row = $this->CI->mymodel->selectWithQuery("
                SELECT COUNT(*) AS c FROM endorse_refresh_queue_attempts
                WHERE started_at >= (NOW() - INTERVAL 60 SECOND)
            ");
            $usedMinute = intval($row[0]['c'] ?? 0);
            if ($usedMinute >= $ratePerMin) {
                return ['reason' => 'rate_cap', 'label' => "batas per-menit tercapai ($usedMinute/$ratePerMin)"];
            }
        }

        $row = $this->CI->mymodel->selectWithQuery("
            SELECT error_class, COUNT(*) AS c
            FROM endorse_refresh_queue_attempts
            WHERE status IN ('failed','retrying')
              AND started_at >= (NOW() - INTERVAL 15 MINUTE)
            GROUP BY error_class
            ORDER BY c DESC
            LIMIT 1
        ");
        if (!empty($row)) {
            $cls = $row[0]['error_class'] ?: 'unknown';
            return ['reason' => 'upstream_error', 'label' => "error upstream: $cls"];
        }

        return ['reason' => 'idle_worker', 'label' => 'worker tidak berjalan (cek cron)'];
    }

    protected function markQueueFailed(int $queue_id, int $attempts, string $msg, ?string $errorClass = null, ?string $worker_id = null): void
    {
        $completedAt = date('Y-m-d H:i:s');
        $this->db->update('endorse_refresh_queue', [
            'status' => 'failed',
            'attempts' => $attempts,
            'error_message' => $msg,
            'worker_id' => null,
            'completed_at' => $completedAt,
        ], ['id' => $queue_id]);
        $this->finalizeQueueAttempt($queue_id, $attempts, $worker_id, 'failed', $errorClass, $msg, $completedAt);
    }

    protected function finalizeQueueAttempt(int $queue_id, int $attemptNo, ?string $worker_id, string $status, ?string $errorClass, ?string $msg, string $finishedAt): void
    {
        $where = [
            'queue_id' => $queue_id,
            'attempt_no' => $attemptNo,
        ];
        if (!empty($worker_id)) {
            $where['worker_id'] = $worker_id;
        }
        $this->db->update('endorse_refresh_queue_attempts', [
            'status' => $status,
            'error_class' => $errorClass,
            'error_message' => $msg,
            'finished_at' => $finishedAt,
        ], $where);
    }
}
