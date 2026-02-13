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

$ai_connect_delete_all = get_option('ai_connect_delete_on_uninstall', 0);

delete_option('ai_connect_jwt_secret');

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE 'ai_connect_refresh_%'"
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE 'ai_connect_auth_code_%'"
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_ai_connect_%' 
        OR option_name LIKE '_transient_timeout_ai_connect_%'"
);

if ($ai_connect_delete_all) {
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
            $ai_connect_redis = new Redis();
            $ai_connect_redis->connect(REDIS_HOST, defined('REDIS_PORT') ? REDIS_PORT : 6379);
            
            if (defined('REDIS_PASSWORD')) {
                $ai_connect_redis->auth(REDIS_PASSWORD);
            }
            
            $ai_connect_keys = $ai_connect_redis->keys('ai_connect:*');
            if (!empty($ai_connect_keys)) {
                foreach ($ai_connect_keys as $ai_connect_key) {
                    $ai_connect_redis->del($ai_connect_key);
                }
            }
            
            $ai_connect_redis->close();
        } catch (Exception $e) {
            // Silently fail - Redis cleanup is not critical
        }
    }
}

wp_cache_flush();
flush_rewrite_rules();

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log(sprintf( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        'AI Connect: Uninstalled. Full cleanup: %s',
        $ai_connect_delete_all ? 'YES' : 'NO (data preserved)'
    ));
}
