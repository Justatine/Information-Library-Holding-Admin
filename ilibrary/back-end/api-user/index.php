<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-type: application/json');

$api = "v1";
$entity = "admin_acc";

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
        $userid = $uri[2];
        
        // Retrieve username and password from POST data
        $idnum = $_POST['idnum'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL insert statement
        $query = "INSERT INTO $entity (admin_id, fname, lname, email, password) VALUES ('$idnum', '$fname', '$lname', '$email', '$hashed_password')";
        
        if (mysqli_query($connection, $query)) {
            // Successful insertion
            // Log activity
            $log_message = "New admin added: Admin - $fname $lname.";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$userid')";
            
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'User added successfully.',
                    'idnum' => $idnum
                ];
                http_response_code(201); // Created
            } else {
                // Log insertion failed
                $response = [
                    'msg' => 'User added but failed to log activity.'
                ];
                http_response_code(500); // Internal Server Error
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error inserting user: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
         echo json_encode($response);
    } else {
        // Invalid request
        $response = [
            'msg' => 'Invalid request.'
        ];
        http_response_code(400); // Bad Request
    }
} else if ($method == 'GET') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {
        $query = "SELECT * FROM $entity WHERE deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $users = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
            echo json_encode($users);
            http_response_code(200); // OK
        } else {
            $response = [
                'msg' => 'Error fetching users: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    }if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity) {
        $accid = $uri[2];
        
        $query = "SELECT * FROM $entity WHERE admin_id = $accid AND deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $users = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
            echo json_encode($users);
            http_response_code(200); // OK
        } else {
            $response = [
                'msg' => 'Error fetching users: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    }
} else if ($method == 'PUT') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity) {
        $userid = $uri[2]; 
        $accid = $uri[3]; 

        // Retrieve JSON input and decode it
        $json = file_get_contents('php://input');
        $_PUT = json_decode($json, true);

        if (isset($_PUT['fname'], $_PUT['lname'], $_PUT['email'])) {

            $fname = $_PUT['fname'];
            $lname = $_PUT['lname'];
            $email = $_PUT['email'];
            $password = isset($_PUT['password']) ? $_PUT['password'] : null;

            $set_clause = "fname = '$fname', lname = '$lname', email = '$email'";

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $set_clause .= ", password = '$hashed_password'";
            }

            $query = "UPDATE $entity SET $set_clause WHERE admin_id = '$userid'";

            if (mysqli_query($connection, $query)) {
 
                $log_message = "Admin details updated: Admin - $fname $lname.";
                $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accid')";
                
                if (mysqli_query($connection, $log_query)) {
                    $response = [
                        'msg' => 'User updated successfully.'
                    ];
                    http_response_code(200);
                } else {
                    $response = [
                        'msg' => 'User updated but failed to log activity.'
                    ];
                    http_response_code(500);
                }
            } else {
                $response = [
                    'msg' => 'Error updating user: ' . mysqli_error($connection)
                ];
                http_response_code(500);
            }
        } else {
            // Handle missing required fields
            $response = ['msg' => 'Missing required fields.'];
            http_response_code(400);
        }
    } else {
        $response = ['msg' => 'Invalid request.'];
        http_response_code(400);
    }
    echo json_encode($response);
} else if ($method == 'DELETE') {

    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity) {
        $userid = $uri[2]; 
        $accid = $uri[3]; 

        $query = "UPDATE $entity SET deleted = '1' WHERE admin_id = '$userid'";

        if (mysqli_query($connection, $query)) {

            $log_message = "Deleted admin: Admin ID - $userid.";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accid')";
            
            if (mysqli_query($connection, $log_query)) {
                $response = [
                    'msg' => 'User deleted successfully.'
                ];
                http_response_code(200); // OK
            } else {
                $response = [
                    'msg' => 'User deleted but failed to log activity.'
                ];
                http_response_code(500); // Internal Server Error
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error deleting user: ' . mysqli_error($connection)
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
