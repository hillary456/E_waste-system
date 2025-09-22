<?php
require_once '../config/cors.php';
require_once '../controllers/AuthController.php';

$authController = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $action = $data['action'] ?? '';

        switch($action) {
            case 'register':
                $result = $authController->register($data);
                break;
            
            case 'login':
                $result = $authController->login($data);
                break;
            
            default:
                $result = array("success" => false, "message" => "Invalid action");
                break;
        }
        break;

    case 'GET':
        // Get user profile (requires authentication)
        $auth = new Auth();
        $user_data = $auth->requireAuth();
        $result = $authController->getProfile($user_data['id']);
        break;

    case 'PUT':
        // Update user profile (requires authentication)
        $auth = new Auth();
        $user_data = $auth->requireAuth();
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $authController->updateProfile($user_data['id'], $data);
        break;

    default:
        $result = array("success" => false, "message" => "Method not allowed");
        http_response_code(405);
        break;
}

echo json_encode($result);
?>