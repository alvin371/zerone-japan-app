<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Permission Library
 * 
 * Handles dynamic user permissions based on positions and individual overrides
 * Integrates with the quest level system and provides easy permission checking
 */
class Permission
{
    protected $CI;
    protected $user_permissions_cache = [];
    protected $permissions_view_name;
    protected $permissions_view_type;
    private const ALLOWED_ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mymodel');
    }
    
    /**
     * Check if user has specific permission for a module
     * 
     * @param int $user_id User ID
     * @param string $module_name Module name
     * @param string $action Permission action (view, create, edit, delete)
     * @return bool
     */
    public function check_permission($user_id, $module_name, $action = 'view')
    {
        $action = $this->normalize_action($action);

        // Cache key for performance
        $cache_key = "{$user_id}_{$module_name}_{$action}";
        
        if (isset($this->user_permissions_cache[$cache_key])) {
            return $this->user_permissions_cache[$cache_key];
        }
        
        // Check if permission tables exist first
        if (!$this->permission_tables_exist()) {
            // Use fallback role-based system
            $has_permission = $this->fallback_permission_check($user_id, $module_name, $action);
        } else {
            $permissions_view = $this->resolve_permissions_view_name();
            if ($permissions_view === '') {
                $has_permission = $this->fallback_permission_check($user_id, $module_name, $action);
            } else {
            // Use the role-based permission system
            try {
                // Use the view for easy permission checking
                $query = "SELECT can_{$action} as has_permission
                    FROM {$permissions_view}
                    WHERE user_id = ? AND module_name = ?
                    LIMIT 1";
                $result = $this->select_with_bindings($query, [$user_id, $module_name]);
                
                if (empty($result)) {
                    // No permission found, use fallback
                    $has_permission = $this->fallback_permission_check($user_id, $module_name, $action);
                } else {
                    $has_permission = $result[0]['has_permission'] == 1;
                }
            } catch (Exception $e) {
                // If any error occurs, use fallback
                $has_permission = $this->fallback_permission_check($user_id, $module_name, $action);
            }
            }
        }
        
        // Cache the result
        $this->user_permissions_cache[$cache_key] = $has_permission;
        
        return $has_permission;
    }
    
    /**
     * Check if user has access to a controller (any permission)
     * 
     * @param int $user_id User ID
     * @param string $controller Controller name
     * @return bool
     */
    public function has_module_access($user_id, $controller)
    {
        try {
            $permissions_view = $this->resolve_permissions_view_name();
            if ($permissions_view === '') {
                return $this->fallback_permission_check($user_id, $controller, 'view');
            }

            $result = $this->select_with_bindings(
                "SELECT COUNT(*) as count
                FROM {$permissions_view}
                WHERE user_id = ?
                AND controller = ?
                AND (can_view = 1 OR can_create = 1 OR can_edit = 1 OR can_delete = 1)",
                [$user_id, $controller]
            );
            
            return !empty($result) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            // Fallback to role-based check
            return $this->fallback_permission_check($user_id, $controller, 'view');
        }
    }
    
    /**
     * Get all permissions for a user
     * 
     * @param int $user_id User ID
     * @return array
     */
    public function get_user_permissions($user_id)
    {
        try {
            $permissions_view = $this->resolve_permissions_view_name();
            if ($permissions_view === '') {
                return [];
            }

            return $this->select_with_bindings(
                "SELECT
                    module_name,
                    module_display_name,
                    controller,
                    parent_id,
                    can_view,
                    can_create,
                    can_edit,
                    can_delete,
                    can_approve,
                    has_override
                FROM {$permissions_view}
                WHERE user_id = ?
                AND (can_view = 1 OR can_create = 1 OR can_edit = 1 OR can_delete = 1 OR can_approve = 1)
                ORDER BY module_name",
                [$user_id]
            );
        } catch (Exception $e) {
            // Return basic permissions for fallback
            return [];
        }
    }
    
    /**
     * Get user's accessible modules for sidebar
     * 
     * @param int $user_id User ID
     * @return array Hierarchical module structure
     */
    public function get_user_sidebar_modules($user_id)
    {
        $permissions_view = $this->resolve_permissions_view_name();
        if ($permissions_view === '') {
            return [];
        }

        $permissions = $this->select_with_bindings("
            SELECT 
                m.id,
                m.name,
                m.display_name,
                m.controller,
                m.icon,
                m.parent_id,
                m.sort_order,
                ump.can_view,
                ump.can_create,
                ump.can_edit,
                ump.can_delete
            FROM modules m
            LEFT JOIN {$permissions_view} ump ON m.id = ump.module_id AND ump.user_id = ?
            WHERE m.is_active = 1 
            AND (ump.can_view = 1 OR ump.can_create = 1 OR ump.can_edit = 1 OR ump.can_delete = 1)
            ORDER BY m.sort_order, m.display_name
        ", [$user_id]);
        
        return $this->build_module_tree($permissions);
    }
    
    /**
     * Build hierarchical module tree
     * 
     * @param array $modules Flat module array
     * @param int $parent_id Parent ID
     * @return array
     */
    private function build_module_tree($modules, $parent_id = null)
    {
        $tree = [];
        
        foreach ($modules as $module) {
            if ($module['parent_id'] == $parent_id) {
                $module['children'] = $this->build_module_tree($modules, $module['id']);
                $tree[] = $module;
            }
        }
        
        return $tree;
    }
    
    /**
     * Check if user can perform action on current controller
     * Uses current CI controller and method
     * 
     * @param int $user_id User ID
     * @param string $action Permission action
     * @return bool
     */
    public function can_access_current($user_id, $action = 'view')
    {
        $controller = $this->CI->router->fetch_class();
        $module_name = $this->resolve_module_name($controller);

        return $this->check_permission($user_id, $module_name, $action);
    }
    
    /**
     * Check if permission tables exist
     * 
     * @return bool
     */
    private function permission_tables_exist()
    {
        try {
            // Check if role-based permission tables exist
            $modules = $this->CI->mymodel->selectWithQuery("SHOW TABLES LIKE 'modules'");
            $roles = $this->CI->mymodel->selectWithQuery("SHOW TABLES LIKE 'roles'");
            $role_permissions = $this->CI->mymodel->selectWithQuery("SHOW TABLES LIKE 'role_permissions'");
            $user_roles = $this->CI->mymodel->selectWithQuery("SHOW TABLES LIKE 'user_roles'");
            $permissions_view = $this->resolve_permissions_view_name();
            
            return !empty($modules)
                && !empty($roles)
                && !empty($role_permissions)
                && !empty($user_roles)
                && $permissions_view !== '';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Fallback permission check when view doesn't exist
     * Queries role_permissions directly via user_roles
     *
     * @param int $user_id User ID
     * @param string $module_name Module name
     * @param string $action Permission action
     * @return bool
     */
    private function fallback_permission_check($user_id, $module_name, $action)
    {
        $action = $this->normalize_action($action);

        try {
            // Query user permissions through role_permissions table
            $query = "SELECT MAX(rp.can_{$action}) as has_permission
                FROM user u
                INNER JOIN user_roles ur ON u.id = ur.user_id
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                INNER JOIN role_permissions rp ON r.id = rp.role_id
                INNER JOIN modules m ON rp.module_id = m.id AND m.is_active = 1
                WHERE u.id = ?
                AND m.name = ?
                GROUP BY u.id, m.name";
            $result = $this->select_with_bindings($query, [$user_id, $module_name]);

            if (!empty($result) && isset($result[0]['has_permission'])) {
                return $result[0]['has_permission'] == 1;
            }

            // If no specific permission found, check if user has admin role
            $user_roles = $this->select_with_bindings(
                "SELECT r.name, r.level
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = ? AND r.is_active = 1",
                [$user_id]
            );

            // Super admin and admin roles get full access
            foreach ($user_roles as $role) {
                if (in_array(strtolower($role['name']), ['super_admin', 'admin'])) {
                    return true;
                }
            }

            // Basic modules everyone can view
            $basic_modules = ['dashboard', 'profile', 'home'];
            if (in_array($module_name, $basic_modules) && $action === 'view') {
                return true;
            }

            return false;

        } catch (Exception $e) {
            // Last resort: use old role-based system
            $user = $this->select_with_bindings(
                "SELECT role FROM user WHERE id = ? LIMIT 1",
                [$user_id]
            );

            if (empty($user)) {
                return false;
            }

            $role = $user[0]['role'];

            // Legacy role IDs: HR/Admin roles (1, 2, 7) get full access
            if (in_array($role, ['1', '2', '7'])) {
                return true;
            }

            // Basic modules everyone can view
            $basic_modules = ['dashboard', 'profile', 'quest'];
            if (in_array($module_name, $basic_modules) && $action === 'view') {
                return true;
            }

            return false;
        }
    }

    /**
     * Get controller to module name mapping
     * 
     * @return array
     */
    private function resolve_module_name($controller)
    {
        if ($controller === 'ads') {
            $platform = $this->CI->input->get('m');
            return $platform ? "ads_" . strtolower($platform) : 'overview';
        }

        if ($controller === 'crm') {
            $brand = $this->CI->input->get('brand');
            return $brand ? "crm_" . strtolower($brand) : 'crm_mg';
        }

        $controller_module_map = [
            'dashboard' => 'dashboard',
            'report' => 'report',
            'expense' => 'expense',
            'overview' => 'overview',
            'influencer' => 'influencer',
            'influencer_dummy' => 'influencer_dummy',
            'endorse_campaign' => 'endorse_campaign',
            'endorse' => 'endorse_campaign',
            'calendar' => 'calendar',
            'payment' => 'payment',
            'codeboost' => 'codeboost',
            'marketplace_account' => 'marketplace_account',
            'transaction' => 'transaction',
            'transaction_item' => 'transaction_item',
            'group_wa' => 'group_wa',
            'stock' => 'stock',
            'product' => 'product',
            'product_3rd' => 'product_3rd',
            'quest_level' => 'quest_level',
            'position' => 'position',
            'roles' => 'roles',
            'benefit' => 'benefit',
            'quest' => 'quest',
            'milestone' => 'milestone',
            'modules' => 'modules',
            'user' => 'user',
            'profile' => 'profile',
            'scraper' => 'scraper',
            'recruitment' => 'recruitment'
        ];

        return $controller_module_map[$controller] ?? $controller;
    }

    private function normalize_action($action)
    {
        $action = strtolower(trim((string)$action));
        return in_array($action, self::ALLOWED_ACTIONS, true) ? $action : 'view';
    }

    public function get_permissions_view_name()
    {
        $name = $this->resolve_permissions_view_name();
        return $name !== '' ? $name : null;
    }

    public function refresh_user_permissions($user_id)
    {
        if (!$this->can_write_permissions_table()) {
            return false;
        }

        $table = $this->resolve_permissions_view_name();
        $this->select_with_bindings("DELETE FROM {$table} WHERE user_id = ?", [$user_id]);

        $insert_query = "INSERT INTO {$table} (
                user_id, user_name, legacy_role, module_id, module_name, module_display_name, controller, parent_id,
                can_view, can_create, can_edit, can_delete, can_approve, has_override, assigned_roles, role_levels
            )
            SELECT
                u.id,
                u.full_name,
                u.role,
                m.id,
                m.name,
                m.display_name,
                m.controller,
                m.parent_id,
                COALESCE(upo.can_view, rp.can_view, 0),
                COALESCE(upo.can_create, rp.can_create, 0),
                COALESCE(upo.can_edit, rp.can_edit, 0),
                COALESCE(upo.can_delete, rp.can_delete, 0),
                COALESCE(upo.can_approve, rp.can_approve, 0),
                CASE WHEN upo.user_id IS NULL THEN 0 ELSE 1 END,
                COALESCE(ura.assigned_roles, '[]'),
                COALESCE(ura.role_levels, '[]')
            FROM user u
            CROSS JOIN (
                SELECT id, name, display_name, controller, parent_id
                FROM modules
                WHERE is_active = 1
            ) m
            LEFT JOIN (
                SELECT
                    ur.user_id,
                    rp.module_id,
                    MAX(rp.can_view) AS can_view,
                    MAX(rp.can_create) AS can_create,
                    MAX(rp.can_edit) AS can_edit,
                    MAX(rp.can_delete) AS can_delete,
                    MAX(rp.can_approve) AS can_approve
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                INNER JOIN role_permissions rp ON r.id = rp.role_id
                WHERE ur.user_id = ?
                GROUP BY ur.user_id, rp.module_id
            ) rp ON rp.user_id = u.id AND rp.module_id = m.id
            LEFT JOIN user_permission_overrides upo ON upo.user_id = u.id AND upo.module_id = m.id
            LEFT JOIN (
                SELECT
                    ur.user_id,
                    CONCAT('[', GROUP_CONCAT(DISTINCT JSON_QUOTE(r.name) ORDER BY r.level DESC SEPARATOR ','), ']') AS assigned_roles,
                    CONCAT('[', GROUP_CONCAT(DISTINCT r.level ORDER BY r.level DESC SEPARATOR ','), ']') AS role_levels
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                WHERE ur.user_id = ?
                GROUP BY ur.user_id
            ) ura ON ura.user_id = u.id
            WHERE u.id = ?";

        $this->select_with_bindings($insert_query, [$user_id, $user_id, $user_id]);

        $this->clear_user_cache($user_id);
        return true;
    }

    public function refresh_role_permissions($role_id)
    {
        $users = $this->select_with_bindings(
            "SELECT user_id FROM user_roles WHERE role_id = ?",
            [$role_id]
        );

        $count = 0;
        foreach ($users as $user) {
            if ($this->refresh_user_permissions((int) $user['user_id'])) {
                $count++;
            }
        }

        return $count;
    }

    public function refresh_module_permissions($module_id)
    {
        if (!$this->can_write_permissions_table()) {
            return false;
        }

        $table = $this->resolve_permissions_view_name();
        $this->select_with_bindings("DELETE FROM {$table} WHERE module_id = ?", [$module_id]);

        $insert_query = "INSERT INTO {$table} (
                user_id, user_name, legacy_role, module_id, module_name, module_display_name, controller, parent_id,
                can_view, can_create, can_edit, can_delete, can_approve, has_override, assigned_roles, role_levels
            )
            SELECT
                u.id,
                u.full_name,
                u.role,
                m.id,
                m.name,
                m.display_name,
                m.controller,
                m.parent_id,
                COALESCE(upo.can_view, rp.can_view, 0),
                COALESCE(upo.can_create, rp.can_create, 0),
                COALESCE(upo.can_edit, rp.can_edit, 0),
                COALESCE(upo.can_delete, rp.can_delete, 0),
                COALESCE(upo.can_approve, rp.can_approve, 0),
                CASE WHEN upo.user_id IS NULL THEN 0 ELSE 1 END,
                COALESCE(ura.assigned_roles, '[]'),
                COALESCE(ura.role_levels, '[]')
            FROM user u
            INNER JOIN modules m ON m.id = ? AND m.is_active = 1
            LEFT JOIN (
                SELECT
                    ur.user_id,
                    rp.module_id,
                    MAX(rp.can_view) AS can_view,
                    MAX(rp.can_create) AS can_create,
                    MAX(rp.can_edit) AS can_edit,
                    MAX(rp.can_delete) AS can_delete,
                    MAX(rp.can_approve) AS can_approve
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                INNER JOIN role_permissions rp ON r.id = rp.role_id
                WHERE rp.module_id = ?
                GROUP BY ur.user_id, rp.module_id
            ) rp ON rp.user_id = u.id AND rp.module_id = m.id
            LEFT JOIN user_permission_overrides upo ON upo.user_id = u.id AND upo.module_id = m.id
            LEFT JOIN (
                SELECT
                    ur.user_id,
                    CONCAT('[', GROUP_CONCAT(DISTINCT JSON_QUOTE(r.name) ORDER BY r.level DESC SEPARATOR ','), ']') AS assigned_roles,
                    CONCAT('[', GROUP_CONCAT(DISTINCT r.level ORDER BY r.level DESC SEPARATOR ','), ']') AS role_levels
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                GROUP BY ur.user_id
            ) ura ON ura.user_id = u.id;";

        $this->select_with_bindings($insert_query, [$module_id, $module_id]);

        $this->clear_user_cache();
        return true;
    }

    public function refresh_all_permissions()
    {
        if (!$this->can_write_permissions_table()) {
            return false;
        }

        $table = $this->resolve_permissions_view_name();
        $this->select_with_bindings("TRUNCATE TABLE {$table}", []);

        $insert_query = "INSERT INTO {$table} (
                user_id, user_name, legacy_role, module_id, module_name, module_display_name, controller, parent_id,
                can_view, can_create, can_edit, can_delete, can_approve, has_override, assigned_roles, role_levels
            )
            SELECT
                u.id,
                u.full_name,
                u.role,
                m.id,
                m.name,
                m.display_name,
                m.controller,
                m.parent_id,
                COALESCE(upo.can_view, rp.can_view, 0),
                COALESCE(upo.can_create, rp.can_create, 0),
                COALESCE(upo.can_edit, rp.can_edit, 0),
                COALESCE(upo.can_delete, rp.can_delete, 0),
                COALESCE(upo.can_approve, rp.can_approve, 0),
                CASE WHEN upo.user_id IS NULL THEN 0 ELSE 1 END,
                COALESCE(ura.assigned_roles, '[]'),
                COALESCE(ura.role_levels, '[]')
            FROM user u
            CROSS JOIN (
                SELECT id, name, display_name, controller, parent_id
                FROM modules
                WHERE is_active = 1
            ) m
            LEFT JOIN (
                SELECT
                    ur.user_id,
                    rp.module_id,
                    MAX(rp.can_view) AS can_view,
                    MAX(rp.can_create) AS can_create,
                    MAX(rp.can_edit) AS can_edit,
                    MAX(rp.can_delete) AS can_delete,
                    MAX(rp.can_approve) AS can_approve
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                INNER JOIN role_permissions rp ON r.id = rp.role_id
                GROUP BY ur.user_id, rp.module_id
            ) rp ON rp.user_id = u.id AND rp.module_id = m.id
            LEFT JOIN user_permission_overrides upo ON upo.user_id = u.id AND upo.module_id = m.id
            LEFT JOIN (
                SELECT
                    ur.user_id,
                    CONCAT('[', GROUP_CONCAT(DISTINCT JSON_QUOTE(r.name) ORDER BY r.level DESC SEPARATOR ','), ']') AS assigned_roles,
                    CONCAT('[', GROUP_CONCAT(DISTINCT r.level ORDER BY r.level DESC SEPARATOR ','), ']') AS role_levels
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id AND r.is_active = 1
                GROUP BY ur.user_id
            ) ura ON ura.user_id = u.id;";

        $this->select_with_bindings($insert_query, []);
        $this->clear_user_cache();
        return true;
    }

    public function purge_user_permissions($user_id)
    {
        if (!$this->can_write_permissions_table()) {
            return false;
        }

        $table = $this->resolve_permissions_view_name();
        $this->select_with_bindings("DELETE FROM {$table} WHERE user_id = ?", [$user_id]);
        $this->clear_user_cache($user_id);
        return true;
    }

    private function resolve_permissions_view_name()
    {
        $this->resolve_permissions_table_info();

        if ($this->permissions_view_name !== null) {
            return $this->permissions_view_name;
        }

        return '';
    }

    private function select_with_bindings($query, array $bindings)
    {
        $result = $this->CI->db->query($query, $bindings);
        if (is_bool($result)) {
            return [];
        }
        return $result->result_array();
    }

    private function resolve_permissions_table_info()
    {
        if ($this->permissions_view_name !== null) {
            return;
        }

        $candidates = ['user_module_permissions', 'user_model_permission'];
        foreach ($candidates as $candidate) {
            $result = $this->CI->mymodel->selectWithQuery("SHOW FULL TABLES LIKE '{$candidate}'");
            if (!empty($result)) {
                $this->permissions_view_name = $candidate;
                $this->permissions_view_type = $result[0]['Table_type'] ?? 'UNKNOWN';
                return;
            }
        }

        $this->permissions_view_name = '';
        $this->permissions_view_type = '';
    }

    private function can_write_permissions_table()
    {
        $this->resolve_permissions_table_info();
        return $this->permissions_view_name !== '' && strtoupper($this->permissions_view_type) === 'BASE TABLE';
    }
    
    /**
     * Check specific ads module permission based on marketplace parameter
     * 
     * @param int $user_id User ID
     * @param string $marketplace Marketplace (tiktok, meta, shopee, lazada)
     * @param string $action Permission action
     * @return bool
     */
    public function check_ads_permission($user_id, $marketplace, $action = 'view')
    {
        $module_name = 'ads_' . strtolower($marketplace);
        return $this->check_permission($user_id, $module_name, $action);
    }
    
    /**
     * Check CRM permission based on brand parameter
     * 
     * @param int $user_id User ID
     * @param string $brand Brand (MG, POME)
     * @param string $action Permission action
     * @return bool
     */
    public function check_crm_permission($user_id, $brand, $action = 'view')
    {
        $module_name = 'crm_' . strtolower($brand);
        return $this->check_permission($user_id, $module_name, $action);
    }
    
    /**
     * Enforce permission check - redirect if no access
     * 
     * @param int $user_id User ID
     * @param string $module_name Module name
     * @param string $action Permission action
     * @param string $redirect_url Redirect URL on failure
     */
    public function enforce_permission($user_id, $module_name, $action = 'view', $redirect_url = null)
    {
        if (!$this->check_permission($user_id, $module_name, $action)) {
            if (!$redirect_url) {
                $redirect_url = base_url() . 'dashboard';
            }
            redirect($redirect_url);
        }
    }
    
    /**
     * Show 403 error page for permission denied
     * Alternative to enforce_permission that shows error page instead of redirect
     * 
     * @param int $user_id User ID
     * @param string $module_name Module name
     * @param string $action Permission action
     * @param array $data Additional data to pass to error page
     */
    public function show_403_if_no_permission($user_id, $module_name, $action = 'view', $data = [])
    {
        if (!$this->check_permission($user_id, $module_name, $action)) {
            // Set HTTP status code
            $this->CI->output->set_status_header(403);
            
            // Prepare data for the error page
            $error_data = array_merge([
                'heading' => 'Access Forbidden',
                'message' => 'You do not have permission to access this resource.',
                'module' => $module_name,
                'action' => $action,
                'user_id' => $user_id
            ], $data);
            
            // Load and display the 403 error page
            $this->CI->load->view('errors/html/error_403', $error_data);
            exit;
        }
    }
    
    /**
     * Enhanced enforce permission with option to show 403 page
     * 
     * @param int $user_id User ID
     * @param string $module_name Module name
     * @param string $action Permission action
     * @param bool $show_403 Whether to show 403 page instead of redirect
     * @param string $redirect_url Redirect URL on failure (if not showing 403)
     * @param array $error_data Additional data for 403 page
     */
    public function enforce_permission_with_403($user_id, $module_name, $action = 'view', $show_403 = false, $redirect_url = null, $error_data = [])
    {
        if (!$this->check_permission($user_id, $module_name, $action)) {
            if ($show_403) {
                $this->show_403_if_no_permission($user_id, $module_name, $action, $error_data);
            } else {
                if (!$redirect_url) {
                    $redirect_url = base_url() . 'dashboard';
                }
                redirect($redirect_url);
            }
        }
    }
    
    /**
     * Clear permission cache for user
     * 
     * @param int $user_id User ID
     */
    public function clear_user_cache($user_id = null)
    {
        if ($user_id) {
            foreach (array_keys($this->user_permissions_cache) as $key) {
                if (strpos($key, $user_id . '_') === 0) {
                    unset($this->user_permissions_cache[$key]);
                }
            }
        } else {
            $this->user_permissions_cache = [];
        }
    }
    
    /**
     * Get all positions with their permission counts
     * For management interface
     * 
     * @return array
     */
    public function get_positions_with_permissions()
    {
        return $this->CI->mymodel->selectWithQuery("
            SELECT 
                p.id,
                p.name as position_name,
                ql.name as quest_level_name,
                ql.level_order,
                COUNT(perm.id) as total_permissions,
                SUM(perm.can_view) as view_permissions,
                SUM(perm.can_create) as create_permissions,
                SUM(perm.can_edit) as edit_permissions,
                SUM(perm.can_delete) as delete_permissions
            FROM positions p
            JOIN quest_levels ql ON p.level_id = ql.id
            LEFT JOIN permissions perm ON p.id = perm.position_id
            GROUP BY p.id, p.name, ql.name, ql.level_order
            ORDER BY ql.level_order, p.name
        ");
    }
    
    /**
     * Get permission matrix for a specific position
     * 
     * @param int $position_id Position ID
     * @return array
     */
    public function get_position_permissions($position_id)
    {
        return $this->CI->mymodel->selectWithQuery("
            SELECT 
                m.id as module_id,
                m.name as module_name,
                m.display_name,
                m.parent_id,
                COALESCE(p.can_view, 0) as can_view,
                COALESCE(p.can_create, 0) as can_create,
                COALESCE(p.can_edit, 0) as can_edit,
                COALESCE(p.can_delete, 0) as can_delete
            FROM modules m
            LEFT JOIN permissions p ON m.id = p.module_id AND p.position_id = ?
            WHERE m.is_active = 1
            ORDER BY m.sort_order, m.display_name
        ", [$position_id]);
    }
    
    /**
     * Update position permissions
     * 
     * @param int $position_id Position ID
     * @param array $permissions Permission data
     * @return bool
     */
    public function update_position_permissions($position_id, $permissions)
    {
        // Start transaction
        $this->CI->db->trans_start();
        
        try {
            // Delete existing permissions for this position
            $this->CI->mymodel->deleteData('permissions', ['position_id' => $position_id]);
            
            // Insert new permissions
            foreach ($permissions as $module_id => $perms) {
                if (isset($perms['can_view']) || isset($perms['can_create']) || 
                    isset($perms['can_edit']) || isset($perms['can_delete'])) {
                    
                    $data = [
                        'position_id' => $position_id,
                        'module_id' => $module_id,
                        'can_view' => isset($perms['can_view']) ? 1 : 0,
                        'can_create' => isset($perms['can_create']) ? 1 : 0,
                        'can_edit' => isset($perms['can_edit']) ? 1 : 0,
                        'can_delete' => isset($perms['can_delete']) ? 1 : 0
                    ];
                    
                    $this->CI->mymodel->insertData('permissions', $data);
                }
            }
            
            $this->CI->db->trans_complete();
            
            if ($this->CI->db->trans_status() === FALSE) {
                return false;
            }
            
            // Clear cache
            $this->clear_user_cache();
            
            return true;
            
        } catch (Exception $e) {
            $this->CI->db->trans_rollback();
            return false;
        }
    }
}
