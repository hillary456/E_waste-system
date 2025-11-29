<?php 
// No CORS headers here - they're handled by .htaccess
// Just include your normal files and handle the request

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
            "user_type" => $user->user_type
        ));
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login failed."));
    }
}
?>