<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-type: application/json');

$api = "v1";
$entity = "authors";

include '../db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight request
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit; // End script for OPTIONS requests
}

if ($method == 'POST') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity) {
        $accid = $uri[2];

        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        
        // Prepare the SQL insert statement
        $query = "INSERT INTO $entity (fname, lname) VALUES ('$fname', '$lname')";
        
        if (mysqli_query($connection, $query)) {
            // Prepare the SQL insert statement for logs
            $log_message = "New author added: Name - " . $fname . " " . $lname . ".";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accid')";
            
            // Execute the logs insertion
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'Author added successfully.',
                ];
                http_response_code(201);
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error inserting user: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
    } else {
        // Invalid request
        $response = [
            'msg' => 'Invalid request.'
        ];
        http_response_code(400); // Bad Request
    }
    echo json_encode($response);
} else if($method == 'GET'){
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {

        // Prepare SQL query to select all author
        $query = "SELECT * FROM $entity WHERE deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $authors = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $authors[] = [
                    'author_id' => $row['author_id'],
                    'fname' => $row['fname'],
                    'lname' => $row['lname'],
                ];
            }

            // Send the authors as a JSON response
            echo json_encode($authors);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching authors: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity && $uri[2] == "count") {
        // Count authors
        $query = "SELECT COUNT(*) AS count FROM $entity WHERE deleted = '0'";

        $result = mysqli_query($connection, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $response = [
                'count' => $row['count']
            ];
            echo json_encode($response);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error counting authors: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity){
        $pubId = $uri[2];
        $query = "SELECT * FROM $entity WHERE author_id = $pubId and deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $authors = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $authors[] = [
                    'author_id' => $row['author_id'],
                    'fname' => $row['fname'],
                    'lname' => $row['lname'],
                ];
            }

            // Send the authors as a JSON response
            echo json_encode($authors);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching authors: ' . mysqli_error($connection)
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
} else if ($method == 'PUT') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);

    if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity) {
        // Get the publisher ID from the URL
        $authId = $uri[2];
        $accid = $uri[3];

        $json = file_get_contents('php://input');
        $_PUT = json_decode($json, true);

        $fname = $_PUT['fname'];
        $lname = $_PUT['lname'];

        // Prepare the SQL update statement
        $query = "UPDATE $entity SET fname = '$fname', lname = '$lname' WHERE author_id = '$authId'";
        
        if (mysqli_query($connection, $query)) {
            // Prepare the SQL insert statement for logs
            $log_message = "Author updated: Name - " . $fname . " " . $lname . ".";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accid')";
            
            // Execute the logs insertion
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'Author updated successfully.',
                ];
                http_response_code(201);
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error updating author: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
        echo json_encode($response);
    } else {
        $response = ['msg' => 'Invalid request.'];
        echo json_encode($response);
        http_response_code(400); // Bad Request
    } 
}  else if ($method == 'DELETE') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);

    if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity) {
        $authId = $uri[2];
        $accid = $uri[3];
    
        $query = "UPDATE $entity SET deleted = '1' WHERE author_id = '$authId'";

        if (mysqli_query($connection, $query)) {
            // Prepare the SQL insert statement for logs
            $log_message = "Author deleted: author id - " . $authId . ".";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accid')";
            
            // Execute the logs insertion
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'Author deleted successfully.',
                ];
                http_response_code(201);
            }
            http_response_code(201); // Created
        } else {
            // Database error
            $response = [
                'msg' => 'Error deleting author: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
        echo json_encode($response);
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
