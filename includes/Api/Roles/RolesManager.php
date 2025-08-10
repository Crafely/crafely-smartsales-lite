<?php

namespace CSMSL\Includes\Api\Roles;

if (!defined('ABSPATH')) {
    exit;
}
class RolesManager
{
    // Define role capabilities as constants
    private const OUTLET_MANAGER_CAPS = [
        'read' => true,
        'upload_files' => true,
        'manage_products' => true,
        'manage_orders' => true,
        'manage_customers' => true,
        'view_sales_reports' => true,
        'manage_pos' => true,
        'manage_outlets' => true,
        'manage_counters' => true,
        'assign_cashiers' => true
    ];

    private const CASHIER_CAPS = [
        'read' => true,
        'upload_files' => true,
        'operate_counter' => true,
        'process_sales' => true,
        'manage_pos' => true
    ];

    private const SHOP_MANAGER_CAPS = [
        'read' => true,
        'upload_files' => true,
        'manage_products' => true,
        'manage_orders' => true,
        'manage_customers' => true,
        'view_sales_reports' => true,
        'manage_pos' => true,
        'manage_outlets' => true
    ];

    private $custom_roles = [
        'csmsl_pos_outlet_manager',
        'csmsl_pos_cashier',
        'csmsl_pos_shop_manager'
    ];

    public function __construct()
    {
        add_action('init', [$this, 'register_custom_roles']);
    }

    public function register_custom_roles()
    {
        // Backup existing users' roles before creating new ones
        $this->backup_user_roles();

        $this->create_outlet_manager_role();
        $this->create_cashier_role();
        $this->create_shop_manager_role();
        $this->add_admin_capabilities();
    }

    public function remove_custom_roles()
    {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        // Remove custom roles while preserving core capabilities
        foreach ($this->custom_roles as $role) {
            $role_obj = get_role($role);
            if ($role_obj) {
                // Remove the role but don't touch core capabilities
                remove_role($role);
            }
        }
    }

    private function create_outlet_manager_role()
    {
        if (!get_role('csmsl_pos_outlet_manager')) {
            add_role('csmsl_pos_outlet_manager', 'Pos Outlet Manager', self::OUTLET_MANAGER_CAPS);
        }
    }

    private function create_cashier_role()
    {
        if (!get_role('csmsl_pos_cashier')) {
            add_role('csmsl_pos_cashier', 'Pos Cashier', self::CASHIER_CAPS);
        }
    }

    private function create_shop_manager_role()
    {
        if (!get_role('csmsl_pos_shop_manager')) {
            add_role('csmsl_pos_shop_manager', 'Pos Shop Manager', self::SHOP_MANAGER_CAPS);
        }
    }

    private function add_admin_capabilities()
    {
        $admin = get_role('administrator');
        if ($admin) {
            foreach (array_merge(self::OUTLET_MANAGER_CAPS, self::CASHIER_CAPS, self::SHOP_MANAGER_CAPS) as $cap => $grant) {
                $admin->add_cap($cap);
            }
        }
    }

    private function backup_user_roles()
    {
        $users_with_custom_roles = [];

        foreach ($this->custom_roles as $role) {
            $users = get_users(['role' => $role]);
            foreach ($users as $user) {
                $users_with_custom_roles[$user->ID] = $role;
            }
        }

        // Store the backup with a timestamp
        update_option('csmsl_backed_up_roles', [
            'timestamp' => current_time('timestamp'),
            'roles' => $users_with_custom_roles
        ]);
    }

    private function reassign_users_to_subscriber()
    {
        $backup = get_option('csmsl_backed_up_roles', []);
        if (!empty($backup['roles'])) {
            foreach ($backup['roles'] as $user_id => $role) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    $user->set_role('subscriber');
                }
            }
        }
    }
}