<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = "SELECT * FROM department";
        $sql=$connection->prepare($query);
        $sql->execute();
        $result=$sql->get_result();
        if ($result->num_rows>0) {
        while ($row=$result->fetch_assoc()) {
            $response['data'][]=$row;
        }
        }else{
        $response['data'] = array();
        }
    }
    
    $connection->commit();
} catch (\Exception $e) {
    $response['error']='Failed to fetch data';
}
echo json_encode($response);