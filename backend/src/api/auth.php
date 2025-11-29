<?php 
ini_set('display_errors', 0);
error_reporting(E_ALL); 
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(array(
        "message" => "Server Error (PHP)", 
        "details" => "$errstr in $errfile on line $errline"
    ));
    exit();
});
 
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(array(
            "message" => "Critical Server Crash", 
            "details" => $error['message'] . " in " . $error['file'] . ":" . $error['line']
        ));
    }
});
 
include_once '../config/cors.php';
 
include_once '../config/database.php';
include_once '../models/User.php';
 
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
 
$data = json_decode(file_get_contents("php://input"));
 
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'register') {
    if(
        !empty($data->name) &&
        !empty($data->email) &&
        !empty($data->password) &&
        !empty($data->user_type)
    ) {
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
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
    }
} 
elseif ($action == 'login') {
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
?>