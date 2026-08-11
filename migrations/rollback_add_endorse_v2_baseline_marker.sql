-- Review-only rollback. Rehearse on staging before any production use.
ALTER TABLE endorse_v2_metric_observations
  DROP COLUMN baseline_reason,
  DROP COLUMN is_baseline;
