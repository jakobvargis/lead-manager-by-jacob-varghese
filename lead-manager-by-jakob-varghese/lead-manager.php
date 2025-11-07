<?php
/*
Plugin Name: Lead Manager by Jakob Varghese
Plugin URI: https://profiles.wordpress.org/jakobvarghese/
Description: A professional yet simple lead management plugin with AJAX form, REST API, dashboard widget, and frontend CRUD integration. Built for agencies, small businesses, and developers.
Version: 1.0.0
Author: Jacob Varghese
Author URI: https://profiles.wordpress.org/jakobvarghese/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lead-manager
Domain Path: /languages
*/

define('LEAD_MANAGER_VERSION', '1.0.0');

if (!defined('ABSPATH')) exit;


// ==================================================
// FRONTEND ASSETS
// ==================================================
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('lm-frontend', plugin_dir_url(__FILE__).'assets/js/lead-form.js', ['jquery'], LEAD_MANAGER_VERSION, true);
    wp_localize_script('lm-frontend', 'lm_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('lm_nonce')
    ]);
    wp_enqueue_style('lm-css', plugin_dir_url(__FILE__).'assets/css/frontend.css', array(), LEAD_MANAGER_VERSION);
});

// ==================================================
// REST API: Get Leads (admin only)
// ==================================================
add_action('rest_api_init', function(){
    register_rest_route('lead-manager/v1','/leads', [
        'methods'             => 'GET',
        'callback'            => 'lm_get_leads',
        'permission_callback' => function(){ return current_user_can('edit_posts'); }
    ]);
});

function lm_get_leads($request){
    $args = ['post_type'=>'lead','posts_per_page'=>20,'post_status'=>'publish'];
    $q = new WP_Query($args);
    $data = [];
    while($q->have_posts()){ $q->the_post();
        global $post;
        $data[] = [
            'id'     => $post->ID,
            'title'  => get_the_title($post->ID),
            'email'  => get_post_meta($post->ID,'_pp_email', true),
            'phone'  => get_post_meta($post->ID,'_pp_phone', true),
            'status' => get_post_meta($post->ID,'_pp_status', true),
            'date'   => $post->post_date
        ];
    }
    wp_reset_postdata();
    return rest_ensure_response($data);
}

// ==================================================
// SHORTCODE: [lead_form]
// ==================================================
add_shortcode('lead_form', function(){
    ob_start(); ?>
    <form id="lm-lead-form" class="lm-lead-form">
        <p><input name="name" id="lm_name" placeholder="Your name" required></p>
        <p><input name="email" id="lm_email" placeholder="Email" type="email" required></p>
        <p><input name="phone" id="lm_phone" placeholder="Phone"></p>
        <p><button type="submit">Submit</button></p>
        <div id="lm-result" style="margin-top:8px;"></div>
    </form>
    <?php
    return ob_get_clean();
});

// ==================================================
// AJAX HANDLER: Save Lead
// ==================================================
add_action('wp_ajax_nopriv_lm_submit_lead','lm_submit_lead');
add_action('wp_ajax_lm_submit_lead','lm_submit_lead');

function lm_submit_lead(){
    check_ajax_referer('lm_nonce','security');

    $name  = isset($_POST['name'])  ? sanitize_text_field( wp_unslash($_POST['name']) )  : '';
    $email = isset($_POST['email']) ? sanitize_email( wp_unslash($_POST['email']) )      : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field( wp_unslash($_POST['phone']) ) : '';


    if (empty($email) && empty($phone)) {
        wp_send_json_error('Please provide an email or phone.');
    }

    $post_id = wp_insert_post([
        'post_type'   => 'lead',
        'post_title'  => $name ?: 'Lead - '.time(),
        'post_status' => 'publish'
    ]);

    if (is_wp_error($post_id) || !$post_id) wp_send_json_error('Failed to save lead.');

    update_post_meta($post_id, '_pp_email', $email);
    update_post_meta($post_id, '_pp_phone', $phone);
    update_post_meta($post_id, '_pp_status', 'new');

    do_action('lm_after_save_lead', $post_id);

    wp_send_json_success('Thanks! Your lead is recorded.');
}

