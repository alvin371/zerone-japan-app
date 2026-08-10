-- Disposable MySQL 8 fixture for the proposed V2 baseline marker.
-- This file never alters production tables.
CREATE TEMPORARY TABLE endorse_v2_observation_fixture (
  endorse_id BIGINT UNSIGNED NOT NULL,
  content_generation INT UNSIGNED NOT NULL,
  observation_date DATE NOT NULL,
  observation_kind ENUM('provider','manual_override','legacy_import') NOT NULL,
  is_baseline TINYINT(1) NOT NULL DEFAULT 0,
  baseline_reason ENUM('first_generation_observation','legacy_import_anchor') NULL,
  views_before BIGINT UNSIGNED NULL,
  views_after BIGINT UNSIGNED NULL,
  PRIMARY KEY (endorse_id, content_generation, observation_date)
);

-- First V2 observation remains an opening balance even when legacy/current
-- metrics existed before V2.
INSERT INTO endorse_v2_observation_fixture VALUES
  (1, 1, '2026-08-01', 'provider', 1, 'first_generation_observation', 150000, 150000),
  -- Same-day refresh updates the daily observation and is no longer baseline.
  (1, 1, '2026-08-02', 'provider', 0, NULL, 150000, 170000),
  -- Imported predecessor allows the first live provider result to be a delta.
  (2, 1, '2026-07-31', 'legacy_import', 1, 'legacy_import_anchor', 90000, 90000),
  (2, 1, '2026-08-01', 'provider', 0, NULL, 90000, 110000),
  -- Identity change starts a new baseline generation.
  (1, 2, '2026-08-03', 'provider', 1, 'first_generation_observation', 5000, 5000);

SELECT 'first provider observation is baseline' AS test_name,
       (SELECT views_after - views_before FROM endorse_v2_observation_fixture
        WHERE endorse_id=1 AND content_generation=1 AND observation_date='2026-08-01') AS actual,
       0 AS expected;

SELECT 'imported predecessor enables live delta' AS test_name,
       (SELECT views_after - views_before FROM endorse_v2_observation_fixture
        WHERE endorse_id=2 AND content_generation=1 AND observation_date='2026-08-01') AS actual,
       20000 AS expected;

SELECT 'new generation creates baseline' AS test_name,
       (SELECT is_baseline FROM endorse_v2_observation_fixture
        WHERE endorse_id=1 AND content_generation=2 AND observation_date='2026-08-03') AS actual,
       1 AS expected;
