CREATE TABLE IF NOT EXISTS endorse_refresh_queue (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_endorse BIGINT UNSIGNED NOT NULL,
    id_campaign BIGINT UNSIGNED NOT NULL,
    platform VARCHAR(32) NOT NULL DEFAULT '',
    purpose VARCHAR(32) NOT NULL DEFAULT 'daily',
    link_upload TEXT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'pending',
    priority INT NOT NULL DEFAULT 100,
    attempts INT NOT NULL DEFAULT 0,
    max_attempts INT NOT NULL DEFAULT 5,
    error_message TEXT NULL,
    worker_id VARCHAR(64) NULL,
    claimed_at DATETIME NULL,
    enqueued_by BIGINT UNSIGNED NULL,
    retry_source_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_pop (status, priority, created_at),
    KEY idx_purpose_dedup (id_endorse, purpose, status),
    KEY idx_campaign_status (id_campaign, status),
    KEY idx_worker (status, worker_id),
    KEY idx_retry_source (retry_source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_refresh_queue_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    queue_id BIGINT UNSIGNED NOT NULL,
    attempt_no INT NOT NULL DEFAULT 1,
    worker_id VARCHAR(64) NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'processing',
    error_class VARCHAR(64) NULL,
    error_message TEXT NULL,
    started_at DATETIME NULL,
    finished_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_queue_attempt (queue_id, attempt_no),
    KEY idx_worker_status (worker_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE endorse_logs
    ADD INDEX idx_id_endorse_date (id_endorse, date);

ALTER TABLE endorse
    ADD INDEX idx_campaign_status (id_campaign, status, status_campaign);