// ==================================================
// DASHBOARD WIDGET
// ==================================================
add_action('wp_dashboard_setup','lm_dashboard_widget');
function lm_dashboard_widget(){
    wp_add_dashboard_widget('lm_widget','Lead Manager – Recent Leads','lm_widget_display');
}

function lm_widget_display(){
    $leads = get_posts(['post_type'=>'lead','numberposts'=>6]);
    if (!$leads){ echo '<p>No leads yet.</p>'; return; }
    echo '<ul>';
    foreach($leads as $lead){
        $email  = get_post_meta($lead->ID,'_pp_email',true);
        $status = get_post_meta($lead->ID,'_pp_status',true);
        $link   = get_edit_post_link($lead->ID);
        echo '<li><a href="'.esc_url($link).'"><strong>'.esc_html($lead->post_title).'</strong></a> — '.esc_html($email).' <em>('.esc_html($status).')</em></li>';
    }
    echo '</ul>';
}

// ==================================================
// ADMIN STYLES
// ==================================================
add_action('admin_enqueue_scripts', function(){
    wp_enqueue_style('lm-admin-css', plugin_dir_url(__FILE__).'assets/css/admin.css', array(), LEAD_MANAGER_VERSION);
});

// ==================================================
// PLUGIN ACTIVATION: create "Submit Lead" page
// ==================================================
register_activation_hook(__FILE__, function() {
    $page = get_page_by_path('submit-lead');
    if (!$page) {
        wp_insert_post([
            'post_title'   => 'Submit Lead',
            'post_name'    => 'submit-lead',
            'post_content' => '[lead_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ]);
        update_option('lm_page_created', true);
    }
    flush_rewrite_rules();
});

add_action('admin_notices', function() {
    if (get_option('lm_page_created')) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Lead Manager:</strong> “Submit Lead” page created successfully.</p></div>';
        delete_option('lm_page_created');
    }
});

register_deactivation_hook(__FILE__, function(){ flush_rewrite_rules(); });

// ==================================================
// LEAD → CLIENT CONVERSION
// ==================================================

// Add a "Convert to Client" link in Lead list
add_filter('post_row_actions', function($actions, $post){
    if ($post->post_type !== 'lead') return $actions;
    $url = wp_nonce_url(
        admin_url('admin-post.php?action=lm_convert_lead&lead_id='.$post->ID),
        'lm_convert_lead_'.$post->ID
    );
    $actions['lm_convert'] = '<a href="'.esc_url($url).'">Convert to Client</a>';
    return $actions;
}, 10, 2);

// Handle conversion securely
add_action('admin_post_lm_convert_lead', 'lm_handle_convert_lead');
function lm_handle_convert_lead() {

    if (!isset($_GET['lead_id'])) wp_die('Missing lead ID.');
    $lead_id = intval($_GET['lead_id']);

    // Permission check (Admins only)
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to convert this lead.');
    }

    // Nonce verification
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field( wp_unslash($_GET['_wpnonce']) ) : '';
    if ( ! $nonce || ! wp_verify_nonce($nonce, 'lm_convert_lead_' . $lead_id) ) {

        wp_die('Security check failed.');
    }

    $lead = get_post($lead_id);
    if (!$lead || $lead->post_type !== 'lead') wp_die('Invalid lead.');

    // Prevent duplicate conversion
    if ( get_post_meta( $lead_id, '_pp_converted', true ) ) {
        $nonce = wp_create_nonce( 'lm_converted_notice' );
        $redirect_url = add_query_arg(
            array(
                'post_type'    => 'lead',
                'lm_converted' => 'dup',
                '_lm_nonce'    => $nonce,
            ),
            admin_url( 'edit.php' )
        );
        wp_safe_redirect( $redirect_url );
        exit;
    }


    // Create Client post
    $client_id = wp_insert_post([
        'post_type'   => 'client',
        'post_status' => 'publish',
        'post_title'  => $lead->post_title,
        'post_content'=> $lead->post_content,
    ]);

    if (is_wp_error($client_id) || !$client_id) wp_die('Failed to create client.');

    // Copy meta data
    foreach (['_pp_email','_pp_phone'] as $key){
        $val = get_post_meta($lead_id, $key, true);
        if ($val) update_post_meta($client_id, $key, $val);
    }

    // Copy thumbnail if exists
    $thumb_id = get_post_thumbnail_id($lead_id);
    if ($thumb_id) set_post_thumbnail($client_id, $thumb_id);

    // Mark lead as converted
    update_post_meta($lead_id, '_pp_status', 'converted');
    update_post_meta($lead_id, '_pp_converted', $client_id);

    // Redirect back with notice
    $nonce = wp_create_nonce( 'lm_converted_notice' );
    $redirect_url = add_query_arg(
        array(
            'post_type'    => 'lead',
            'lm_converted' => $client_id,
            '_lm_nonce'    => $nonce,
        ),
        admin_url( 'edit.php' )
    );
    wp_safe_redirect( $redirect_url );
    exit;

}

