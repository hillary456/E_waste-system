<?php
require_once '../config/cors.php';
require_once '../controller/DonationController.php';
require_once '../middleware/auth.php';

$donationController = new DonationController();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Check if user is authenticated (optional for donations)
        $auth = new Auth();
        $token = $auth->getBearerToken();
        $user_id = null;
        
        if ($token) {
            $user_data = $auth->validateToken($token);
            if ($user_data) {
                $user_id = $user_data['id'];
            }
        }
        
        $result = $donationController->create($data, $user_id);
        break;

    case 'GET':
        $auth = new Auth();
        
        if (isset($_GET['all']) && $_GET['all'] === 'true') {
            // Get all donations (admin only)
            $auth->requireAdmin();
            $result = $donationController->getAll();
        } else if (isset($_GET['stats']) && $_GET['stats'] === 'true') {
            // Get donation statistics (admin only)
            $auth->requireAdmin();
            $result = $donationController->getStats();
        } else {
            // Get user's donations
            $user_data = $auth->requireAuth();
            $result = $donationController->getByUser($user_data['id']);
        }
        break;

    case 'PUT':
        // Update donation status (admin only)
        $auth = new Auth();
        $auth->requireAdmin();
        
        $data = json_decode(file_get_contents("php://input"), true);
        $donation_id = $data['id'] ?? 0;
        $status = $data['status'] ?? '';
        
        $result = $donationController->updateStatus($donation_id, $status);
        break;

    default:
        $result = array("success" => false, "message" => "Method not allowed");
        http_response_code(405);
        break;
}

echo json_encode($result);
?>