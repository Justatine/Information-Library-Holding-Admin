<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
header('Content-type: application/json');

$api = "v1";
$entity = "admin_acc";

include '../db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $param = $_REQUEST['p'] ?? '';
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {
        // Retrieve username and password from POST data
        $idnum = $_POST['idnum'] ?? '';
        $password = $_POST['password'] ?? '';

        //file_put_contents('request1.log', $username , FILE_APPEND);
        //file_put_contents('request2.log', $password , FILE_APPEND);

        // Sanitize username (not necessary if using prepared statements)
        $idnum = mysqli_real_escape_string($connection, $idnum);

        // Retrieve user data from the database based on the provided username
        $sql = "SELECT * FROM $entity WHERE admin_id = '$idnum'";
        $result = mysqli_query($connection, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, return user data
                $response = [
                    'msg' => 'Login successful',
                    'admin_id' => $user['admin_id']
                ];
            } else {
                // Password is incorrect
                $response = [
                    'msg' => 'Incorrect password.'
                ];
            }
        } else {
            // User not found
            $response = [
                'msg' => 'User not found.'
            ];
        }
    } else {
        // Invalid request
        $response = [
            'msg' => 'Invalid request.'
        ];
        http_response_code(400);
    }
} else {
    // Invalid method
    $response = [
        'msg' => 'Invalid method.'
    ];
    http_response_code(405);
}

// Return the JSON response
echo json_encode($response);
?>
