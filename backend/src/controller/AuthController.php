<?php
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../middleware/auth.php';

class AuthController {
    private $db;
    private $user;
    private $auth;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->auth = new Auth();
    }

    // Register new user
    public function register($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['user_type'])) {
            return array(
                "success" => false,
                "message" => "All required fields must be filled."
            );
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return array(
                "success" => false,
                "message" => "Invalid email format."
            );
        }

        // Check if email already exists
        $this->user->email = $data['email'];
        if ($this->user->emailExists()) {
            return array(
                "success" => false,
                "message" => "Email already exists."
            );
        }

        // Set user properties
        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->user_type = $data['user_type'];
        $this->user->organization = $data['organization'] ?? '';
        $this->user->phone = $data['phone'] ?? '';
        $this->user->location = $data['location'] ?? '';

        // Create user
        if ($this->user->create()) {
            // Generate token
            $user_data = array(
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
                "user_type" => $this->user->user_type
            );

            $token = $this->auth->generateToken($user_data);

            return array(
                "success" => true,
                "message" => "User registered successfully.",
                "token" => $token,
                "user" => $user_data
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to register user."
        );
    }

    // Login user
    public function login($data) {
        // Validate required fields
        if (empty($data['email']) || empty($data['password'])) {
            return array(
                "success" => false,
                "message" => "Email and password are required."
            );
        }

        // Check if user exists
        $this->user->email = $data['email'];
        if (!$this->user->readByEmail()) {
            return array(
                "success" => false,
                "message" => "Invalid email or password."
            );
        }

        // Verify password
        if (!password_verify($data['password'], $this->user->password)) {
            return array(
                "success" => false,
                "message" => "Invalid email or password."
            );
        }

        // Generate token
        $user_data = array(
            "id" => $this->user->id,
            "name" => $this->user->name,
            "email" => $this->user->email,
            "user_type" => $this->user->user_type
        );

        $token = $this->auth->generateToken($user_data);

        return array(
            "success" => true,
            "message" => "Login successful.",
            "token" => $token,
            "user" => $user_data
        );
    }

    // Get user profile
    public function getProfile($user_id) {
        $this->user->id = $user_id;
        if ($this->user->readById()) {
            return array(
                "success" => true,
                "user" => array(
                    "id" => $this->user->id,
                    "name" => $this->user->name,
                    "email" => $this->user->email,
                    "user_type" => $this->user->user_type,
                    "organization" => $this->user->organization,
                    "phone" => $this->user->phone,
                    "location" => $this->user->location,
                    "created_at" => $this->user->created_at
                )
            );
        }

        return array(
            "success" => false,
            "message" => "User not found."
        );
    }

    // Update user profile
    public function updateProfile($user_id, $data) {
        $this->user->id = $user_id;
        
        if (!$this->user->readById()) {
            return array(
                "success" => false,
                "message" => "User not found."
            );
        }

        // Update user properties
        $this->user->name = $data['name'] ?? $this->user->name;
        $this->user->organization = $data['organization'] ?? $this->user->organization;
        $this->user->phone = $data['phone'] ?? $this->user->phone;
        $this->user->location = $data['location'] ?? $this->user->location;

        if ($this->user->update()) {
            return array(
                "success" => true,
                "message" => "Profile updated successfully."
            );
        }

        return array(
            "success" => false,
            "message" => "Unable to update profile."
        );
    }
}
?>