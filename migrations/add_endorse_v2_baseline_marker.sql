-- Review-only additive migration. Do not apply until staging rehearsal passes.
-- Existing application versions ignore both columns safely.
ALTER TABLE endorse_v2_metric_observations
  ADD COLUMN is_baseline TINYINT(1) NOT NULL DEFAULT 0 AFTER observation_kind,
  ADD COLUMN baseline_reason ENUM('first_generation_observation','legacy_import_anchor') NULL AFTER is_baseline;
