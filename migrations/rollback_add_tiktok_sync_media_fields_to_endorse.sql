ALTER TABLE endorse
    DROP KEY idx_endorse_tiktok_content_id,
    DROP KEY idx_endorse_tiktok_media_type,
    DROP COLUMN tiktok_fetched_at,
    DROP COLUMN tiktok_content_link,
    DROP COLUMN tiktok_cover,
    DROP COLUMN tiktok_media_type,
    DROP COLUMN tiktok_content_id;
