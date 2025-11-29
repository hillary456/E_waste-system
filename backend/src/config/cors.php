<?php  
// Handle preflight requests FIRST
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Set CORS headers for preflight
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $allowed_origins = [
            'http://127.0.0.1:5500',
            'http://localhost:5500',
            'http://localhost:3000'
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'];
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
        }
    } else {
        header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");
    http_response_code(200);
    exit(0);
}

// Regular request CORS headers
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowed_origins = [
        'http://127.0.0.1:5500',
        'http://localhost:5500', 
        'http://localhost:3000'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
    }
    header('Access-Control-Allow-Credentials: true');
} else {
    header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");
?>