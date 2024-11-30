<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Use isset to ensure POST variables exist
        $title = isset($_POST['title']) ? $_POST['title'] : null;
        $acs_num = isset($_POST['acs_num']) ? $_POST['acs_num'] : null;
        $call_num = isset($_POST['call_num']) ? $_POST['call_num'] : null;
        $pub_year = isset($_POST['pub_year']) ? $_POST['pub_year'] : null;
        $copies = isset($_POST['copies']) ? $_POST['copies'] : null;
        $av_copies = isset($_POST['av_copies']) ? $_POST['av_copies'] : null;
        // $author = 11;
        // $author = isset($_POST['author']) ? $_POST['author'] : null;
        // $sub_name = isset($_POST['sub_name']) ? $_POST['sub_name'] : null;
        $course = isset($_POST['course']) ? $_POST['course'] : null;
        $department = isset($_POST['department']) ? $_POST['department'] : null;
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
        
        // Decode subjects JSON string safely
        $subjects_array = isset($_POST['subjects']) ? json_decode($_POST['subjects'], true) : [];
        $authors_array = isset($_POST['authors']) ? json_decode($_POST['authors'], true) : [];  

        if (!$title || !$acs_num || !$call_num || !$pub_year || !$copies || !$av_copies || !$course || !$department || !$keyword) {
            throw new Exception("Missing required fields.");
        }

        $query = "INSERT INTO holdings(title, accss_num, call_num, published_year, copies, av_copies, course, department, keyword) 
                  VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $connection->prepare($query);
        $sql->bind_param("ssssissss", $title, $acs_num, $call_num, $pub_year, $copies, $av_copies, $course, $department, $keyword);

        if ($sql->execute()) {
            $new_holding_id = $connection->insert_id;

            $subject_query = "INSERT INTO subjects_holdings(sub_id, hold_id) VALUES(?, ?)";
            $subject_stmt = $connection->prepare($subject_query);

            foreach ($subjects_array as $subject) {
                $subject_id = isset($subject['sub_id']) ? $subject['sub_id'] : null;
                if ($subject_id) {
                    $subject_stmt->bind_param("ii", $subject_id, $new_holding_id);
                    $subject_stmt->execute();
                }
            }

            $author_query = "INSERT INTO holdings_authors(hold_id, author_id) VALUES(?, ?)";
            $author_stmt = $connection->prepare($author_query);
            
            foreach ($authors_array as $author) {
                $author_id = isset($author['author_id']) ? $author['author_id'] : null;
                if ($author_id) {
                    $author_stmt->bind_param("ii",$new_holding_id,$author_id);
                    $author_stmt->execute();
                }
            }

            $response['success'] = true;
            $response['message'] = 'Library Holding Added';
            // $response['holding_id'] = $new_holding_id;
            // $response['subjects'] = $subjects_array;
        } else {
            throw new Exception("Failed to insert holding.");
        }
    }

    $connection->commit();
} catch (\Exception $e) {
    $connection->rollback();
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
