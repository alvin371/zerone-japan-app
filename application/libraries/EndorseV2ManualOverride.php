<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Versioned, auditable manual metric floor/override; a later override may correct downward. */
class EndorseV2ManualOverride
{
    private $CI;
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->database(); $this->CI->load->library(['EndorseV2Runtime', 'EndorseV2Identity', 'EndorseV2Writer']); }
    public function set(int $endorseId, string $metric, int $value, string $reason, int $actorId): array
    {
        if (!$this->CI->endorsev2runtime->canEnqueue()) return ['status'=>false,'code'=>'v2_disabled'];
        if (!in_array($metric,['views','likes','comments','shares','saves'],true) || $value<0 || trim($reason)==='') return ['status'=>false,'code'=>'invalid_override'];
        $this->CI->db->trans_begin(); $endorse=$this->CI->db->query('SELECT * FROM endorse WHERE id=? FOR UPDATE',[$endorseId])->row_array();
        if(!$endorse){$this->CI->db->trans_rollback();return ['status'=>false,'code'=>'not_found'];}
        $state=$this->CI->db->query('SELECT * FROM endorse_v2_content_state WHERE endorse_id=? FOR UPDATE',[$endorseId])->row_array();
        if(!$state){$i=EndorseV2Identity::identify((string)$endorse['platform'],(string)$endorse['link_upload']);$now=EndorseV2Writer::utcNow();$this->CI->db->insert('endorse_v2_content_state',['endorse_id'=>$endorseId,'platform'=>$i['platform'],'platform_content_id'=>$i['platform_content_id'],'canonical_url'=>$i['canonical_url'],'canonical_url_hash'=>$i['canonical_url_hash'],'created_at'=>$now,'updated_at'=>$now]);$state=$this->CI->db->get_where('endorse_v2_content_state',['endorse_id'=>$endorseId])->row_array();}
        $now=EndorseV2Writer::utcNow(); $this->CI->db->update('endorse_v2_manual_overrides',['cleared_at'=>$now,'cleared_by'=>$actorId],['endorse_id'=>$endorseId,'content_generation'=>$state['content_generation'],'metric'=>$metric,'cleared_at'=>null]);
        $this->CI->db->insert('endorse_v2_manual_overrides',['endorse_id'=>$endorseId,'content_generation'=>$state['content_generation'],'metric'=>$metric,'value'=>$value,'reason'=>trim($reason),'created_by'=>$actorId,'created_at'=>$now]);
        $field=['views'=>'trusted_views','likes'=>'trusted_likes','comments'=>'trusted_comments','shares'=>'trusted_shares','saves'=>'trusted_saves'][$metric]; $this->CI->db->update('endorse_v2_content_state',[$field=>$value,'state_version'=>(int)$state['state_version']+1,'updated_at'=>$now],['endorse_id'=>$endorseId]);
        $legacy=['views'=>'views','likes'=>'likes','comments'=>'comment'][$metric]??null; if($legacy)$this->CI->db->update('endorse',[$legacy=>$value,'updated_at'=>gmdate('Y-m-d H:i:s'),'updated_by'=>(string)$actorId],['id'=>$endorseId]);
        $this->CI->db->trans_commit(); return ['status'=>true];
    }
}
