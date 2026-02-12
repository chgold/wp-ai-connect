<?php
/**
 * Uninstall AI Connect
 * 
 * Security-sensitive data (JWT secrets, tokens) is always deleted.
 * Other data (OAuth clients, settings) is deleted only if user opted in via Settings.
 * 
 * @package AIConnect
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$delete_all = get_option('ai_connect_delete_on_uninstall', 0);

delete_option('ai_connect_jwt_secret');

$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE 'ai_connect_refresh_%'"
);

$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE 'ai_connect_auth_code_%'"
);

$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_ai_connect_%' 
        OR option_name LIKE '_transient_timeout_ai_connect_%'"
);

if ($delete_all) {
    
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE 'ai_connect_client_%'"
    );
    
    delete_option('ai_connect_rate_limit_per_minute');
    delete_option('ai_connect_rate_limit_per_hour');
    delete_option('ai_connect_delete_on_uninstall');
    delete_option('ai_connect_plan');
    delete_option('ai_connect_version');
    delete_option('ai_connect_installed');
    delete_option('ai_connect_welcome_notice');
    
    if (class_exists('Redis') && defined('REDIS_HOST')) {
        try {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, defined('REDIS_PORT') ? REDIS_PORT : 6379);
            
            if (defined('REDIS_PASSWORD')) {
                $redis->auth(REDIS_PASSWORD);
            }
            
            $keys = $redis->keys('ai_connect:*');
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    $redis->del($key);
                }
            }
            
            $redis->close();
        } catch (Exception $e) {
        }
    }
}

wp_cache_flush();
flush_rewrite_rules();

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log(sprintf(
        'AI Connect: Uninstalled. Full cleanup: %s',
        $delete_all ? 'YES' : 'NO (data preserved)'
    ));
}
