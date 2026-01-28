-- Add Overview and Influencer Listing modules under Marketing parent

-- Ensure Marketing parent exists
INSERT INTO modules (name, display_name, controller, icon, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'marketing', 'Marketing', NULL, 'bi bi-megaphone', NULL, 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE name = 'marketing');

UPDATE modules
SET display_name = 'Marketing',
    controller = NULL,
    icon = 'bi bi-megaphone',
    parent_id = NULL,
    sort_order = 0,
    is_active = 1,
    updated_at = NOW()
WHERE name = 'marketing';

SET @marketing_id = (SELECT id FROM modules WHERE name = 'marketing' LIMIT 1);

-- Add Overview module
INSERT INTO modules (name, display_name, controller, icon, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'overview', 'Overview', 'overview', 'bi bi-speedometer2', @marketing_id, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE name = 'overview');

UPDATE modules
SET display_name = 'Overview',
    controller = 'overview',
    icon = 'bi bi-speedometer2',
    parent_id = @marketing_id,
    sort_order = 1,
    is_active = 1,
    updated_at = NOW()
WHERE name = 'overview';

-- Add Influencer Listing module
INSERT INTO modules (name, display_name, controller, icon, parent_id, sort_order, is_active, created_at, updated_at)
SELECT 'influencer_dummy', 'Influencer Listing', 'influencer_dummy', 'bi bi-person-lines-fill', @marketing_id, 5, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE name = 'influencer_dummy');

UPDATE modules
SET display_name = 'Influencer Listing',
    controller = 'influencer_dummy',
    icon = 'bi bi-person-lines-fill',
    parent_id = @marketing_id,
    sort_order = 5,
    is_active = 1,
    updated_at = NOW()
WHERE name = 'influencer_dummy';

-- Copy Marketing permissions to Overview for existing roles
INSERT INTO role_permissions (role_id, module_id, can_view, can_create, can_edit, can_delete, can_approve)
SELECT rp.role_id, m_overview.id, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete, rp.can_approve
FROM role_permissions rp
INNER JOIN modules m_marketing ON rp.module_id = m_marketing.id AND m_marketing.name = 'marketing'
INNER JOIN modules m_overview ON m_overview.name = 'overview'
LEFT JOIN role_permissions rp_overview ON rp_overview.role_id = rp.role_id AND rp_overview.module_id = m_overview.id
WHERE rp_overview.id IS NULL;

-- Copy Influencer permissions to Influencer Listing for existing roles
INSERT INTO role_permissions (role_id, module_id, can_view, can_create, can_edit, can_delete, can_approve)
SELECT rp.role_id, m_listing.id, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete, rp.can_approve
FROM role_permissions rp
INNER JOIN modules m_influencer ON rp.module_id = m_influencer.id AND m_influencer.name = 'influencer'
INNER JOIN modules m_listing ON m_listing.name = 'influencer_dummy'
LEFT JOIN role_permissions rp_listing ON rp_listing.role_id = rp.role_id AND rp_listing.module_id = m_listing.id
WHERE rp_listing.id IS NULL;