// Admin notice after conversion (final WordPress.org-compliant version)
add_action('admin_notices', function() {

    // 1️ Copy and sanitize all GET data at once
    $get = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ( empty($get) || ! isset($get['lm_converted']) ) {
        return;
    }

    // 2️ Extract safe values
    $converted_value = sanitize_text_field( wp_unslash( $get['lm_converted'] ) );
    $nonce_value     = isset($get['_lm_nonce']) ? sanitize_text_field( wp_unslash( $get['_lm_nonce'] ) ) : '';

    // 3️ Verify nonce (only show message if legitimate redirect)
    if ( empty($nonce_value) || ! wp_verify_nonce( $nonce_value, 'lm_converted_notice' ) ) {
        return;
    }

    // 4️ Handle duplicate conversions
    if ( $converted_value === 'dup' ) {
        echo '<div class="notice notice-warning is-dismissible"><p>This lead was already converted.</p></div>';
        return;
    }

    // 5 Ensure numeric ID
    $id = intval( $converted_value );
    if ( $id <= 0 ) {
        return;
    }

    // 6️ Fetch and display
    $link = get_edit_post_link( $id );
    if ( ! $link ) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>Lead converted to Client successfully. <a href="' . esc_url( $link ) . '">Edit client #' . esc_html( $id ) . '</a></p>';
    echo '</div>';
});


// ==================================================
// ADMIN LIST COLUMNS FOR LEADS
// ==================================================

// 1️⃣ Add new columns to Leads list
add_filter('manage_edit-lead_columns', function($columns) {
    // Keep default columns, then add custom ones
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['lm_email']  = 'Email';
            $new['lm_phone']  = 'Phone';
            $new['lm_status'] = 'Status';
        }
    }
    return $new;
});

// 2️⃣ Fill the custom column data
add_action('manage_lead_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'lm_email':
            $val = get_post_meta($post_id, '_pp_email', true);
            echo esc_html($val ?: '—');
            break;
        case 'lm_phone':
            $val = get_post_meta($post_id, '_pp_phone', true);
            echo esc_html($val ?: '—');
            break;
        case 'lm_status':
            $status = get_post_meta($post_id, '_pp_status', true);
            if ($status === 'converted') {
                echo '<span style="color:green;font-weight:bold;">Converted</span>';
            } elseif ($status === 'new') {
                echo '<span style="color:#0073aa;">New</span>';
            } else {
                echo esc_html(ucfirst($status ?: 'Unknown'));
            }
            break;
    }
}, 10, 2);

// 3️⃣ Make columns sortable
add_filter('manage_edit-lead_sortable_columns', function($columns) {
    $columns['lm_status'] = 'lm_status';
    return $columns;
});

