<?php 
if (ob_get_level()) ob_end_clean();
 
header_remove('Access-Control-Allow-Origin');
header_remove('Access-Control-Allow-Credentials');
header_remove('Access-Control-Max-Age');
header_remove('Access-Control-Allow-Methods');
header_remove('Access-Control-Allow-Headers');
 
$allowed_origins = [
    'http://127.0.0.1:5500',
    'http://localhost:5500',
    'http://localhost:3000'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else { 
        header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");
    header("Content-Type: application/json; charset=UTF-8");
     
    http_response_code(200);
    exit(0);
} 
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");
?>