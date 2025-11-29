<?php
require_once '../config/cors.php';
require_once '../controller/SchoolRequestController.php';
require_once '../middleware/auth.php';

$schoolRequestController = new SchoolRequestController();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        // Create school request (requires authentication)
        $auth = new Auth();
        $user_data = $auth->requireAuth();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $schoolRequestController->create($data, $user_data['id']);
        break;

    case 'GET':
        $auth = new Auth();
        
        if (isset($_GET['all']) && $_GET['all'] === 'true') {
            // Get all requests (admin only)
            $auth->requireAdmin();
            $result = $schoolRequestController->getAll();
        } else if (isset($_GET['stats']) && $_GET['stats'] === 'true') {
            // Get request statistics (admin only)
            $auth->requireAdmin();
            $result = $schoolRequestController->getStats();
        } else {
            // Get user's requests
            $user_data = $auth->requireAuth();
            $result = $schoolRequestController->getByUser($user_data['id']);
        }
        break;

    case 'PUT':
        // Update request status (admin only)
        $auth = new Auth();
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $request_id = $data['id'] ?? 0;
        $status = $data['status'] ?? '';
        
        $result = $schoolRequestController->updateStatus($request_id, $status);
        break;

    default:
        $result = array("success" => false, "message" => "Method not allowed");
        http_response_code(405);
        break;
}

echo json_encode($result);
?>