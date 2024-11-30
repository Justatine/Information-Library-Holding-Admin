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
        $sub_name = $requestData['sub_name'];
        $sub_desc = $requestData['sub_desc'];
        $year_level = $requestData['year_level'];
        $acad_year = $requestData['acad_year'];
        $semester = $requestData['semester'];
        $course = $requestData['course'];

        $query = "UPDATE subjects SET sub_name=?, sub_desc=?, year_level=?, acad_year=?, semester=?, course=? WHERE sub_id = ?";
        $sql=$connection->prepare($query);
        $sql->bind_param("ssiissi",$sub_name,$sub_desc, $year_level, $acad_year, $semester, $course, $id);
        
        if ($sql->execute()) {
            $response['success'] = true;
            $response['message'] = 'Subject updated';
        }
    }
    $connection->commit();
} catch (\Exception $e) {
    $response['error']='Failed to update subject';
}
echo json_encode($response);