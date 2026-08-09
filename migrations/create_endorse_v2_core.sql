-- Endorse V2 Core (reviewed additive schema; intentionally not auto-executed).
-- MySQL 8.0+, UTC values are stored in DATETIME(6); observation_date is Asia/Jakarta.
-- This migration never changes legacy endorse, endorse_logs, or endorse_refresh_queue tables.

CREATE TABLE IF NOT EXISTS endorse_v2_runtime_control (
  control_key VARCHAR(64) NOT NULL,
  control_value VARCHAR(64) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  updated_by BIGINT UNSIGNED NULL,
  PRIMARY KEY (control_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_content_state (
  endorse_id BIGINT UNSIGNED NOT NULL,
  content_generation INT UNSIGNED NOT NULL DEFAULT 1,
  state_version BIGINT UNSIGNED NOT NULL DEFAULT 1,
  platform VARCHAR(32) NOT NULL,
  platform_content_id VARCHAR(191) NULL,
  canonical_url TEXT NULL,
  canonical_url_hash BINARY(32) NULL,
  trusted_views BIGINT UNSIGNED NULL,
  trusted_likes BIGINT UNSIGNED NULL,
  trusted_comments BIGINT UNSIGNED NULL,
  trusted_shares BIGINT UNSIGNED NULL,
  trusted_saves BIGINT UNSIGNED NULL,
  trusted_share_save BIGINT UNSIGNED NULL,
  last_successful_observed_at DATETIME(6) NULL,
  last_sync_error_class VARCHAR(32) NULL,
  deleted_at DATETIME(6) NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (endorse_id),
  KEY idx_endorse_v2_state_identity (platform, platform_content_id),
  KEY idx_endorse_v2_state_url_hash (canonical_url_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_refresh_jobs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  endorse_id BIGINT UNSIGNED NOT NULL,
  campaign_id BIGINT UNSIGNED NOT NULL,
  content_generation INT UNSIGNED NOT NULL,
  state_version BIGINT UNSIGNED NOT NULL,
  purpose VARCHAR(32) NOT NULL DEFAULT 'refresh',
  status ENUM('pending','processing','retry_scheduled','completed','failed','cancelled','deferred') NOT NULL DEFAULT 'pending',
  requested_by BIGINT UNSIGNED NULL,
  available_at DATETIME(6) NOT NULL,
  claimed_at DATETIME(6) NULL,
  lease_expires_at DATETIME(6) NULL,
  active_attempt_id BIGINT UNSIGNED NULL,
  completed_at DATETIME(6) NULL,
  error_class VARCHAR(32) NULL,
  error_message VARCHAR(512) NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_endorse_v2_job_ready (status, available_at, id),
  KEY idx_endorse_v2_job_endorse (endorse_id, content_generation, status),
  KEY idx_endorse_v2_job_campaign (campaign_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_refresh_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_id BIGINT UNSIGNED NOT NULL,
  attempt_sequence INT UNSIGNED NOT NULL,
  worker_id CHAR(36) NOT NULL,
  started_at DATETIME(6) NOT NULL,
  completed_at DATETIME(6) NULL,
  provider_observed_at DATETIME(6) NULL,
  result_class VARCHAR(32) NULL,
  result_message VARCHAR(512) NULL,
  provider_reference VARCHAR(191) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_endorse_v2_attempt_sequence (job_id, attempt_sequence),
  KEY idx_endorse_v2_attempt_worker (worker_id, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_refresh_dedupe (
  dedupe_key BINARY(32) NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  expires_at DATETIME(6) NULL,
  created_at DATETIME(6) NOT NULL,
  PRIMARY KEY (dedupe_key),
  KEY idx_endorse_v2_dedupe_job (job_id),
  KEY idx_endorse_v2_dedupe_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_metric_observations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  endorse_id BIGINT UNSIGNED NOT NULL,
  campaign_id_at_observation BIGINT UNSIGNED NOT NULL,
  content_generation INT UNSIGNED NOT NULL,
  observation_date DATE NOT NULL,
  observed_at DATETIME(6) NOT NULL,
  observation_time_source ENUM('provider','worker_received','legacy_import','manual_override') NOT NULL,
  observation_kind ENUM('provider','manual_override','legacy_import') NOT NULL,
  views_before BIGINT UNSIGNED NULL,
  views_after BIGINT UNSIGNED NULL,
  likes_before BIGINT UNSIGNED NULL,
  likes_after BIGINT UNSIGNED NULL,
  comments_before BIGINT UNSIGNED NULL,
  comments_after BIGINT UNSIGNED NULL,
  shares_before BIGINT UNSIGNED NULL,
  shares_after BIGINT UNSIGNED NULL,
  saves_before BIGINT UNSIGNED NULL,
  saves_after BIGINT UNSIGNED NULL,
  anomaly_json JSON NULL,
  source_attempt_id BIGINT UNSIGNED NULL,
  source_override_id BIGINT UNSIGNED NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_endorse_v2_observation_day (endorse_id, content_generation, observation_date),
  KEY idx_endorse_v2_observation_campaign_day (campaign_id_at_observation, observation_date),
  KEY idx_endorse_v2_observation_endorse_time (endorse_id, observed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_rollup_work (
  campaign_id BIGINT UNSIGNED NOT NULL,
  dirty_version BIGINT UNSIGNED NOT NULL DEFAULT 1,
  processed_version BIGINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('pending','processing','failed') NOT NULL DEFAULT 'pending',
  worker_id CHAR(36) NULL,
  lease_expires_at DATETIME(6) NULL,
  last_error VARCHAR(512) NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (campaign_id),
  KEY idx_endorse_v2_rollup_ready (status, lease_expires_at, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_manual_overrides (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  endorse_id BIGINT UNSIGNED NOT NULL,
  content_generation INT UNSIGNED NOT NULL,
  metric VARCHAR(32) NOT NULL,
  value BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(512) NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME(6) NOT NULL,
  cleared_by BIGINT UNSIGNED NULL,
  cleared_at DATETIME(6) NULL,
  active_key VARCHAR(255) GENERATED ALWAYS AS (CASE WHEN cleared_at IS NULL THEN CONCAT(endorse_id, ':', content_generation, ':', metric) ELSE NULL END) STORED,
  PRIMARY KEY (id),
  UNIQUE KEY uq_endorse_v2_override_active (active_key),
  KEY idx_endorse_v2_override_endorse (endorse_id, content_generation, metric)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS endorse_v2_legacy_import_manifest (
  legacy_log_id BIGINT UNSIGNED NOT NULL,
  v2_observation_id BIGINT UNSIGNED NULL,
  import_class VARCHAR(32) NOT NULL,
  source_fingerprint BINARY(32) NOT NULL,
  imported_at DATETIME(6) NOT NULL,
  notes VARCHAR(512) NULL,
  PRIMARY KEY (legacy_log_id),
  KEY idx_endorse_v2_import_observation (v2_observation_id),
  KEY idx_endorse_v2_import_class (import_class)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
