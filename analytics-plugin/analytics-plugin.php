<?php
/*
Plugin Name:  Analytics Plugin
Description: A simple plugin that provides basic analytics data like page views and visitor statistics with a dashboard widget for a quick overview.
Version: 1.0
Author: Mauro Pereira
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to record page views. The function increments the view count each time a post or page is loaded by a non-admin user.
function sap_record_page_view() {
    if (is_admin()) {
        return; // Don't track admin pages
    }

    $post_id = get_the_ID();
    if ($post_id) {
        $views = get_post_meta($post_id, 'sap_page_views', true);
        $views = $views ? $views + 1 : 1;
        update_post_meta($post_id, 'sap_page_views', $views);
    }
}
add_action('wp_head', 'sap_record_page_view');

// Function to display the dashboard widget
function sap_add_dashboard_widget() {
    wp_add_dashboard_widget('sap_dashboard_widget', 'Analytics Overview', 'sap_dashboard_widget_display');
}
add_action('wp_dashboard_setup', 'sap_add_dashboard_widget');

// Displays the analytics data within this widget.
function sap_dashboard_widget_display() {
    global $wpdb;

    $total_views = $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'sap_page_views'");

    echo "<p><strong>Total Page Views:</strong> " . intval($total_views) . "</p>";

    $results = $wpdb->get_results("
        SELECT post_id, meta_value 
        FROM $wpdb->postmeta 
        WHERE meta_key = 'sap_page_views' 
        ORDER BY meta_value DESC 
        LIMIT 5
    ");

    if ($results) {
        echo "<p><strong>Top 5 Posts:</strong></p><ul>";
        foreach ($results as $result) {
            echo "<li><a href='" . get_permalink($result->post_id) . "'>" . get_the_title($result->post_id) . "</a> (" . intval($result->meta_value) . " views)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No data available.</p>";
    }
}
?>
