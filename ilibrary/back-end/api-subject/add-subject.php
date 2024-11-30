<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sub_name = $_POST['sub_name'];
        $sub_desc = $_POST['sub_desc'];
        $year_level = $_POST['year_level'];
        $acad_year = $_POST['acad_year'];
        $semester = $_POST['semester'];
        $course = $_POST['course'];

        $query = "INSERT INTO subjects(sub_name,sub_desc,year_level,acad_year,semester,course) VALUES(?, ?, ?, ?, ?, ?)";
        $sql=$connection->prepare($query);
        $sql->bind_param("ssssss",$sub_name,$sub_desc,$year_level,$acad_year,$semester,$course);
        
        if ($sql->execute()) {
            $response['success'] = true;
            $response['message'] = 'Subject added';
        }
    }
    $connection->commit();
} catch (\Exception $e) {
    $response['error']='Failed to fetch data';
}
echo json_encode($response);