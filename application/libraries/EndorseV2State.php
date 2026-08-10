<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Keeps V2 content generation independent from mutable campaign/creator metadata. */
class EndorseV2State
{
    private $CI;
    public function __construct(){ $this->CI =& get_instance(); $this->CI->load->database(); $this->CI->load->library(['endorsev2runtime','endorsev2identity','endorsev2writer']); }
    public function afterEndorseUpdate(array $old,array $new): void
    {
        if (!$this->CI->endorsev2runtime->canEnqueue()) return;
        $id=(int)$old['id']; $this->CI->db->trans_begin(); $state=$this->CI->db->query('SELECT * FROM endorse_v2_content_state WHERE endorse_id=? FOR UPDATE',[$id])->row_array();
        if(!$state){$this->CI->db->trans_commit();return;}
        $identity=EndorseV2Identity::identify((string)($new['platform']??$old['platform']),(string)($new['link_upload']??$old['link_upload']));
        if(EndorseV2Identity::requiresNewGeneration($state,$identity)){
            $now=EndorseV2Writer::utcNow(); $this->CI->db->update('endorse_v2_content_state',['content_generation'=>(int)$state['content_generation']+1,'state_version'=>(int)$state['state_version']+1,'platform'=>$identity['platform'],'platform_content_id'=>$identity['platform_content_id'],'canonical_url'=>$identity['canonical_url'],'canonical_url_hash'=>$identity['canonical_url_hash'],'trusted_views'=>null,'trusted_likes'=>null,'trusted_comments'=>null,'trusted_shares'=>null,'trusted_saves'=>null,'trusted_share_save'=>null,'last_successful_observed_at'=>null,'updated_at'=>$now],['endorse_id'=>$id]);
            $this->CI->db->query("UPDATE endorse_v2_refresh_jobs SET status='cancelled', completed_at=?, error_class='identity_changed', error_message='Content identity changed', active_attempt_id=NULL, lease_expires_at=NULL, updated_at=? WHERE endorse_id=? AND status IN ('pending','retry_scheduled','processing')",[$now,$now,$id]);
        }
        // A transfer deliberately does not rewrite historical campaign_id_at_observation; only future writes use new campaign.
        $this->CI->db->trans_commit();
    }
}
