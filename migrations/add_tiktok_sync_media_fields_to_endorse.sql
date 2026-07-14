-- Mirrors the TikTok media fields used by the BHskin endorsement sync flow.
ALTER TABLE endorse
    ADD COLUMN tiktok_content_id VARCHAR(32) NULL AFTER link_upload,
    ADD COLUMN tiktok_media_type VARCHAR(10) NULL AFTER tiktok_content_id,
    ADD COLUMN tiktok_cover TEXT NULL AFTER tiktok_media_type,
    ADD COLUMN tiktok_content_link TEXT NOT NULL AFTER tiktok_cover,
    ADD COLUMN tiktok_fetched_at DATETIME NULL AFTER tiktok_content_link,
    ADD KEY idx_endorse_tiktok_content_id (tiktok_content_id),
    ADD KEY idx_endorse_tiktok_media_type (tiktok_media_type);
