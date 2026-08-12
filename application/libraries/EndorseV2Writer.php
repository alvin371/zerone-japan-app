<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Applies an already fetched provider result. Callers must hold the V2 job/state locks.
 * It performs no HTTP/network operation and writes only V2 sidecars plus current endorse metrics.
 */
class EndorseV2Writer
{
    private $CI;
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); }

    public static function utcNow(): string { return gmdate('Y-m-d H:i:s') . '.000000'; }

    public function applyLocked(array $endorse, array $state, array $response, int $attemptId, int $actorId): array
    {
        $this->CI->load->library('endorse_sync');
        $classification = $this->CI->endorse_sync->classify_response($response, (string) $endorse['platform'], (string) $endorse['link_upload']);
        if ($classification['class'] !== Endorse_sync::ERR_OK) return ['ok' => false, 'classification' => $classification];

        $data = (array) ($response['data'] ?? []);
        $previous = [
            'views' => $state['trusted_views'] ?? $endorse['views'] ?? null,
            'likes' => $state['trusted_likes'] ?? $endorse['likes'] ?? null,
            'comments' => $state['trusted_comments'] ?? $endorse['comment'] ?? null,
            'share_save' => $state['trusted_share_save'] ?? $endorse['share_save'] ?? null,
        ];
        $incoming = [
            'views' => $this->presentInt($data, 'view'), 'likes' => $this->presentInt($data, 'like'),
            'comments' => $this->presentInt($data, 'comment'), 'shares' => $this->presentInt($data, 'share'),
            'saves' => $this->presentInt($data, 'collect'),
        ];
        $overrides = $this->activeOverrides((int) $endorse['id'], (int) $state['content_generation']);
        $this->CI->load->library('EndorseV2MetricTrustPolicy');
        $metrics = EndorseV2MetricTrustPolicy::trusted($previous, $incoming, $overrides);
        $observedAt = $this->observedAt($data);
        $observationDate = (new DateTimeImmutable($observedAt, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d');
        $anomaly = [];
        if ($incoming['views'] !== null && $previous['views'] !== null && $incoming['views'] < $previous['views']) $anomaly['views_decreased'] = true;

        $stateUpdate = [
            'trusted_views' => $metrics['views'], 'trusted_likes' => $metrics['likes'], 'trusted_comments' => $metrics['comments'],
            'trusted_shares' => $metrics['shares'], 'trusted_saves' => $metrics['saves'], 'trusted_share_save' => $metrics['share_save'],
            'last_successful_observed_at' => $observedAt, 'last_sync_error_class' => null, 'updated_at' => self::utcNow(),
        ];
        $this->CI->db->update('endorse_v2_content_state', $stateUpdate, ['endorse_id' => $endorse['id']]);

        $cpm = ((float) ($endorse['total_cost'] ?? 0) > 0 && $metrics['views'] > 0)
            ? ((float) $endorse['total_cost'] / $metrics['views'] * 1000) : 0;
        $legacyUpdate = ['views' => $metrics['views'], 'likes' => $metrics['likes'], 'comment' => $metrics['comments'],
            'share_save' => $metrics['share_save'], 'cpm' => $cpm, 'sync_at' => gmdate('Y-m-d H:i:s'),
            'updated_at' => gmdate('Y-m-d H:i:s'), 'updated_by' => (string) $actorId];
        if (!empty($data['created_at'])) $legacyUpdate['posting_at'] = (string) $data['created_at'];
        $this->CI->db->update('endorse', $legacyUpdate, ['id' => $endorse['id']]);

        $existing = $this->CI->db->get_where('endorse_v2_metric_observations', ['endorse_id' => $endorse['id'], 'content_generation' => $state['content_generation'], 'observation_date' => $observationDate])->row_array();
        $predecessor = $existing ?: $this->previousObservation((int) $endorse['id'], (int) $state['content_generation'], $observationDate);
        $this->CI->load->library('EndorseV2ObservationPolicy');
        $storage = EndorseV2ObservationPolicy::build($metrics, $existing ?: null, $existing ? null : ($predecessor ?: null));
        $before = $storage['before'];
        $observation = [
            'endorse_id' => $endorse['id'], 'campaign_id_at_observation' => $endorse['id_campaign'], 'content_generation' => $state['content_generation'],
            'observation_date' => $observationDate, 'observed_at' => $observedAt, 'observation_time_source' => 'worker_received', 'observation_kind' => 'provider',
            'is_baseline' => $storage['is_baseline'] ? 1 : 0, 'baseline_reason' => $storage['baseline_reason'],
            'views_before' => $before['views'], 'views_after' => $metrics['views'],
            'likes_before' => $before['likes'], 'likes_after' => $metrics['likes'],
            'comments_before' => $before['comments'], 'comments_after' => $metrics['comments'],
            'shares_before' => $before['shares'], 'shares_after' => $metrics['shares'],
            'saves_before' => $before['saves'], 'saves_after' => $metrics['saves'],
            'anomaly_json' => empty($anomaly) ? null : json_encode($anomaly), 'source_attempt_id' => $attemptId, 'updated_at' => self::utcNow(),
        ];
        if ($existing) $this->CI->db->update('endorse_v2_metric_observations', $observation, ['id' => $existing['id']]);
        else { $observation['created_at'] = self::utcNow(); $this->CI->db->insert('endorse_v2_metric_observations', $observation); }
        return ['ok' => true, 'metrics' => $metrics, 'observed_at' => $observedAt, 'observation_date' => $observationDate, 'anomaly' => $anomaly];
    }

    private function presentInt(array $data, string $key): ?int { return array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '' ? max(0, (int) $data[$key]) : null; }
    private function activeOverrides(int $endorseId, int $generation): array { $rows=$this->CI->db->where(['endorse_id'=>$endorseId,'content_generation'=>$generation])->where('cleared_at IS NULL', null, false)->get('endorse_v2_manual_overrides')->result_array(); $out=[]; foreach($rows as $r) $out[$r['metric']]=(int)$r['value']; return $out; }
    private function observedAt(array $data): string { if (!empty($data['observed_at'])) { try { return (new DateTimeImmutable($data['observed_at']))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.u'); } catch (Exception $e) {} } return self::utcNow(); }
    private function previousObservation(int $endorseId,int $generation,string $date): array { return (array)$this->CI->db->where('endorse_id',$endorseId)->where('content_generation',$generation)->where('observation_date <',$date)->order_by('observation_date','DESC')->limit(1)->get('endorse_v2_metric_observations')->row_array(); }
}
