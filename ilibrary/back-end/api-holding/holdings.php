<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
include '../db_connection.php';

$response = array();

$connection->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            $query = "SELECT a.*, c.author_id, e.dept_id, e.deptname
                        FROM holdings a 
						LEFT JOIN holdings_authors AS b
                        ON a.hold_id=b.hold_id
                        LEFT JOIN authors AS c
                        ON b.author_id=c.author_id
                        INNER JOIN department e
                        ON a.department = e.dept_id
                        WHERE a.hold_id = ?
                        LIMIT 1";
            $sql = $connection->prepare($query);
            $sql->bind_param("i", $id);
            $sql->execute();
            $result = $sql->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $response['data'] = $row;
                
                $subjectsQuery = "SELECT e.sub_id, e.sub_name, e.course
                                 FROM subjects e
                                 LEFT JOIN subjects_holdings b ON b.sub_id = e.sub_id
                                 WHERE b.hold_id = ?";
                $subjectsSql = $connection->prepare($subjectsQuery);
                $subjectsSql->bind_param("i", $id);
                $subjectsSql->execute();
                $subjectsResult = $subjectsSql->get_result();
                
                $subjects = array();
                while ($subjectRow = $subjectsResult->fetch_assoc()) {
                    $subjects[] = array(
                        'value' => $subjectRow['sub_name'].' - '.$subjectRow['course']  ,
                        'sub_id' => $subjectRow['sub_id']
                    );
                }
                
                $response['data']['subjects'] = $subjects;

                $authorsQuery = "SELECT a.author_id, a.fname, a.lname
                                 FROM authors a
                                 LEFT JOIN holdings_authors AS ha 
                                 ON ha.author_id = a.author_id
                                 WHERE ha.hold_id = ?";
                $authorsSql = $connection->prepare($authorsQuery);
                $authorsSql->bind_param("i", $id);
                $authorsSql->execute();
                $authorsResult = $authorsSql->get_result();
                
                $authors = array();
                while ($authorRow = $authorsResult->fetch_assoc()) {
                    $authors[] = array(
                        'value' => $authorRow['fname'] . ' ' . $authorRow['lname'],
                        'author_id' => $authorRow['author_id']
                    );
                }
                
                $response['data']['authors'] = $authors;
            } else {
                $response['data'] = array();
            }
        }
        else{
            $query = "SELECT a.*, e.deptname    
                        FROM holdings AS a
                        LEFT JOIN department e
                        ON a.department = e.dept_id";
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
    }
    
    $connection->commit();
} catch (\Exception $e) {
    $response['error']='Failed to fetch data';
}
echo json_encode($response);