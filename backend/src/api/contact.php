<?php  
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(array("message" => "Server Error (PHP)", "details" => "$errstr in $errfile on line $errline"));
    exit();
});
 
include_once '../config/cors.php';
include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
 
    $raw_input = file_get_contents("php://input");
    if (trim($raw_input) === "") throw new Exception("Empty Request Body");
    $data = json_decode($raw_input);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'POST') { 
        if(empty($data->name) || empty($data->email) || empty($data->message)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required fields."));
            exit;
        }
 
        $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)";
        $stmt = $db->prepare($query);
        
        $name = htmlspecialchars(strip_tags($data->name));
        $email = htmlspecialchars(strip_tags($data->email));
        $subject_raw = htmlspecialchars(strip_tags($data->subject ?? 'General Inquiry'));
        $message = htmlspecialchars(strip_tags($data->message));
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":subject", $subject_raw);
        $stmt->bindParam(":message", $message);
        
        if($stmt->execute()) { 
            
            $to = "cfs.kenya.ke@gmail.com"; 
            
            $email_subject = "New Contact: " . $subject_raw;
            $email_body = "You received a new message from your website.\n\n";
            $email_body .= "Name: " . $name . "\n";
            $email_body .= "Email: " . $email . "\n";
            $email_body .= "Message:\n" . $message;
            
            $headers = "From: " . $email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
 
            $sent = @mail($to, $email_subject, $email_body, $headers);

            if($sent) {
                http_response_code(200);
                echo json_encode(array("message" => "Message sent to email successfully!"));
            } else {
                 
                http_response_code(200);
                echo json_encode(array("message" => "Message saved to DB, but Email failed (Check Server Config)."));
            }
            
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Database error."));
        }
    } else {
        http_response_code(405);
        echo json_encode(array("message" => "Method Not Allowed"));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "System Error", "details" => $e->getMessage()));
}
?>