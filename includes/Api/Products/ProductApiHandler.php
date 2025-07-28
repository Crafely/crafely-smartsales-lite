<?php

namespace AISMARTSALES\Includes\Api\Products;

use AISMARTSALES\Includes\Api\BaseApiHandler;
use WP_Query;
use WP_REST_Response;
use WC_Product_Simple;

if (!defined('ABSPATH')) {
    exit;
}

class ProductApiHandler extends BaseApiHandler
{

    public function register_routes()
    {
        register_rest_route('ai-smart-sales/v1', '/products', [
            'methods' => 'GET',
            'callback' => [$this, 'get_products'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/products/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/products/(?P<id>\d+)/variations', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product_variations'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/products/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_product'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/products/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_product'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/products', [
            'methods' => 'POST',
            'callback' => [$this, 'create_product'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/products/bulk-delete', [
            'methods' => 'DELETE',
            'callback' => [$this, 'bulk_delete_products'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    public function check_permission($request)
    {
        // Check if user is logged in and has appropriate capabilities
        if (!is_user_logged_in()) {
            return false;
        }

        // Get current user
        $user = wp_get_current_user();

        // Check if user has any of our POS roles or is an administrator
        $allowed_roles = ['administrator', 'aipos_outlet_manager', 'aipos_cashier', 'aipos_shop_manager'];
        $user_roles = (array) $user->roles;

        if (!array_intersect($allowed_roles, $user_roles)) {
            return false;
        }

        return true;
    }

    private function format_error_response($message, $errors = [], $statusCode = 400, $path = '')
    {
        $error = [];

        if (is_array($errors) && !empty($errors) && array_keys($errors) !== range(0, count($errors) - 1)) {
            $error = $errors;
        } else {
            $error = [
                'error' => $message,
            ];
        }

        return [
            'success' => false,
            'message' => $message,
            'data' => [], // Changed from null to empty array
            'error' => $error,
        ];
    }

    private function format_product_response($product)
    {
        $default_image_url = SMARTSALES_URL . 'assets/images/product.png';
        $default_sku = 'N/A';

        // Get the product image ID
        $product_image_id = $product->get_image_id();

        // Get the image URL, or use the default if no image is set
        $image_url = $product_image_id ? wp_get_attachment_url($product_image_id) : $default_image_url;

        // If the image URL is false or empty, use the default image
        if (empty($image_url)) {
            $image_url = $default_image_url;
        }

        return [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price' => intval($product->get_price()),
            'regular_price' => intval($product->get_regular_price()),
            'sale_price' => intval($product->get_sale_price()) ?: intval($product->get_regular_price()),
            // 'currency' => get_woocommerce_currency(),
            'stock' => $product->get_manage_stock() ? intval($product->get_stock_quantity()) : null,
            'sku' => $product->get_sku() ?: $default_sku,
            'featured' => $product->is_featured(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'status' => $product->get_status(),
            'categories' => wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'ids']),
            'tags' => $product->get_tag_ids(),
            'image_url' => $image_url,
        ];
    }

    public function get_products($request)
    {
        $current_page = $request->get_param('current_page') ? intval($request->get_param('current_page')) : 1;
        $per_page = $request->get_param('per_page') ? intval($request->get_param('per_page')) : 10;
        $search_query = $request->get_param('q');

        $args = [
            'post_type'      => 'product',
            'post_status'    => $request->get_param('status') ?: 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $current_page,
            'orderby'        => $request->get_param('orderby') ?: 'date',
            'order'          => $request->get_param('order') ?: 'DESC',
        ];

        // Add search functionality
        if (!empty($search_query)) {
            $args['s'] = sanitize_text_field($search_query);

            // Add meta query for SKU search
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => '_sku',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ]
            ];

            // Remove default WP search behavior
            remove_filter('posts_search', 'relevanssi_prevent_default_request', 10);

            // Add custom search filter
            add_filter('posts_search', function ($search, $query) use ($search_query) {
                global $wpdb;

                if (!empty($search) && !empty($query->query_vars['s'])) {
                    $like = '%' . $wpdb->esc_like($search_query) . '%';

                    // Search in title, excerpt, content, and SKU
                    $search = $wpdb->prepare(
                        " AND (
                            {$wpdb->posts}.post_title LIKE %s 
                            OR {$wpdb->posts}.post_excerpt LIKE %s 
                            OR {$wpdb->posts}.post_content LIKE %s 
                            OR EXISTS (
                                SELECT 1 
                                FROM {$wpdb->postmeta} 
                                WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                                AND {$wpdb->postmeta}.meta_key = '_sku' 
                                AND {$wpdb->postmeta}.meta_value LIKE %s
                            )
                        )",
                        $like,
                        $like,
                        $like,
                        $like
                    );
                }
                return $search;
            }, 10, 2);

            // Also search in product categories and tags
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            $args['tax_query'] = [
                'relation' => 'OR',
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'name',
                    'terms'    => $search_query,
                    'operator' => 'LIKE'
                ],
                [
                    'taxonomy' => 'product_tag',
                    'field'    => 'name',
                    'terms'    => $search_query,
                    'operator' => 'LIKE'
                ]
            ];
        }

        // Validate orderby parameter
        $valid_orderby_values = ['date', 'title', 'price', 'popularity'];
        if (!in_array($args['orderby'], $valid_orderby_values)) {
            return new WP_REST_Response($this->format_error_response(
                'Invalid orderby value.',
                [
                    'orderby' => "The orderby value '{$args['orderby']}' is not supported.",
                ],
                400,
                $request->get_route()
            ), 400);
        }

        // Validate order parameter
        $valid_order_values = ['ASC', 'DESC'];
        if (!in_array(strtoupper($args['order']), $valid_order_values)) {
            return new WP_REST_Response($this->format_error_response(
                'Invalid order value.',
                [
                    'order' => "The order value '{$args['order']}' is not supported.",
                ],
                400,
                $request->get_route()
            ), 400);
        }

        // Validate category parameter
        if ($category = $request->get_param('category')) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $category,
                ],
            ];
        }

        $query = new WP_Query($args);
        $products = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                $products[] = $this->format_product_response($product);
            }
            wp_reset_postdata();
        }

        if (empty($products)) {
            return new WP_REST_Response([
                'success' => true, // Changed to true since empty results are valid
                'message' => 'No products found.',
                'data' => [], // Changed from null to empty array
                'pagination' => [
                    'total_products' => 0,
                    'total_pages' => 0,
                    'current_page' => $current_page,
                    'per_page' => $per_page,
                ],
            ], 200); // Changed from 404 to 200 since this is not an error
        }

        // Add pagination information to the response
        $total_products = $query->found_posts;
        $total_pages    = $query->max_num_pages;

        return new WP_REST_Response([
            'success'    => true,
            'message'    => 'Products retrieved successfully.',
            'data'       => $products,
            'pagination' => [
                'total_products' => $total_products,
                'total_pages'    => $total_pages,
                'current_page'   => $current_page,
                'per_page'       => $per_page,
            ],
        ], 200);
    }


    public function get_product($data)
    {
        $product_id = $data['id'];
        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_REST_Response($this->format_error_response(
                'Product not found.',
                [
                    'id' => "The product with the ID '{$product_id}' does not exist.",
                ],
                404,
                '/ai-smart-sales/v1/products/' . $product_id
            ), 404);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => $this->format_product_response($product),
        ], 200);
    }

    public function create_product($request)
    {
        $data = $request->get_json_params();

        // Define required fields and their error messages
        $required_fields = [
            'name' => 'name is required.',
            'sku' => 'sku is required.',
            'regular_price' => 'regular_price is required.',
            'stock' => 'stock is required.',
            'status' => 'status is required.',
        ];

        $errors = [];

        // Check for missing required fields
        foreach ($required_fields as $field => $error_message) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = $error_message;
            }
        }

        // If there are missing fields, return a comprehensive error response
        if (!empty($errors)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Missing required fields: ' . implode(', ', array_keys($errors)),
                'data' => [], // Changed from null to empty array
                'error' => $errors,
            ], 400);
        }

        // Validate regular_price and sale_price
        if (!is_numeric($data['regular_price']) || $data['regular_price'] < 0) {
            $errors['regular_price'] = 'Regular price must be a non-negative number.';
        }

        if (isset($data['sale_price']) && (!is_numeric($data['sale_price']) || $data['sale_price'] < 0)) {
            $errors['sale_price'] = 'Sale price must be a non-negative number.';
        }

        // Validate sale_price against regular_price
        if (isset($data['sale_price']) && $data['sale_price'] >= $data['regular_price']) {
            $errors['sale_price'] = 'Sale price must be less than the regular price.';
        }

        // Validate status
        $valid_statuses = ['draft', 'pending', 'private', 'publish'];
        if (!in_array($data['status'], $valid_statuses)) {
            $errors['status'] = "The status '{$data['status']}' is not supported.";
        }

        // Check if SKU already exists
        $existing_product_id = wc_get_product_id_by_sku($data['sku']);
        if ($existing_product_id) {
            $errors['sku'] = "A product with the SKU '{$data['sku']}' already exists.";
        }

        // If there are validation errors, return them
        if (!empty($errors)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => [], // Changed from null to empty array
                'error' => $errors,
            ], 400);
        }

        // Create the product
        $product = new WC_Product_Simple();
        $product->set_name($data['name']);
        $product->set_sku($data['sku']);
        $product->set_regular_price($data['regular_price']); // Set regular price

        // Set sale price if provided and valid
        if (isset($data['sale_price']) && $data['sale_price'] < $data['regular_price']) {
            $product->set_sale_price($data['sale_price']);
        } else {
            $product->set_sale_price(''); // Clear sale price if invalid or not provided
        }

        $product->set_status($data['status']);

        // Enable stock management and set stock quantity (only if product supports it)
        if ($product->supports('stock')) {
            $product->set_manage_stock(true); // Enable stock management
            $product->set_stock_quantity($data['stock']); // Set stock quantity
        }

        // Set optional fields if provided
        if (isset($data['description'])) {
            $product->set_description($data['description']);
        }
        if (isset($data['short_description'])) {
            $product->set_short_description($data['short_description']);
        }
        if (isset($data['categories']) && is_array($data['categories'])) {
            $product->set_category_ids($data['categories']);
        }
        if (isset($data['tags']) && is_array($data['tags'])) {
            $product->set_tag_ids($data['tags']);
        }
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $product->set_attributes($data['attributes']);
        }
        if (isset($data['image']) && (is_array($data['image']) || is_numeric($data['image']))) {
            $image_id = is_array($data['image']) ? $data['image'][0] : $data['image'];
            $product->set_image_id($image_id); // Set the single image as the product image
        }
        if (isset($data['featured'])) {
            $product->set_featured($data['featured']);
        }

        // Save the product
        $product_id = $product->save();

        if (!$product_id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Failed to create product.',
                'data' => [], // Changed from null to empty array
                'error' => [
                    'server' => 'The product could not be created.',
                ],
            ], 500);
        }

        // Refresh the product object to ensure all data is up-to-date
        $product = wc_get_product($product_id);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $this->format_product_response($product),
        ], 201);
    }

    public function update_product($request)
    {
        $product_id = $request->get_param('id');
        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_REST_Response($this->format_error_response(
                'Product not found.',
                [
                    'id' => "The product with the ID '{$product_id}' does not exist.",
                ],
                404,
                '/ai-smart-sales/v1/products/' . $product_id
            ), 404);
        }

        $data = $request->get_json_params();

        // Fix: Ensure regular_price and sale_price are properly updated
        if (isset($data['regular_price']) && is_numeric($data['regular_price']) && $data['regular_price'] > 0) {
            $product->set_regular_price((float) $data['regular_price']);
        } elseif ($product->get_regular_price() === '' || $product->get_regular_price() === null) {
            $product->set_regular_price(0); // Default price if missing
        }

        if (isset($data['sale_price']) && is_numeric($data['sale_price']) && $data['sale_price'] > 0) {
            $product->set_sale_price((float) $data['sale_price']);
        } elseif ($product->get_sale_price() === '' || $product->get_sale_price() === null) {
            $product->set_sale_price(''); // Ensure sale price is empty, not 0
        }

        // Other updates
        if (isset($data['name'])) {
            $product->set_name($data['name']);
        }
        
        // Fix stock update logic
        if (isset($data['stock'])) {
            $product->set_manage_stock(true);
            $product->set_stock_quantity((int) $data['stock']);
            $product->set_stock_status('instock'); // Set stock status to in stock
        }
        
        if (isset($data['sku']) && !empty($data['sku'])) {
            $product->set_sku($data['sku']);
        }
        if (isset($data['description'])) {
            $product->set_description($data['description']);
        }
        if (isset($data['short_description'])) {
            $product->set_short_description($data['short_description']);
        }
        if (isset($data['status'])) {
            $product->set_status($data['status']);
        }
        if (isset($data['categories'])) {
            $product->set_category_ids($data['categories']);
        }
        if (isset($data['tags'])) {
            $product->set_tag_ids($data['tags']);
        }
        if (isset($data['attributes'])) {
            $product->set_attributes($data['attributes']);
        }
        if (isset($data['image']) && (is_array($data['image']) || is_numeric($data['image']))) {
            $image_id = is_array($data['image']) ? $data['image'][0] : $data['image'];
            $product->set_image_id($image_id);
        }
        if (isset($data['featured'])) {
            $product->set_featured($data['featured']);
        }

        // Save the product
        $updated_product_id = $product->save();
        wc_delete_product_transients($product_id); // Clear cache

        if (!$updated_product_id) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to update product.',
                [
                    'server' => 'The product could not be updated.',
                ],
                500,
                $request->get_route()
            ), 500);
        }

        // Refresh the product object to ensure all data is up-to-date
        $product = wc_get_product($updated_product_id);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $this->format_product_response($product),
        ], 200);
    }



    public function delete_product($request)
    {
        $product_id = $request->get_param('id');
        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_REST_Response($this->format_error_response(
                'Product not found.',
                [
                    'id' => "The product with the ID '{$product_id}' does not exist.",
                ],
                404,
                '/ai-smart-sales/v1/products/' . $product_id
            ), 404);
        }

        $product->delete();

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data' => ['product_id' => $product_id],
        ], 200);
    }

    public function get_product_variations($data)
    {
        $product_id = $data['id'];
        $product = wc_get_product($product_id);

        if (!$product) {
            return new WP_REST_Response($this->format_error_response(
                'Product not found.',
                [
                    'id' => "The product with the ID '{$product_id}' does not exist.",
                ],
                404,
                '/ai-smart-sales/v1/products/' . $product_id . '/variations'
            ), 404);
        }

        if (!$product->is_type('variable')) {
            return new WP_REST_Response($this->format_error_response(
                'Not a variable product.',
                [
                    'type' => "The product with the ID '{$product_id}' is not a variable product.",
                ],
                400,
                '/ai-smart-sales/v1/products/' . $product_id . '/variations'
            ), 400);
        }

        $variations = $product->get_children();
        $data = [];

        foreach ($variations as $variation_id) {
            $variation = wc_get_product($variation_id);
            $data[] = [
                'id' => $variation_id,
                'name' => $variation->get_name(),
                'price' => $variation->get_price(),
                'stock' => $variation->get_stock_quantity(),
                'sku' => $variation->get_sku(),
                'attributes' => $variation->get_attributes(),
                'image_url' => wp_get_attachment_url($variation->get_image_id()),
            ];
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Product variations retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    public function bulk_delete_products($request)
    {
        $product_ids = $request->get_param('ids');

        if (empty($product_ids) || !is_array($product_ids)) {
            return new WP_REST_Response($this->format_error_response(
                'Invalid product IDs.',
                [
                    'ids' => 'Product IDs must be provided as an array.',
                ],
                400,
                $request->get_route()
            ), 400);
        }

        $deleted_products = [];
        $errors = [];

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);

            if ($product) {
                $product->delete();
                $deleted_products[] = $product_id;
            } else {
                $errors[] = "Product with ID {$product_id} not found.";
            }
        }

        if (!empty($errors)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Some products could not be deleted.',
                'data' => [
                    'deleted_products' => $deleted_products,
                    'errors' => $errors,
                ],
            ], 207); // 207 Multi-Status
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Products deleted successfully.',
            'data' => [
                'deleted_products' => $deleted_products,
            ],
        ], 200);
    }
}

new ProductApiHandler();