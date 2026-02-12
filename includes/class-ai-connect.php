<?php
if (!defined('ABSPATH')) {
    exit;
}

class AI_Connect {
    
    private $version = '0.1.0';
    
    private $manifest;
    private $tools_endpoint;
    private $modules = [];
    
    public function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->register_modules();
    }
    
    private function load_dependencies() {
        if (file_exists(AI_CONNECT_PATH . 'vendor/autoload.php')) {
            require_once AI_CONNECT_PATH . 'vendor/autoload.php';
        }
        
        require_once AI_CONNECT_PATH . 'includes/core/class-manifest.php';
        require_once AI_CONNECT_PATH . 'includes/core/class-auth.php';
        require_once AI_CONNECT_PATH . 'includes/core/class-rate-limiter.php';
        require_once AI_CONNECT_PATH . 'includes/modules/class-module-base.php';
        require_once AI_CONNECT_PATH . 'includes/modules/class-core-module.php';
        require_once AI_CONNECT_PATH . 'includes/api/class-tools-endpoint.php';
    }
    
    private function init_components() {
        $this->manifest = new \AIConnect\Core\Manifest();
        $this->tools_endpoint = new \AIConnect\API\Tools_Endpoint();
    }
    
    private function register_modules() {
        // Register WordPress Core module (Free)
        $core_module = new \AIConnect\Modules\Core_Module($this->manifest);
        $this->modules['wordpress'] = $core_module;
        $this->tools_endpoint->register_module($core_module);
        
        // Allow external plugins (Pro) to register additional modules
        // Pro plugin hooks here via: add_action('ai_connect_register_modules', ...)
        do_action('ai_connect_register_modules', $this);
    }
    
    /**
     * Register external module (used by Pro plugin)
     * 
     * @param string $key Module key
     * @param object $module Module instance
     */
    public function register_external_module($key, $module) {
        $this->modules[$key] = $module;
        $this->tools_endpoint->register_module($module);
    }
    
    /**
     * Get manifest instance (used by Pro plugin)
     */
    public function get_manifest_instance() {
        return $this->manifest;
    }
    
    /**
     * Get tools endpoint instance (used by Pro plugin)
     */
    public function get_tools_endpoint() {
        return $this->tools_endpoint;
    }
    
    public function run() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('AI Connect', 'ai-connect'),
            __('AI Connect', 'ai-connect'),
            'manage_options',
            'ai-connect',
            [$this, 'admin_page'],
            'dashicons-admin-plugins',
            100
        );
        
        add_submenu_page(
            'ai-connect',
            __('Settings', 'ai-connect'),
            __('Settings', 'ai-connect'),
            'manage_options',
            'ai-connect-settings',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            'ai-connect',
            __('OAuth Clients', 'ai-connect'),
            __('OAuth Clients', 'ai-connect'),
            'manage_options',
            'ai-connect-clients',
            [$this, 'clients_page']
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🚀 <?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-success inline">
                <p><strong><?php printf(__('✅ AI Connect v%s is active and ready!', 'ai-connect'), $this->version); ?></strong></p>
            </div>
            
            <div class="card" style="max-width: 800px;">
                <h2><?php _e('Environment Status', 'ai-connect'); ?></h2>
                <table class="widefat striped">
                    <tbody>
                        <tr>
                            <td style="width: 200px;"><strong>WordPress:</strong></td>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP:</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>MySQL:</strong></td>
                            <td><?php 
                                global $wpdb;
                                echo $wpdb->db_version() ? '✓ Connected (Port 3307)' : '✗ Not connected';
                            ?></td>
                        </tr>
                        <tr>
                            <td><strong>WooCommerce:</strong></td>
                            <td><?php 
                                echo class_exists('WooCommerce') ? '✓ Active (v' . WC()->version . ')' : '✗ Not installed';
                            ?></td>
                        </tr>
                        <tr>
                            <td><strong>Redis:</strong></td>
                            <td><?php echo extension_loaded('redis') ? '✓ Available' : '○ Not installed (optional)'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Composer:</strong></td>
                            <td><?php echo file_exists(AI_CONNECT_PATH . 'vendor') ? '✓ Installed' : '○ Run composer install'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php if (!file_exists(AI_CONNECT_PATH . 'vendor/autoload.php')): ?>
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2><?php _e('⚠️ Action Required', 'ai-connect'); ?></h2>
                <p><?php _e('Composer dependencies are not installed. Please run:', 'ai-connect'); ?></p>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;"><code>cd <?php echo esc_html(dirname(AI_CONNECT_PATH)); ?> && composer install</code></pre>
            </div>
            <?php else: ?>
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2><?php _e('🎯 Next Steps', 'ai-connect'); ?></h2>
                <ol>
                    <li><strong><?php _e('Create an OAuth Client', 'ai-connect'); ?></strong> - <?php _e('Go to AI Connect → OAuth Clients', 'ai-connect'); ?></li>
                    <li><strong><?php _e('Configure Rate Limits', 'ai-connect'); ?></strong> - <?php _e('Go to AI Connect → Settings', 'ai-connect'); ?></li>
                    <li><strong><?php _e('Test the API', 'ai-connect'); ?></strong> - <?php _e('Visit', 'ai-connect'); ?> <code><?php echo rest_url('ai-connect/v1/manifest'); ?></code></li>
                    <li><strong><?php _e('Read the Docs', 'ai-connect'); ?></strong> - <a href="https://github.com/chgold/ai-connect#readme" target="_blank"><?php _e('GitHub README', 'ai-connect'); ?></a></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function register_routes() {
        register_rest_route('ai-connect/v1', '/status', [
            'methods' => 'GET',
            'callback' => [$this, 'get_status'],
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('ai-connect/v1', '/manifest', [
            'methods' => 'GET',
            'callback' => [$this, 'get_manifest'],
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('.well-known', '/ai-plugin.json', [
            'methods' => 'GET',
            'callback' => [$this, 'get_manifest'],
            'permission_callback' => '__return_true'
        ]);
        
        $this->tools_endpoint->register_routes();
    }
    
    public function get_manifest() {
        $manifest = $this->manifest->generate();
        
        return rest_ensure_response($manifest);
    }
    
    public function get_status() {
        return rest_ensure_response([
            'status' => 'ok',
            'version' => $this->version,
            'wordpress' => get_bloginfo('version'),
            'php' => PHP_VERSION
        ]);
    }
    
    public function settings_page() {
        if (isset($_POST['ai_connect_save_settings'])) {
            check_admin_referer('ai_connect_settings');
            
            $rate_limit_per_minute = absint($_POST['rate_limit_per_minute'] ?? 50);
            $rate_limit_per_hour = absint($_POST['rate_limit_per_hour'] ?? 1000);
            $delete_on_uninstall = isset($_POST['delete_on_uninstall']) ? 1 : 0;
            
            update_option('ai_connect_rate_limit_per_minute', $rate_limit_per_minute);
            update_option('ai_connect_rate_limit_per_hour', $rate_limit_per_hour);
            update_option('ai_connect_delete_on_uninstall', $delete_on_uninstall);
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'ai-connect') . '</p></div>';
        }
        
        $rate_limit_per_minute = get_option('ai_connect_rate_limit_per_minute', 50);
        $rate_limit_per_hour = get_option('ai_connect_rate_limit_per_hour', 1000);
        $delete_on_uninstall = get_option('ai_connect_delete_on_uninstall', 0);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field('ai_connect_settings'); ?>
                
                <h2><?php _e('API Rate Limiting', 'ai-connect'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Rate Limit (per minute)', 'ai-connect'); ?></th>
                        <td>
                            <input type="number" name="rate_limit_per_minute" value="<?php echo esc_attr($rate_limit_per_minute); ?>" class="regular-text">
                            <p class="description"><?php _e('Maximum API requests per minute per user', 'ai-connect'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Rate Limit (per hour)', 'ai-connect'); ?></th>
                        <td>
                            <input type="number" name="rate_limit_per_hour" value="<?php echo esc_attr($rate_limit_per_hour); ?>" class="regular-text">
                            <p class="description"><?php _e('Maximum API requests per hour per user', 'ai-connect'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Data Management', 'ai-connect'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Uninstall Cleanup', 'ai-connect'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="delete_on_uninstall" value="1" <?php checked($delete_on_uninstall, 1); ?>>
                                    <?php _e('Delete all plugin data when uninstalling', 'ai-connect'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('When enabled, all OAuth clients, tokens, and settings will be permanently deleted when you uninstall this plugin. Leave unchecked to preserve data for reinstallation.', 'ai-connect'); ?>
                                    <br>
                                    <strong><?php _e('Note:', 'ai-connect'); ?></strong> <?php _e('Sensitive security data (JWT secrets, refresh tokens) will always be deleted regardless of this setting.', 'ai-connect'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'ai-connect'), 'primary', 'ai_connect_save_settings'); ?>
            </form>
        </div>
        <?php
    }
    
    public function clients_page() {
        if (isset($_POST['create_client'])) {
            check_admin_referer('ai_connect_create_client');
            
            $name = sanitize_text_field($_POST['client_name']);
            $redirect_uri = esc_url_raw($_POST['redirect_uri']);
            
            $auth = new \AIConnect\Core\Auth();
            $client = $auth->register_client($name, $redirect_uri);
            
            echo '<div class="notice notice-success"><p>' . __('OAuth client created successfully!', 'ai-connect') . '</p>';
            echo '<p><strong>Client ID:</strong> <code>' . esc_html($client['client_id']) . '</code></p>';
            echo '<p><strong>Client Secret:</strong> <code>' . esc_html($client['client_secret']) . '</code></p>';
            echo '<p class="description">' . __('Save the client secret now - it will not be shown again!', 'ai-connect') . '</p>';
            echo '</div>';
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="card" style="max-width: 600px; margin-top: 20px;">
                <h2><?php _e('Create New OAuth Client', 'ai-connect'); ?></h2>
                
                <form method="post">
                    <?php wp_nonce_field('ai_connect_create_client'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Client Name', 'ai-connect'); ?></th>
                            <td>
                                <input type="text" name="client_name" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Redirect URI', 'ai-connect'); ?></th>
                            <td>
                                <input type="url" name="redirect_uri" class="regular-text" required>
                                <p class="description"><?php _e('The URL where users will be redirected after authorization', 'ai-connect'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Create Client', 'ai-connect'), 'primary', 'create_client'); ?>
                </form>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2><?php _e('Authorization Endpoints', 'ai-connect'); ?></h2>
                <table class="widefat striped">
                    <tr>
                        <th>Authorization URL:</th>
                        <td><code><?php echo rest_url('ai-connect/v1/oauth/authorize'); ?></code></td>
                    </tr>
                    <tr>
                        <th>Token URL:</th>
                        <td><code><?php echo rest_url('ai-connect/v1/oauth/token'); ?></code></td>
                    </tr>
                    <tr>
                        <th>Refresh URL:</th>
                        <td><code><?php echo rest_url('ai-connect/v1/oauth/refresh'); ?></code></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}
