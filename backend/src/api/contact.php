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
        if(empty($data->name) || empty($data->email) || empty($data->message)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required fields: name, email, or message."));
            exit;
        }
         
        $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)";
        $stmt = $db->prepare($query);
         
        $name = htmlspecialchars(strip_tags($data->name));
        $stmt->bindParam(":name", $name);
        
        $email = htmlspecialchars(strip_tags($data->email));
        $stmt->bindParam(":email", $email);
        
        $subject = htmlspecialchars(strip_tags($data->subject ?? 'General Inquiry'));
        $stmt->bindParam(":subject", $subject);
        
        $message = htmlspecialchars(strip_tags($data->message));
        $stmt->bindParam(":message", $message);
        
        if($stmt->execute()) {
            http_response_code(200);
            echo json_encode(array("message" => "Message sent successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to send message. Database error."));
        }
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