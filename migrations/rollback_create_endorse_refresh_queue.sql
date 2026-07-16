DROP TABLE IF EXISTS endorse_refresh_queue_attempts;
DROP TABLE IF EXISTS endorse_refresh_queue;

DROP INDEX idx_id_endorse_date ON endorse_logs;
DROP INDEX idx_campaign_status ON endorse;
