<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Internal, read-only V2 observation repository. It exposes no HTTP API. */
class EndorseV2ObservedAnalyticsReader
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function observationsForCampaign(int $campaignId, string $from, string $until, string $population): array
    {
        if ($campaignId <= 0 || $from > $until) {
            throw new InvalidArgumentException('Invalid campaign or date range');
        }
        if ($population !== EndorseV2AnalyticsPopulation::ENDORSE_ROW) {
            throw new InvalidArgumentException('Canonical population requires an approved historical identity mapping');
        }

        // One bounded query returns in-range observations and one predecessor
        // per Endorse generation. It deliberately has no per-content queries.
        $sql = "
            SELECT o.*
            FROM endorse_v2_metric_observations o
            WHERE o.campaign_id_at_observation = ?
              AND o.observation_date BETWEEN ? AND ?
            UNION ALL
            SELECT p.*
            FROM endorse_v2_metric_observations p
            INNER JOIN (
                SELECT endorse_id, content_generation, MAX(observation_date) AS predecessor_date
                FROM endorse_v2_metric_observations
                WHERE campaign_id_at_observation = ? AND observation_date < ?
                GROUP BY endorse_id, content_generation
            ) predecessor
              ON predecessor.endorse_id = p.endorse_id
             AND predecessor.content_generation = p.content_generation
             AND predecessor.predecessor_date = p.observation_date
            WHERE p.campaign_id_at_observation = ?
            ORDER BY observation_date ASC, observed_at ASC, endorse_id ASC";
        $rows = $this->CI->db->query($sql, [$campaignId, $from, $until, $campaignId, $from, $campaignId])->result_array();
        foreach ($rows as &$row) {
            $row['population_key'] = EndorseV2AnalyticsPopulation::rowKey((int) $row['endorse_id'], (int) $row['content_generation']);
        }
        unset($row);
        return $rows;
    }
}
