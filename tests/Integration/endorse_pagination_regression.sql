-- Disposable-MySQL regression fixture for Endorse::item pagination parity.
-- Run only against a dedicated schema; it creates and drops no production data.

CREATE TEMPORARY TABLE endorse_fixture (
  id INT PRIMARY KEY,
  id_campaign INT NOT NULL,
  nama_creator VARCHAR(255) NOT NULL,
  platform VARCHAR(32) NOT NULL,
  status VARCHAR(32) NOT NULL
);

CREATE TEMPORARY TABLE influencer_fixture (
  id INT PRIMARY KEY,
  username VARCHAR(255) NOT NULL,
  contact VARCHAR(255),
  tipe_kontak VARCHAR(255)
);

INSERT INTO endorse_fixture VALUES
  (1, 10, 'normal', 'TikTok', 'Aktif'),
  (2, 10, 'orphan', 'TikTok', 'Aktif'),
  (3, 10, 'duplicate-name', 'Instagram', 'Aktif'),
  (4, 11, 'other-campaign', 'TikTok', 'Aktif'),
  (5, 10, 'inactive', 'TikTok', 'Tidak Aktif');

INSERT INTO influencer_fixture VALUES
  (1, 'normal', '081', 'phone'),
  (2, 'duplicate-name', 'first', 'phone'),
  (3, 'duplicate-name', 'second', 'email');

-- Each row below is a deterministic assertion: actual must equal expected.
SELECT 'campaign/platform/status count' AS test_name,
       (SELECT COUNT(*) FROM endorse_fixture
        WHERE id_campaign = 10 AND platform = 'TikTok' AND status = 'Aktif') AS actual,
       2 AS expected;

SELECT 'campaign-only count' AS test_name,
       (SELECT COUNT(*) FROM endorse_fixture WHERE id_campaign = 10) AS actual,
       4 AS expected;

SELECT 'instagram filter count' AS test_name,
       (SELECT COUNT(*) FROM endorse_fixture
        WHERE id_campaign = 10 AND platform = 'Instagram' AND status = 'Aktif') AS actual,
       1 AS expected;

SELECT 'empty campaign count' AS test_name,
       (SELECT COUNT(*) FROM endorse_fixture WHERE id_campaign = 99) AS actual,
       0 AS expected;

-- This mirrors the production data query. The grouped derived join guarantees
-- one UI row per Endorse row even if influencer.username becomes duplicate.
SELECT e.id, i.contact, i.tipe_kontak
FROM (SELECT * FROM endorse_fixture
      WHERE id_campaign = 10 AND platform = 'TikTok' AND status = 'Aktif') AS e
LEFT JOIN (
  SELECT username, MAX(contact) AS contact, MAX(tipe_kontak) AS tipe_kontak
  FROM influencer_fixture GROUP BY username
) AS i ON e.nama_creator = i.username
ORDER BY e.id;

SELECT 'deduplicated influencer join' AS test_name,
       (SELECT COUNT(*)
        FROM (SELECT * FROM endorse_fixture WHERE id_campaign = 10) AS e
        LEFT JOIN (
          SELECT username, MAX(contact) AS contact, MAX(tipe_kontak) AS tipe_kontak
          FROM influencer_fixture GROUP BY username
        ) AS i ON e.nama_creator = i.username) AS actual,
       4 AS expected;

-- Pagination checks: the same filtered population must be stable for every
-- supported limit. The application applies OFFSET/LIMIT only after this count.
SELECT 'page-size 10 first page' AS test_name,
       (SELECT COUNT(*) FROM (
          SELECT id FROM endorse_fixture
          WHERE id_campaign = 10 ORDER BY id LIMIT 10 OFFSET 0
        ) AS page_rows) AS actual,
       4 AS expected;

SELECT 'page-size 20 first page' AS test_name,
       (SELECT COUNT(*) FROM (
          SELECT id FROM endorse_fixture
          WHERE id_campaign = 10 ORDER BY id LIMIT 20 OFFSET 0
        ) AS page_rows) AS actual,
       4 AS expected;

SELECT 'page-size 10 empty last page' AS test_name,
       (SELECT COUNT(*) FROM (
          SELECT id FROM endorse_fixture
          WHERE id_campaign = 10 ORDER BY id LIMIT 10 OFFSET 10
        ) AS page_rows) AS actual,
       0 AS expected;

-- Manual assertions:
-- returned IDs are 1,2,3,5; orphan ID 2 is present exactly once; and ID 3
-- remains exactly once despite two matching influencer_fixture rows.
-- The target controller's search/status/platform predicates must be substituted
-- into both derived endorse subqueries for a full CI3 integration test.
