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
include_once '../models/User.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
 
    $raw_input = file_get_contents("php://input");
     
    if (trim($raw_input) === "") {
        $method = $_SERVER['REQUEST_METHOD'];
        $content_type = $_SERVER['CONTENT_TYPE'] ?? 'Not Set';
         
        $hint = "";
        if ($method === 'GET') {
            $hint = " | HINT: The request became a GET. This usually means a 301/302 Redirect occurred. Check your URL for typos or trailing slashes.";
        } elseif ($method === 'OPTIONS') {
            $hint = " | HINT: This is a CORS Preflight check. It should have been handled by cors.php.";
        } elseif ($method === 'POST') {
            $hint = " | HINT: It is a POST but empty. Check if you are sending JSON correctly.";
        }

        throw new Exception("Request body is empty. Server received Method: [$method] with Content-Type: [$content_type]. $hint");
    } 

    $data = json_decode($raw_input);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON received: " . json_last_error_msg());
    }

    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action == 'register') {
        $missing_fields = [];
        if (empty($data->name)) $missing_fields[] = 'name';
        if (empty($data->email)) $missing_fields[] = 'email';
        if (empty($data->password)) $missing_fields[] = 'password';
        if (empty($data->user_type)) $missing_fields[] = 'user_type';

        if (count($missing_fields) > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete.", "missing_fields" => $missing_fields));
            exit();
        }

        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = password_hash($data->password, PASSWORD_BCRYPT);
        $user->user_type = $data->user_type;
        $user->organization = $data->organization ?? '';
        $user->phone = $data->phone ?? '';
        $user->location = $data->location ?? '';

        if($user->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "User was created."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create user. Email might already exist."));
        }
    } 
    elseif ($action == 'login') {
        if(empty($data->email) || empty($data->password)) {
             http_response_code(400);
             echo json_encode(array("message" => "Email and password are required."));
             exit();
        }
        $user->email = $data->email;
        $email_exists = $user->emailExists();

        if($email_exists && password_verify($data->password, $user->password)) {
            http_response_code(200);
            echo json_encode(array(
                "message" => "Successful login.",
                "user_id" => $user->id,
                "name" => $user->name,
                "user_type" => $user->user_type,
                "organization" => $user->organization,
                "location" => $user->location
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed. Check your email or password."));
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "System Error", "details" => $e->getMessage()));
}
?>