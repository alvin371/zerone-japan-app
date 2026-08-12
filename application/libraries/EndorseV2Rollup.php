<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Coalesced campaign rollup. It intentionally preserves legacy row-count aggregation semantics. */
class EndorseV2Rollup
{
    private $CI;
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); $this->CI->load->library(['EndorseV2Runtime', 'EndorseV2Writer']); }
    public function runOne(string $workerId, int $actorId = 0): array
    {
        if (!$this->CI->endorsev2runtime->enabled('ROLLUP') || $this->CI->endorsev2runtime->writerMode() !== 'v2') return ['status'=>false,'code'=>'v2_disabled'];
        $this->CI->db->trans_begin(); $now=EndorseV2Writer::utcNow();
        $work=$this->CI->db->query("SELECT * FROM endorse_v2_rollup_work WHERE status='pending' OR (status='processing' AND lease_expires_at < ?) ORDER BY updated_at LIMIT 1 FOR UPDATE",[$now])->row_array();
        if(!$work){$this->CI->db->trans_commit();return ['status'=>true,'idle'=>true];}
        $lease=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->modify('+2 minutes')->format('Y-m-d H:i:s.u'); $this->CI->db->update('endorse_v2_rollup_work',['status'=>'processing','worker_id'=>$workerId,'lease_expires_at'=>$lease,'updated_at'=>$now],['campaign_id'=>$work['campaign_id']]); $this->CI->db->trans_commit();
        // Current legacy campaign aggregate: count every endorse row; metrics only active rows with a content URL.
        $totals=$this->CI->db->query("SELECT COUNT(*) count_endorse, SUM(CASE WHEN status='Aktif' AND link_upload<>'' THEN 1 ELSE 0 END) count_endorse_processed, SUM(CASE WHEN status='Aktif' AND link_upload<>'' THEN COALESCE(total_cost,0) ELSE 0 END) total_cost, SUM(CASE WHEN status='Aktif' AND link_upload<>'' THEN COALESCE(views,0) ELSE 0 END) views, SUM(CASE WHEN status='Aktif' AND link_upload<>'' THEN COALESCE(likes,0) ELSE 0 END) likes, SUM(CASE WHEN status='Aktif' AND link_upload<>'' THEN COALESCE(comment,0) ELSE 0 END) comment, SUM(CASE WHEN status='Aktif' AND link_upload<>'' THEN COALESCE(share_save,0) ELSE 0 END) share_save, AVG(CASE WHEN status='Aktif' AND link_upload<>'' THEN cpm END) cpm FROM endorse WHERE id_campaign=?",[$work['campaign_id']])->row_array();
        $this->CI->db->trans_begin(); $locked=$this->CI->db->query('SELECT * FROM endorse_v2_rollup_work WHERE campaign_id=? FOR UPDATE',[$work['campaign_id']])->row_array();
        if(!$locked || $locked['worker_id']!==$workerId){$this->CI->db->trans_rollback();return ['status'=>false,'code'=>'stale_rollup'];}
        $update=['count_endorse'=>(int)$totals['count_endorse'],'count_endorse_processed'=>(int)$totals['count_endorse_processed'],'total_cost'=>(float)$totals['total_cost'],'views'=>(int)$totals['views'],'likes'=>(int)$totals['likes'],'comment'=>(int)$totals['comment'],'share_save'=>(int)$totals['share_save'],'cpm'=>(float)$totals['cpm'],'updated_at'=>gmdate('Y-m-d H:i:s'),'updated_by'=>(string)$actorId];
        $this->CI->db->update('endorse_campaign',$update,['id'=>$work['campaign_id']]); $this->CI->db->update('endorse_v2_rollup_work',['status'=>'pending','processed_version'=>$locked['dirty_version'],'worker_id'=>null,'lease_expires_at'=>null,'updated_at'=>EndorseV2Writer::utcNow()],['campaign_id'=>$work['campaign_id']]); $this->CI->db->trans_commit(); return ['status'=>true,'campaign_id'=>(int)$work['campaign_id'],'totals'=>$totals];
    }
}
