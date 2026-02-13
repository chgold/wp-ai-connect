<?php
/**
 * Plugin Name: AI Connect
 * Plugin URI: https://github.com/chgold/ai-connect
 * Description: Bridge WordPress & AI Agents with WebMCP Protocol
 * Version: 0.1.0
 * Author: chgold
 * Author URI: https://github.com/chgold
 * License: GPL v3
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: ai-connect
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AI_CONNECT_VERSION', '0.1.0');
define('AI_CONNECT_PATH', plugin_dir_path(__FILE__));
define('AI_CONNECT_URL', plugin_dir_url(__FILE__));

// Load Composer dependencies
if (file_exists(AI_CONNECT_PATH . 'vendor/autoload.php')) {
    require_once AI_CONNECT_PATH . 'vendor/autoload.php';
}

register_activation_hook(__FILE__, 'ai_connect_activate');
function ai_connect_activate() {
    add_option('ai_connect_version', AI_CONNECT_VERSION);
    add_option('ai_connect_installed', time());
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'ai_connect_deactivate');
function ai_connect_deactivate() {
    flush_rewrite_rules();
}

add_action('plugins_loaded', 'ai_connect_init');
function ai_connect_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('AI Connect: WooCommerce is recommended for full functionality.', 'ai-connect');
            echo '</p></div>';
        });
    }
    
    require_once AI_CONNECT_PATH . 'includes/class-ai-connect.php';
    $plugin = new AI_Connect();
    $plugin->run();
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ai_connect_action_links');
function ai_connect_action_links($links) {
    $settings_link = '<a href="admin.php?page=ai-connect">' . esc_html__('Settings', 'ai-connect') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
