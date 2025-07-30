<?php

namespace AISMARTSALES\Includes\Core;

use AISMARTSALES\Includes\Api\Roles\RolesManager;

if (!defined('ABSPATH')) {
    exit;
}
class Activation
{
    public static function activate()
    {
        $roles_manager = new RolesManager();
        $roles_manager->register_custom_roles();
        self::create_tables();
        self::set_default_options();
        self::backup_user_roles();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        if (defined('SMARTSALES_DEV_MODE') && SMARTSALES_DEV_MODE) {
            self::cleanup_roles();
        }
        self::cleanup_tables();
        self::cleanup_options();
        self::restore_user_roles();
        $roles_manager = new RolesManager();
        $roles_manager->remove_custom_roles();
        flush_rewrite_rules();
    }

    private static function create_tables()
    {
        global $wpdb;
        // Add table creation logic here
    }

    private static function set_default_options()
    {
        add_option('SMARTSALES_VERSION', SMARTSALES_VERSION);
    }

    private static function cleanup_tables()
    {
        global $wpdb;
        // Add table cleanup logic here
    }

    private static function cleanup_options()
    {
        delete_option('SMARTSALES_VERSION');
    }

    private static function cleanup_roles()
    {
        $roles_manager = new RolesManager();
        $roles_manager->remove_custom_roles();
    }

    private static function backup_user_roles()
    {
        $users = get_users();
        $backup = array();

        foreach ($users as $user) {
            if (!empty($user->roles)) {
                // Store the user's current roles
                $backup[$user->ID] = array(
                    'roles' => $user->roles,
                    'capabilities' => get_user_meta($user->ID, 'wp_capabilities', true)
                );
            }
        }

        update_option('aismartsales_user_roles_backup', $backup);
    }

    private static function restore_user_roles()
    {
        $backup = get_option('aismartsales_user_roles_backup', array());

        foreach ($backup as $user_id => $data) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                // Remove all roles first
                $user->set_role('');

                // Check if user had administrator role
                if (in_array('administrator', $data['roles'])) {
                    $user->set_role('administrator');
                }
                // If not admin but has other default WordPress roles
                else if (array_intersect($data['roles'], array('editor', 'author', 'contributor', 'subscriber'))) {
                    foreach ($data['roles'] as $role) {
                        if (in_array($role, array('editor', 'author', 'contributor', 'subscriber'))) {
                            $user->set_role($role);
                            break;
                        }
                    }
                }
                // Default to subscriber if no valid role found
                else {
                    $user->set_role('subscriber');
                }
            }
        }

        delete_option('aismartsales_user_roles_backup');
    }

    /**
     * Manually flush rewrite rules - can be called from anywhere
     */
    public static function force_flush_rewrite_rules()
    {
        update_option('aipos_flush_rewrite_rules', true);
        // Try to flush immediately
        flush_rewrite_rules();
        // Set flag to ensure it gets flushed on next page load
        update_option('aipos_permalinks_flushed', '');
    }
}