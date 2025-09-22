<?php
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $secret_key = "your-secret-key-here";
    private $issuer = "cfs-kenya";
    private $audience = "cfs-kenya-users";
    private $issuedAt;
    private $expire;

    public function __construct() {
        $this->issuedAt = time();
        $this->expire = $this->issuedAt + (24 * 60 * 60); // 24 hours
    }

    // Generate JWT token
    public function generateToken($user_data) {
        $token = array(
            "iss" => $this->issuer,
            "aud" => $this->audience,
            "iat" => $this->issuedAt,
            "exp" => $this->expire,
            "data" => array(
                "id" => $user_data['id'],
                "name" => $user_data['name'],
                "email" => $user_data['email'],
                "user_type" => $user_data['user_type']
            )
        );

        return JWT::encode($token, $this->secret_key, 'HS256');
    }

    // Validate JWT token
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            return (array) $decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get token from header
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    // Get authorization header
    private function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }

    // Middleware to check authentication
    public function requireAuth() {
        $token = $this->getBearerToken();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(array("message" => "Access denied. No token provided."));
            exit();
        }

        $user_data = $this->validateToken($token);
        
        if (!$user_data) {
            http_response_code(401);
            echo json_encode(array("message" => "Access denied. Invalid token."));
            exit();
        }

        return $user_data;
    }

    // Check if user is admin
    public function requireAdmin() {
        $user_data = $this->requireAuth();
        
        if ($user_data['user_type'] !== 'admin') {
            http_response_code(403);
            echo json_encode(array("message" => "Access denied. Admin privileges required."));
            exit();
        }

        return $user_data;
    }
}
?>