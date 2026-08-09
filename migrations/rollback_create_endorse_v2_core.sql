-- Roll back only after V2 writers/workers are quiesced and an export/backup has been verified.
DROP TABLE IF EXISTS endorse_v2_legacy_import_manifest;
DROP TABLE IF EXISTS endorse_v2_manual_overrides;
DROP TABLE IF EXISTS endorse_v2_rollup_work;
DROP TABLE IF EXISTS endorse_v2_metric_observations;
DROP TABLE IF EXISTS endorse_v2_refresh_dedupe;
DROP TABLE IF EXISTS endorse_v2_refresh_attempts;
DROP TABLE IF EXISTS endorse_v2_refresh_jobs;
DROP TABLE IF EXISTS endorse_v2_content_state;
DROP TABLE IF EXISTS endorse_v2_runtime_control;
