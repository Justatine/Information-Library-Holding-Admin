<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $putData = file_get_contents("php://input");
        $requestData = json_decode($putData, true);

        // Extract data from request
        $title = $requestData['edit_title'];
        $acs_num = $requestData['edit_accss_num'];
        $call_num = $requestData['edit_call_num'];
        $year_published = $requestData['edit_published_year'];
        $copies = $requestData['edit_copies'];  
        $av_copies = $requestData['edit_av_copies'];
        $subjects = isset($requestData['edit_subjects']) ? $requestData['edit_subjects'] : null;  
        $course = $requestData['edit_course'];  
        $department = $requestData['edit_department'];  
        $keyword = $requestData['edit_keyword'];     
        $id = $requestData['id'];   
        $authors = isset($requestData['edit_authors']) ? $requestData['edit_authors'] : null;

        // Update holdings query
        $query = "UPDATE holdings SET 
            title = ?, 
            accss_num = ?,
            call_num = ?,
            published_year = ?,
            copies = ?,
            av_copies = ?,
            keyword = ?,
            department = ?,
            course = ?
            WHERE hold_id = ?";

        $sql = $connection->prepare($query);
        $sql->bind_param(
            "sissiisisi", 
            $title,
            $acs_num,
            $call_num,
            $year_published,
            $copies,
            $av_copies,
            $keyword,
            $department,
            $course,
            $id
        );

        if ($sql->execute()) {
            if (isset($subjects)) {
                $subjects_array = json_decode($subjects, true) ?? [];
                
                $delete_all_subjects = "DELETE FROM subjects_holdings WHERE hold_id = ?";
                $stmt = $connection->prepare($delete_all_subjects);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                if (!empty($subjects_array)) {
                    $insert_query = "INSERT INTO subjects_holdings (hold_id, sub_id) VALUES (?, ?)";
                    $stmt = $connection->prepare($insert_query);
                    
                    foreach ($subjects_array as $subject) {
                        $stmt->bind_param("ii", $id, $subject['sub_id']);
                        $stmt->execute();
                    }
                }
            }

            if (isset($authors)) {
                $authors_array = json_decode($authors, true) ?? [];

                $delete_all_query = "DELETE FROM holdings_authors WHERE hold_id = ?";
                $stmt = $connection->prepare($delete_all_query);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                if (!empty($authors_array)) {
                    $insert_query = "INSERT INTO holdings_authors (hold_id, author_id) VALUES (?, ?)";
                    $stmt = $connection->prepare($insert_query);
                    
                    foreach ($authors_array as $author) {
                        $stmt->bind_param("ii", $id, $author['author_id']);
                        $stmt->execute();
                    }
                }
            }

            $response['success'] = true;
            $response['message'] = 'Holding updated successfully.';
        } else {
            throw new Exception('Failed to execute update query.');
        }
    } else {
        throw new Exception('Invalid request method.');
    }

    $connection->commit();
} catch (Exception $e) {
    $connection->rollback();
    $response['error'] = $e->getMessage();
    error_log('Error: ' . $e->getMessage());
}

echo json_encode($response);