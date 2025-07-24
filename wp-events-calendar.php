<?php
/**
 * Plugin Name: WP Events Calendar
 * Description: A simple plugin to add and display upcoming events.
 * Version: 1.0
 * Author: Nayan Jain
 */

defined('ABSPATH') or die('No script kiddies please!');

// Include files
include_once plugin_dir_path(__FILE__) . 'admin/events-admin.php';
include_once plugin_dir_path(__FILE__) . 'public/display-events.php';

// Load CSS and JavaScript
function wec_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style('wec-style', plugin_dir_url(__FILE__) . 'css/events-style.css');
    
    // Enqueue jQuery UI datepicker if not already included
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    
    // Enqueue our custom JavaScript
    wp_enqueue_script('wec-script', plugin_dir_url(__FILE__) . 'js/events-script.js', array('jquery', 'jquery-ui-datepicker'), '1.0', true);
    
    // Pass AJAX URL and nonce to JavaScript
    wp_localize_script('wec-script', 'wec_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wec_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'wec_enqueue_scripts');

// Add AJAX handlers
function wec_add_event_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wec_ajax_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    // Parse form data
    parse_str($_POST['formData'], $formData);
    
    // Validate and sanitize data
    $title = sanitize_text_field($formData['event_title']);
    $date = sanitize_text_field($formData['event_date']);
    $desc = sanitize_textarea_field($formData['event_description']);
    
    if (!$title || !$date) {
        wp_send_json_error(array('message' => 'Please fill in all required fields'));
    }
    
    // Insert event
    global $wpdb;
    $table = $wpdb->prefix . 'wec_events';
    $result = $wpdb->insert($table, array(
        'title' => $title,
        'event_date' => $date,
        'description' => $desc
    ));
    
    if ($result) {
        wp_send_json_success(array('message' => 'Event added successfully'));
    } else {
        wp_send_json_error(array('message' => 'Error adding event'));
    }
}
add_action('wp_ajax_wec_add_event', 'wec_add_event_ajax');
add_action('wp_ajax_nopriv_wec_add_event', 'wec_add_event_ajax');

// AJAX handler for getting events
function wec_get_events_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'wec_ajax_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    ob_start();
    echo do_shortcode('[events_calendar]');
    $html = ob_get_clean();
    
    wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_wec_get_events', 'wec_get_events_ajax');
add_action('wp_ajax_nopriv_wec_get_events', 'wec_get_events_ajax');

// Create events table on activation
register_activation_hook(__FILE__, 'wec_create_event_table');
function wec_create_event_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'wec_events';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        event_date DATE NOT NULL,
        description TEXT,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
