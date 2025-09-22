<?php
require_once '../config/cors.php';
require_once '../controllers/ContactController.php';
require_once '../middleware/auth.php';

$contactController = new ContactController();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $contactController->create($data);
        break;

    case 'GET':
        // Get all messages (admin only)
        $auth = new Auth();
        $auth->requireAdmin();
        $result = $contactController->getAll();
        break;

    case 'PUT':
        // Update message status (admin only)
        $auth = new Auth();
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $message_id = $data['id'] ?? 0;
        $status = $data['status'] ?? '';
        
        $result = $contactController->updateStatus($message_id, $status);
        break;

    default:
        $result = array("success" => false, "message" => "Method not allowed");
        http_response_code(405);
        break;
}

echo json_encode($result);
?>