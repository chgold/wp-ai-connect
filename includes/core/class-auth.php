<?php
namespace AIConnect\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!defined('ABSPATH')) {
    exit;
}

class Auth {
    
    private $jwt_secret;
    private $jwt_algorithm = 'HS256';
    private $token_expiry = 3600;
    private $refresh_token_expiry = 604800;
    
    public function __construct() {
        $this->jwt_secret = $this->get_jwt_secret();
        $this->token_expiry = \apply_filters('ai_connect_token_expiry', 3600);
        $this->refresh_token_expiry = \apply_filters('ai_connect_refresh_token_expiry', 604800);
    }
    
    private function get_jwt_secret() {
        $secret = \get_option('ai_connect_jwt_secret');
        
        if (empty($secret)) {
            $secret = \wp_generate_password(64, true, true);
            \update_option('ai_connect_jwt_secret', $secret);
        }
        
        return $secret;
    }
    
    public function generate_authorization_code($client_id, $user_id, $scopes = []) {
        $code = \wp_generate_password(32, false);
        
        $data = [
            'client_id' => $client_id,
            'user_id' => $user_id,
            'scopes' => $scopes,
            'expires' => time() + 600,
            'used' => false,
        ];
        
        \set_transient('ai_connect_auth_code_' . $code, $data, 600);
        
        return $code;
    }
    
    public function validate_authorization_code($code, $client_id) {
        $data = \get_transient('ai_connect_auth_code_' . $code);
        
        if (!$data) {
            return new \WP_Error('invalid_code', 'Authorization code is invalid or expired');
        }
        
        if ($data['used']) {
            \delete_transient('ai_connect_auth_code_' . $code);
            return new \WP_Error('code_used', 'Authorization code has already been used');
        }
        
        if ($data['client_id'] !== $client_id) {
            return new \WP_Error('client_mismatch', 'Client ID does not match');
        }
        
        if ($data['expires'] < time()) {
            \delete_transient('ai_connect_auth_code_' . $code);
            return new \WP_Error('code_expired', 'Authorization code has expired');
        }
        
        $data['used'] = true;
        \set_transient('ai_connect_auth_code_' . $code, $data, 60);
        
        return $data;
    }
    
    public function generate_access_token($user_id, $client_id, $scopes = []) {
        $issued_at = time();
        $expiration = $issued_at + $this->token_expiry;
        
        $payload = [
            'iss' => \site_url(),
            'iat' => $issued_at,
            'exp' => $expiration,
            'sub' => $user_id,
            'client_id' => $client_id,
            'scopes' => $scopes,
        ];
        
        return JWT::encode($payload, $this->jwt_secret, $this->jwt_algorithm);
    }
    
    public function generate_refresh_token($user_id, $client_id, $scopes = []) {
        $token = \wp_generate_password(64, false);
        
        $data = [
            'user_id' => $user_id,
            'client_id' => $client_id,
            'scopes' => $scopes,
            'created' => time(),
            'expires' => time() + $this->refresh_token_expiry,
        ];
        
        \set_transient('ai_connect_refresh_' . $token, $data, $this->refresh_token_expiry);
        
        return $token;
    }
    
    public function validate_access_token($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, $this->jwt_algorithm));
            
            if ($decoded->exp < time()) {
                return new \WP_Error('token_expired', 'Access token has expired');
            }
            
            return [
                'user_id' => $decoded->sub,
                'client_id' => $decoded->client_id,
                'scopes' => $decoded->scopes,
                'issued_at' => $decoded->iat,
                'expires_at' => $decoded->exp,
            ];
        } catch (\Exception $e) {
            return new \WP_Error('invalid_token', 'Invalid access token: ' . $e->getMessage());
        }
    }
    
    public function refresh_access_token($refresh_token, $client_id) {
        $data = \get_transient('ai_connect_refresh_' . $refresh_token);
        
        if (!$data) {
            return new \WP_Error('invalid_refresh_token', 'Refresh token is invalid or expired');
        }
        
        if ($data['client_id'] !== $client_id) {
            return new \WP_Error('client_mismatch', 'Client ID does not match');
        }
        
        if ($data['expires'] < time()) {
            \delete_transient('ai_connect_refresh_' . $refresh_token);
            return new \WP_Error('refresh_token_expired', 'Refresh token has expired');
        }
        
        $access_token = $this->generate_access_token(
            $data['user_id'],
            $data['client_id'],
            $data['scopes']
        );
        
        return [
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $this->token_expiry,
        ];
    }
    
    public function register_client($name, $redirect_uri, $user_id = null) {
        $client_id = 'client_' . \wp_generate_password(32, false);
        $client_secret = \wp_generate_password(64, true, true);
        
        $client_data = [
            'client_id' => $client_id,
            'client_secret' => \wp_hash_password($client_secret),
            'name' => $name,
            'redirect_uri' => $redirect_uri,
            'user_id' => $user_id ?: \get_current_user_id(),
            'created' => time(),
        ];
        
        \update_option('ai_connect_client_' . $client_id, $client_data);
        
        return [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'name' => $name,
            'redirect_uri' => $redirect_uri,
        ];
    }
    
    public function validate_client($client_id, $client_secret = null) {
        $client_data = \get_option('ai_connect_client_' . $client_id);
        
        if (!$client_data) {
            return new \WP_Error('invalid_client', 'Client ID not found');
        }
        
        if ($client_secret !== null && !\wp_check_password($client_secret, $client_data['client_secret'])) {
            return new \WP_Error('invalid_client_secret', 'Client secret is invalid');
        }
        
        return $client_data;
    }
    
    public function validate_redirect_uri($client_id, $redirect_uri) {
        $client_data = $this->validate_client($client_id);
        
        if (\is_wp_error($client_data)) {
            return $client_data;
        }
        
        if ($client_data['redirect_uri'] !== $redirect_uri) {
            return new \WP_Error('invalid_redirect_uri', 'Redirect URI does not match');
        }
        
        return true;
    }
    
    public function get_token_from_request() {
        $auth_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        
        if (empty($auth_header) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        }
        
        if (empty($auth_header)) {
            return null;
        }
        
        if (strpos($auth_header, 'Bearer ') === 0) {
            return substr($auth_header, 7);
        }
        
        return null;
    }
    
    public function check_scope($token_data, $required_scope) {
        if (\is_wp_error($token_data)) {
            return false;
        }
        
        $scopes = isset($token_data['scopes']) ? $token_data['scopes'] : [];
        
        if (in_array('admin', $scopes)) {
            return true;
        }
        
        return in_array($required_scope, $scopes);
    }
    
    public function revoke_token($token) {
        \delete_transient('ai_connect_refresh_' . $token);
        return true;
    }
    
    public function revoke_client($client_id) {
        return \delete_option('ai_connect_client_' . $client_id);
    }
}
