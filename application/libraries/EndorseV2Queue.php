<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** V2 sidecar queue: short DB transactions, provider HTTP outside a transaction, exact-attempt ownership. */
class EndorseV2Queue
{
    private $CI;
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); $this->CI->load->library(['EndorseV2Runtime', 'EndorseV2Writer', 'EndorseV2Identity']); }

    public function enqueue(int $endorseId, int $requestedBy = 0, string $purpose = 'refresh'): array
    {
        if (!$this->CI->endorsev2runtime->canEnqueue()) return ['status'=>false,'code'=>'v2_disabled','msg'=>'Endorse V2 is not enabled.'];
        $this->CI->db->trans_begin();
        $endorse=$this->CI->db->query('SELECT * FROM endorse WHERE id = ? FOR UPDATE',[$endorseId])->row_array();
        if (!$endorse || $endorse['status'] !== 'Aktif' || trim((string)$endorse['link_upload']) === '') { $this->CI->db->trans_rollback(); return ['status'=>false,'code'=>'ineligible','msg'=>'Endorse is not refresh-eligible.']; }
        $state=$this->stateForLocked($endorse);
        $key=hash('sha256',$endorseId.'|'.$state['content_generation'].'|'.$purpose,true);
        $existing=$this->CI->db->get_where('endorse_v2_refresh_dedupe',['dedupe_key'=>$key])->row_array();
        if ($existing) { $this->CI->db->trans_commit(); return ['status'=>true,'duplicate'=>true,'job_id'=>(int)$existing['job_id']]; }
        $now=EndorseV2Writer::utcNow();
        $this->CI->db->insert('endorse_v2_refresh_jobs',['endorse_id'=>$endorseId,'campaign_id'=>$endorse['id_campaign'],'content_generation'=>$state['content_generation'],'state_version'=>$state['state_version'],'purpose'=>$purpose,'status'=>'pending','requested_by'=>$requestedBy?:null,'available_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
        $jobId=(int)$this->CI->db->insert_id();
        $this->CI->db->insert('endorse_v2_refresh_dedupe',['dedupe_key'=>$key,'job_id'=>$jobId,'created_at'=>$now]);
        $this->CI->db->trans_commit(); return ['status'=>true,'duplicate'=>false,'job_id'=>$jobId];
    }

    public function claim(string $workerId): ?array
    {
        if (!$this->CI->endorsev2runtime->canEnqueue()) return null;
        $this->CI->db->trans_begin(); $now=EndorseV2Writer::utcNow();
        $job=$this->CI->db->query("SELECT * FROM endorse_v2_refresh_jobs WHERE status IN ('pending','retry_scheduled') AND available_at <= ? ORDER BY available_at,id LIMIT 1 FOR UPDATE",[$now])->row_array();
        if (!$job) { $this->CI->db->trans_commit(); return null; }
        $last=$this->CI->db->select_max('attempt_sequence')->where('job_id',$job['id'])->get('endorse_v2_refresh_attempts')->row_array(); $seq=(int)($last['attempt_sequence']??0)+1;
        $this->CI->db->insert('endorse_v2_refresh_attempts',['job_id'=>$job['id'],'attempt_sequence'=>$seq,'worker_id'=>$workerId,'started_at'=>$now]); $attemptId=(int)$this->CI->db->insert_id();
        $lease=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->modify('+5 minutes')->format('Y-m-d H:i:s.u');
        $this->CI->db->update('endorse_v2_refresh_jobs',['status'=>'processing','claimed_at'=>$now,'lease_expires_at'=>$lease,'active_attempt_id'=>$attemptId,'updated_at'=>$now],['id'=>$job['id']]);
        $job['active_attempt_id']=$attemptId; $job['attempt_sequence']=$seq; $job['worker_id']=$workerId; $this->CI->db->trans_commit(); return $job;
    }

    public function runOne(string $workerId, int $actorId = 0): array
    {
        $job=$this->claim($workerId); if (!$job) return ['status'=>true,'idle'=>true];
        $endorse=$this->CI->db->get_where('endorse',['id'=>$job['endorse_id']])->row_array();
        if (!$endorse) return $this->finishError($job,$workerId,'permanent','Endorse no longer exists');
        if (strtolower((string)$endorse['platform']) === 'threads') return $this->finishError($job,$workerId,'deferred','Threads V2 is independently gated');
        // HTTP is deliberately outside the claim/apply transactions.
        try { $this->CI->load->library('template'); $response=$this->CI->template->get_social_media($endorse['platform'],$endorse['link_upload']); }
        catch (Throwable $e) { return $this->finishError($job,$workerId,'transient','Provider request failed'); }
        return $this->applyResult($job,$workerId,(array)$response,$actorId);
    }

    public function applyResult(array $job,string $workerId,array $response,int $actorId=0): array
    {
        $this->CI->db->trans_begin();
        $locked=$this->CI->db->query('SELECT * FROM endorse_v2_refresh_jobs WHERE id=? FOR UPDATE',[$job['id']])->row_array();
        if (!$locked || $locked['status']!=='processing' || (int)$locked['active_attempt_id']!==(int)$job['active_attempt_id']) { $this->CI->db->trans_rollback(); return ['status'=>false,'code'=>'stale_result']; }
        $attempt=$this->CI->db->query('SELECT * FROM endorse_v2_refresh_attempts WHERE id=? FOR UPDATE',[$job['active_attempt_id']])->row_array();
        if (!$attempt || !hash_equals($attempt['worker_id'],$workerId)) { $this->CI->db->trans_rollback(); return ['status'=>false,'code'=>'stale_result']; }
        $endorse=$this->CI->db->query('SELECT * FROM endorse WHERE id=? FOR UPDATE',[$locked['endorse_id']])->row_array();
        $state=$endorse?$this->CI->db->query('SELECT * FROM endorse_v2_content_state WHERE endorse_id=? FOR UPDATE',[$endorse['id']])->row_array():null;
        if (!$endorse || !$state || (int)$state['content_generation'] !== (int)$locked['content_generation'] || (int)$state['state_version'] !== (int)$locked['state_version']) { $this->CI->db->trans_rollback(); return ['status'=>false,'code'=>'stale_result']; }
        $result=$this->CI->endorsev2writer->applyLocked($endorse,$state,$response,(int)$attempt['id'],$actorId);
        if (!$result['ok']) { $this->CI->db->trans_rollback(); return $this->finishError($job,$workerId,$result['classification']['class'],$result['classification']['msg']); }
        $now=EndorseV2Writer::utcNow(); $this->CI->db->update('endorse_v2_refresh_attempts',['completed_at'=>$now,'result_class'=>'ok','result_message'=>'OK'],['id'=>$attempt['id']]);
        $this->CI->db->update('endorse_v2_refresh_jobs',['status'=>'completed','completed_at'=>$now,'active_attempt_id'=>null,'lease_expires_at'=>null,'updated_at'=>$now],['id'=>$locked['id']]);
        $this->CI->db->delete('endorse_v2_refresh_dedupe',['job_id'=>$locked['id']]); $this->markRollupDirty((int)$endorse['id_campaign'],$now); $this->CI->db->trans_commit();
        return ['status'=>true,'job_id'=>(int)$locked['id'],'metrics'=>$result['metrics']];
    }

    private function finishError(array $job,string $workerId,string $class,string $message): array
    {
        $this->CI->db->trans_begin(); $locked=$this->CI->db->query('SELECT * FROM endorse_v2_refresh_jobs WHERE id=? FOR UPDATE',[$job['id']])->row_array();
        if (!$locked || $locked['status']!=='processing' || (int)$locked['active_attempt_id']!==(int)$job['active_attempt_id']) { $this->CI->db->trans_rollback(); return ['status'=>false,'code'=>'stale_result']; }
        $now=EndorseV2Writer::utcNow(); $terminal=in_array($class,['permanent','empty','deferred'],true) || (int)$job['attempt_sequence']>=3;
        $status=$class==='deferred'?'deferred':($terminal?'failed':'retry_scheduled'); $update=['status'=>$status,'error_class'=>$class,'error_message'=>substr($message,0,512),'active_attempt_id'=>null,'lease_expires_at'=>null,'updated_at'=>$now];
        if (!$terminal) $update['available_at']=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->modify('+'.(int)(60*pow(2,(int)$job['attempt_sequence']-1)).' seconds')->format('Y-m-d H:i:s.u'); else $update['completed_at']=$now;
        $this->CI->db->update('endorse_v2_refresh_attempts',['completed_at'=>$now,'result_class'=>$class,'result_message'=>substr($message,0,512)],['id'=>$job['active_attempt_id']]); $this->CI->db->update('endorse_v2_refresh_jobs',$update,['id'=>$job['id']]);
        if ($terminal) $this->CI->db->delete('endorse_v2_refresh_dedupe',['job_id'=>$job['id']]); $this->CI->db->trans_commit(); return ['status'=>false,'job_id'=>(int)$job['id'],'error_class'=>$class,'msg'=>$message];
    }
    private function stateForLocked(array $endorse): array { $state=$this->CI->db->query('SELECT * FROM endorse_v2_content_state WHERE endorse_id=? FOR UPDATE',[$endorse['id']])->row_array(); if($state)return $state; $i=EndorseV2Identity::identify((string)$endorse['platform'],(string)$endorse['link_upload']); $now=EndorseV2Writer::utcNow(); $this->CI->db->insert('endorse_v2_content_state',['endorse_id'=>$endorse['id'],'content_generation'=>1,'state_version'=>1,'platform'=>$i['platform'],'platform_content_id'=>$i['platform_content_id'],'canonical_url'=>$i['canonical_url'],'canonical_url_hash'=>$i['canonical_url_hash'],'trusted_views'=>(int)$endorse['views'],'trusted_likes'=>(int)$endorse['likes'],'trusted_comments'=>(int)$endorse['comment'],'trusted_share_save'=>(int)$endorse['share_save'],'created_at'=>$now,'updated_at'=>$now]); return $this->CI->db->get_where('endorse_v2_content_state',['endorse_id'=>$endorse['id']])->row_array(); }
    private function markRollupDirty(int $campaignId,string $now): void { $row=$this->CI->db->query('SELECT * FROM endorse_v2_rollup_work WHERE campaign_id=? FOR UPDATE',[$campaignId])->row_array(); if($row)$this->CI->db->update('endorse_v2_rollup_work',['dirty_version'=>(int)$row['dirty_version']+1,'status'=>'pending','updated_at'=>$now],['campaign_id'=>$campaignId]); else $this->CI->db->insert('endorse_v2_rollup_work',['campaign_id'=>$campaignId,'dirty_version'=>1,'processed_version'=>0,'status'=>'pending','created_at'=>$now,'updated_at'=>$now]); }
}
