<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
header('Content-type: application/json');

$api = "v1";
$entity = "ocr_log";

include '../db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $param = $_REQUEST['p'] ?? '';
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    // Check if the endpoint is for inserting into the ocr_log table
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {
        
        // Retrieve OCR result from POST data
        $ocrResult = $_POST['ocr_result'] ?? '';
        $timestamp = date('Y-m-d H:i:s');
        $ocrResult = mysqli_real_escape_string($connection, $ocrResult);
        
        // SQL query to insert data into the ocr_log table
        $sql = "INSERT INTO ocr_log (ocr_result, timestamp) VALUES ('$ocrResult', '$timestamp')";
        
        // Execute the query
        if (mysqli_query($connection, $sql)) {
            // Insert successful
            $response = [
                'msg' => 'OCR result logged successfully'
            ];
        } else {
            $response = [
                'msg' => 'Failed to log OCR result.',
                'error' => mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
        
    } else {
        $response = [
            'msg' => 'Invalid request.'
        ];
        http_response_code(400);
    }
} else {
    $response = [
        'msg' => 'Invalid method.'
    ];
    http_response_code(405);
}

// Return the JSON response
echo json_encode($response);
?>
