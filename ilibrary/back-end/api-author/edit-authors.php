<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $putData = file_get_contents("php://input");
        $requestData = json_decode($putData, true);
    
        $id = $requestData['id'];
        $fname = $requestData['fname1'];
        $lname = $requestData['lname1'];
        $corporate_author = $requestData['corporate_author1'];

        $query = "UPDATE authors SET fname=?, lname=?, corporate_author=? WHERE author_id = ?";
        $sql = $connection->prepare($query);
        $sql->bind_param("sssi", $fname, $lname, $corporate_author, $id);
        
        if ($sql->execute()) {
            $response['success'] = true;
            $response['message'] = 'Author updated successfully';
        }
    }
    $connection->commit();
} catch (\Exception $e) {
    $response['error'] = 'Failed to update author';
}
echo json_encode($response);