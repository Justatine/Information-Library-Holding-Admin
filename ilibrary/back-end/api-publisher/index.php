<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-type: application/json');

$api = "v1";
$entity = "publishers";

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
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {

        $pub_name = $_POST['pub_name'];
        $pub_address = $_POST['pub_address'];
        $contact_num = $_POST['contact_num'];
        $email = $_POST['email'];

        // Prepare the SQL insert statement
        $query = "INSERT INTO $entity (pub_name, pub_address, contact_num, email) VALUES ('$pub_name', '$pub_address','$contact_num','$email')";
        
        if (mysqli_query($connection, $query)) {
            // Successful insertion
            $response = [
                'msg' => 'Publisher added successfully.',
            ];
            http_response_code(201); // Created
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
        // Prepare SQL query to select all subjects
        $query = "SELECT * FROM $entity WHERE deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $publishers = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $publishers[] = [
                    'pub_id' => $row['pub_id'],
                    'pub_name' => $row['pub_name'],
                    'pub_address' => $row['pub_address'],
                    'contact_num' => $row['contact_num'],
                    'email' => $row['email']
                ];
            }

            // Send the publishers as a JSON response
            echo json_encode($publishers);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching publishers: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    }else if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity){
        $pubId = $uri[2];
        $query = "SELECT * FROM $entity WHERE pub_id = $pubId and deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $publishers = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $publishers[] = [
                    'pubId' => $row['pub_id'],
                    'pubName' => $row['pub_name'],
                    'pubAddress' => $row['pub_address'],
                    'contactNum' => $row['contact_num'],
                    'email' => $row['email']
                ];
            }

            // Send the subjects as a JSON response
            echo json_encode($publishers);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching publisher: ' . mysqli_error($connection)
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

    if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity) {
        // Get the publisher ID from the URL
        $pub_id = $uri[2];

        $json = file_get_contents('php://input');
        $_PUT = json_decode($json, true);

        $pubId = $_PUT['pub_Id'];
        $pubName = $_PUT['pub_name'];
        $pubAdd = $_PUT['pub_add'];
        $contactNum = $_PUT['contact_num'];
        $email = $_PUT['email'];

        // Prepare the SQL update statement
        $query = "UPDATE $entity SET pub_id = '$pubId', pub_name = '$pubName', pub_address = '$pubAdd', contact_num = '$contactNum', email = '$email' WHERE pub_id = '$pub_id'";
        
        if (mysqli_query($connection, $query)) {
            // Successful insertion
            $response = [
                'msg' => 'Publisher updated successfully.',
            ];
            http_response_code(201); // Created
        } else {
            // Database error
            $response = [
                'msg' => 'Error updating publisher: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
        echo json_encode($response);
    } else {
        $response = ['msg' => 'Invalid request.'];
        echo json_encode($response);
        http_response_code(400); // Bad Request
    } 
} else if ($method == 'DELETE') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);

    if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity) {
        $pubId = $uri[2];

        $query = "UPDATE $entity SET deleted = '1' WHERE pub_id = '$pubId'";

        if (mysqli_query($connection, $query)) {
            // Successful deletion
            $response = [
                'msg' => 'Publisher deleted successfully.',
            ];
            http_response_code(201); // Created
        } else {
            // Database error
            $response = [
                'msg' => 'Error deleting publisher: ' . mysqli_error($connection)
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
