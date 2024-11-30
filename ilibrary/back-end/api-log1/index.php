<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-type: application/json');

$api = "v1";
$entity = "logs";

include '../db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight request
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit; // End script for OPTIONS requests
}

if ($method == 'GET') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);

    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {
        // Prepare SQL query to select all logs
        $query = "SELECT log_id, activity, timestamp, admin_id FROM $entity ORDER BY timestamp DESC";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $logs = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $logs[] = [
                    'log_id' => $row['log_id'],
                    'activity' => $row['activity'],
                    'timestamp' => $row['timestamp'],
                    'admin_id' => $row['admin_id']
                ];
            }

            // Send the logs as a JSON response
            echo json_encode($logs);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching logs: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else {
        // Invalid request
        $response = [
            'msg' => 'Invalid request.'
        ];
        echo json_encode($response);
        http_response_code(400); // Bad Request
    }
} else {
    // Invalid method
    $response = [
        'msg' => 'Invalid method.'
    ];
    echo json_encode($response);
    http_response_code(405); // Method Not Allowed
}

// Close the database connection
mysqli_close($connection);
?>
