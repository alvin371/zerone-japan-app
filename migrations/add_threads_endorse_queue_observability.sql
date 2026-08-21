-- Additive migration for Threads endorse jobs. Run on MySQL 8 after taking a
-- backup; it intentionally preserves old scraping_queue result rows.
ALTER TABLE scraping_queue
    ADD COLUMN id_campaign INT NULL AFTER entity_id,
    ADD COLUMN canonical_url VARCHAR(500) NULL AFTER scrape_url,
    ADD COLUMN external_post_id VARCHAR(191) NULL AFTER response_id,
    ADD COLUMN next_poll_at DATETIME NULL AFTER submitted_at,
    ADD COLUMN last_polled_at DATETIME NULL AFTER next_poll_at,
    ADD COLUMN poll_attempts INT NOT NULL DEFAULT 0 AFTER last_polled_at,
    ADD COLUMN worker_id VARCHAR(96) NULL AFTER error_message,
    ADD COLUMN lease_expires_at DATETIME NULL AFTER worker_id;

UPDATE scraping_queue q
INNER JOIN endorse e ON e.id = q.entity_id
SET q.id_campaign = e.id_campaign
WHERE q.entity_type = 'endorse' AND q.id_campaign IS NULL;

-- Keep the newest active row per entity before enforcing one active job.
UPDATE scraping_queue older
INNER JOIN scraping_queue newer
  ON newer.entity_type = older.entity_type
 AND newer.entity_id = older.entity_id
 AND newer.status IN ('pending','submitted','polling')
 AND older.status IN ('pending','submitted','polling')
 AND newer.id > older.id
SET older.status = 'failed',
    older.completed_at = NOW(),
    older.error_message = 'Superseded by newer active queue row during Threads queue migration.';

ALTER TABLE scraping_queue
    ADD COLUMN active_job_key VARCHAR(96)
      GENERATED ALWAYS AS (
        CASE WHEN entity_type = 'endorse' AND status IN ('pending','submitted','polling')
          THEN CONCAT(entity_type, ':', entity_id) ELSE NULL END
      ) STORED,
    ADD UNIQUE KEY uq_scraping_queue_active_endorse (active_job_key),
    ADD KEY idx_scraping_queue_campaign_status (id_campaign, status, created_at),
    ADD KEY idx_scraping_queue_poll_ready (status, next_poll_at, lease_expires_at);

CREATE TABLE IF NOT EXISTS scraping_queue_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    queue_id INT NOT NULL,
    attempt_no INT NOT NULL,
    phase ENUM('submit','poll','persist','retry') NOT NULL,
    worker_id VARCHAR(96) NULL,
    external_job_id VARCHAR(255) NULL,
    status VARCHAR(24) NOT NULL,
    error_class VARCHAR(64) NULL,
    error_message VARCHAR(512) NULL,
    started_at DATETIME NOT NULL,
    finished_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_scraping_queue_attempt (queue_id, attempt_no, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
