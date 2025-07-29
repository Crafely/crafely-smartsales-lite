<?php

namespace AISMARTSALES\Includes\CPT;

if (!defined('ABSPATH')) {
    exit;
}
class PostTypes
{
    public function __construct()
    {
        add_action('init', array($this, 'register_post_types'));
    }

    public function register_post_types()
    {
        // Register Outlet post type
        register_post_type('smartsales_outlet', [
            'labels' => [
                'name' => __('Outlets', 'crafely-smartsales-lite'),
                'singular_name' => __('Outlet', 'crafely-smartsales-lite'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'aipos',
            'supports' => ['title'],
            'capabilities' => [
                'edit_post' => 'smartsales_manage_outlet',
                'read_post' => 'smartsales_manage_outlet',
                'delete_post' => 'smartsales_manage_outlet',
                'edit_posts' => 'smartsales_manage_outlet',
                'edit_others_posts' => 'smartsales_manage_outlet',
                'publish_posts' => 'smartsales_manage_outlet',
                'read_private_posts' => 'smartsales_manage_outlet'
            ],
        ]);

        // Register Counter post type
        register_post_type('smartsales_counter', [
            'labels' => [
                'name' => __('Counters', 'crafely-smartsales-lite'),
                'singular_name' => __('Counter', 'crafely-smartsales-lite'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'aipos',
            'supports' => ['title'],
            'capabilities' => [
                'edit_post' => 'smartsales_manage_counters',
                'read_post' => 'smartsales_manage_counters',
                'delete_post' => 'smartsales_manage_counters',
                'edit_posts' => 'smartsales_manage_counters',
                'edit_others_posts' => 'smartsales_manage_counters',
                'publish_posts' => 'smartsales_manage_counters',
                'read_private_posts' => 'smartsales_manage_counters'
            ],
        ]);

        // Register Assignment History post type
        register_post_type('smsl_assign_hist', [
            'labels' => [
                'name' => __('Assignment History', 'crafely-smartsales-lite'),
                'singular_name' => __('Assignment History', 'crafely-smartsales-lite'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'aipos',
            'supports' => ['title', 'editor'],
        ]);

        // Register Invoice post type
        register_post_type('smartsales_invoice', [
            'labels' => [
                'name' => __('Invoices', 'crafely-smartsales-lite'),
                'singular_name' => __('Invoice', 'crafely-smartsales-lite'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'aipos',
            'show_in_rest' => true,  // Important for REST API
            'supports' => ['title'],
            'capabilities' => [
                'edit_post' => 'smartsales_manage_invoices',
                'read_post' => 'smartsales_manage_invoices',
                'delete_post' => 'smartsales_manage_invoices',
                'edit_posts' => 'smartsales_manage_invoices',
                'edit_others_posts' => 'smartsales_manage_invoices',
                'publish_posts' => 'smartsales_manage_invoices',
                'read_private_posts' => 'smartsales_manage_invoices'
            ],
        ]);
    }
}