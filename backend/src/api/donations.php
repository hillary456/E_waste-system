<?php 
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(array("message" => "Server Error (PHP)", "details" => "$errstr in $errfile on line $errline"));
    exit();
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(array("message" => "Critical Server Crash", "details" => $error['message']));
    }
});
 
include_once '../config/cors.php';
include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
 
    $raw_input = file_get_contents("php://input");
     
    if (trim($raw_input) === "") {
        $method = $_SERVER['REQUEST_METHOD'];
        $hint = "";
        if ($method === 'GET') $hint = " | HINT: Request became GET. Check for redirects/trailing slashes.";
        elseif ($method === 'OPTIONS') $hint = " | HINT: CORS Preflight not handled.";
        
        throw new Exception("Request body is empty. Method: [$method]. $hint");
    }

    $data = json_decode($raw_input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'POST') { 
        if(empty($data->donor_name) || empty($data->quantity) || empty($data->computer_type)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required fields. Please fill all starred fields."));
            exit;
        }
         
        $query = "INSERT INTO donations 
                 (user_id, donor_name, organization, email, phone, address, computer_type, quantity, condition_status, pickup_date, message, status) 
                 VALUES (:uid, :name, :org, :email, :phone, :addr, :type, :qty, :cond, :date, :msg, 'pending')";
        
        $stmt = $db->prepare($query);
         
        $userId = isset($data->user_id) && !empty($data->user_id) ? $data->user_id : null;
         
        $stmt->bindParam(":uid", $userId);
        $name = htmlspecialchars(strip_tags($data->donor_name));
        $stmt->bindParam(":name", $name);
        $org = htmlspecialchars(strip_tags($data->organization ?? ''));
        $stmt->bindParam(":org", $org);
        $email = htmlspecialchars(strip_tags($data->email));
        $stmt->bindParam(":email", $email);
        $phone = htmlspecialchars(strip_tags($data->phone));
        $stmt->bindParam(":phone", $phone);
        $addr = htmlspecialchars(strip_tags($data->address));
        $stmt->bindParam(":addr", $addr);
        $type = htmlspecialchars(strip_tags($data->computer_type));
        $stmt->bindParam(":type", $type);
        $qty = htmlspecialchars(strip_tags($data->quantity));
        $stmt->bindParam(":qty", $qty);
        $cond = htmlspecialchars(strip_tags($data->condition_status));
        $stmt->bindParam(":cond", $cond);
         
        $date = !empty($data->pickup_date) ? htmlspecialchars(strip_tags($data->pickup_date)) : null;
        $stmt->bindParam(":date", $date);
        
        $msg = htmlspecialchars(strip_tags($data->message ?? ''));
        $stmt->bindParam(":msg", $msg);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Donation submitted successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to submit donation. Database error."));
        }
    } elseif ($method == 'GET') { 
        $query = "SELECT * FROM donations ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $donations_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($donations_arr, $row);
        }
        http_response_code(200);
        echo json_encode($donations_arr);
    } else {
        http_response_code(405);
        echo json_encode(array("message" => "Method Not Allowed"));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "System Error",
        "details" => $e->getMessage()
    ));
}
?>