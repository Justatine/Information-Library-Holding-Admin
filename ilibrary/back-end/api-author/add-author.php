<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $author_fname = $_POST['fname'] ?? null;
        $author_lname = $_POST['lname'] ?? null;
        $author_corporate = $_POST['corporate_author'] ?? null;

        if ((!empty($author_fname) && !empty($author_lname) && empty($author_corporate)) ||
            (empty($author_fname) && empty($author_lname) && !empty($author_corporate))) {
            
            $query = "INSERT INTO authors(fname, lname, corporate_author) VALUES(?, ?, ?)";
            $sql = $connection->prepare($query);
            $sql->bind_param("sss", $author_fname, $author_lname, $author_corporate);
            
            if ($sql->execute()) {
                $response['success'] = true;
                $response['message'] = 'Author added successfully';            
            }

        } else {
            throw new Exception('Invalid author data: Please provide either first name and last name OR corporate author, but not both');
        }
    } else {
        throw new Exception('Invalid request method');
    }
    $connection->commit();
} catch (Exception $e) {
    $connection->rollback();
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    error_log('Author addition failed: ' . $e->getMessage());
}

echo json_encode($response);