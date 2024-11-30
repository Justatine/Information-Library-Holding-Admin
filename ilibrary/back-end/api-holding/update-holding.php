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
            if (!empty($subjects)) {
                $subjects_array = json_decode($subjects, true);

                // Get current subjects for the holding
                $current_subjects_query = "SELECT sub_id FROM subjects_holdings WHERE hold_id = ?";
                $stmt = $connection->prepare($current_subjects_query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                $current_sub_ids = [];
                while ($row = $result->fetch_assoc()) {
                    $current_sub_ids[] = $row['sub_id'];
                }

                // Get new subject IDs from input
                $new_sub_ids = array_map(function ($subject) {
                    return $subject['sub_id'];
                }, $subjects_array);

                // Find subjects to delete (exist in table but not in input)
                $subjects_to_delete = array_diff($current_sub_ids, $new_sub_ids);

                // Find subjects to add (exist in input but not in table)
                $subjects_to_add = array_diff($new_sub_ids, $current_sub_ids);

                // Delete subjects that are no longer present
                if (!empty($subjects_to_delete)) {
                    $placeholders = implode(',', array_fill(0, count($subjects_to_delete), '?'));
                    $delete_query = "DELETE FROM subjects_holdings WHERE hold_id = ? AND sub_id IN ($placeholders)";
                    
                    $stmt = $connection->prepare($delete_query);
                    $types = str_repeat('i', count($subjects_to_delete) + 1);
                    $stmt->bind_param($types, $id, ...$subjects_to_delete);
                    $stmt->execute();
                }

                // Add new subjects
                if (!empty($subjects_to_add)) {
                    $insert_query = "INSERT INTO subjects_holdings (hold_id, sub_id) VALUES (?, ?)";
                    $stmt = $connection->prepare($insert_query);
                    
                    foreach ($subjects_to_add as $sub_id) {
                        $stmt->bind_param("ii", $id, $sub_id);
                        $stmt->execute();
                    }
                }
            }

            if (!empty($authors)) {
                $authors_array = json_decode($authors, true);

                // Get current authors for the holding
                $current_authors_query = "SELECT author_id FROM holdings_authors WHERE hold_id = ?";
                $stmt = $connection->prepare($current_authors_query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                $current_auth_ids = [];
                while ($row = $result->fetch_assoc()) {
                    $current_auth_ids[] = $row['author_id'];
                }

                // Get new author IDs from input
                $new_auth_ids = array_map(function ($author) {
                    return $author['author_id'];
                }, $authors_array); 

                // Find authors to delete (exist in table but not in input)
                $authors_to_delete = array_diff($current_auth_ids, $new_auth_ids);

                // Find authors to add (exist in input but not in table)
                $authors_to_add = array_diff($new_auth_ids, $current_auth_ids);

                // Delete authors that are no longer present
                if (!empty($authors_to_delete)) {
                    $placeholders = implode(',', array_fill(0, count($authors_to_delete), '?'));
                    $delete_query = "DELETE FROM holdings_authors WHERE hold_id = ? AND author_id IN ($placeholders)";
                    
                    $stmt = $connection->prepare($delete_query);
                    $types = str_repeat('i', count($authors_to_delete) + 1);
                    $stmt->bind_param($types, $id, ...$authors_to_delete);
                    $stmt->execute();
                }

                // Add new authors
                if (!empty($authors_to_add)) {
                    $insert_query = "INSERT INTO holdings_authors (hold_id, author_id) VALUES (?, ?)";
                    $stmt = $connection->prepare($insert_query);
                    
                    foreach ($authors_to_add as $auth_id) {
                        $stmt->bind_param("ii", $id, $auth_id);
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