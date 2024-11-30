<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-type: application/json');

$api = "v1";
$entity = "holdings";

include '../db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight request
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit; // End script for OPTIONS requests
}

if ($method == 'POST') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity) {
        $accId = $uri[2];

        $title = $_POST['title'];
        $acs_num = $_POST['acs_num'];
        $call_num = $_POST['call_num'];
        $pub_year = $_POST['pub_year'];
        $auth = $_POST['author'];
        $copies = $_POST['copies'];
        $av_copies = $_POST['av_copies'];
        $category = $_POST['category'];
        $subjects = $_POST['subjects'];

        // Prepare the SQL insert statement for holding
        $query = "INSERT INTO $entity (title, accss_num, call_num, published_year, author_id, copies, av_copies, category, subjects) 
                  VALUES ('$title', '$acs_num', '$call_num', '$pub_year', '$auth', '$copies', '$av_copies','$category', '$subjects')";
        
        if (mysqli_query($connection, $query)) {
            // Prepare the SQL insert statement for logs
            $log_message = "New holding added: Title - " . $title . ".";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accId')";
            
            // Execute the logs insertion
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'Holding added successfully.',
                ];
                http_response_code(201);
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error inserting holding: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
        echo json_encode($response);
    } else {
        // Invalid request
        $response = [
            'msg' => 'Invalid request.'
        ];
        echo json_encode($response);
        http_response_code(400); // Bad Request
    }
} else if($method == 'GET'){
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {

        $query = "
            SELECT 
                holdings.hold_id, 
                holdings.title, 
                holdings.accss_num, 
                holdings.call_num, 
                holdings.published_year, 
                holdings.author_id, 
                holdings.copies, 
                holdings.av_copies, 
                holdings.category, 
                holdings.subjects, 
                authors.fname AS author_first_name, 
                authors.lname AS author_last_name
            FROM 
                $entity AS holdings
            LEFT JOIN 
                authors ON holdings.author_id = authors.author_id
            WHERE 
                holdings.deleted = '0'
        ";
    
        $result = mysqli_query($connection, $query);
    
        if ($result) {
            $holding = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $holding[] = [
                    'hold_id' => $row['hold_id'],
                    'title' => $row['title'],
                    'accss_num' => $row['accss_num'],
                    'call_num' => $row['call_num'],
                    'published_year' => $row['published_year'],
                    'author_id' => $row['author_id'],
                    'author_name' => $row['author_first_name'] . ' ' . $row['author_last_name'], // Combine first and last name
                    'copies' => $row['copies'],
                    'av_copies' => $row['av_copies'],
                    'category' => $row['category'],
                    'subjects' => $row['subjects']
                ];
            }
    
            // Send the holding as a JSON response
            echo json_encode($holding);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching holding: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity && $uri[2] == "count") {
        // Count holdings
        $query = "SELECT COUNT(*) AS count FROM $entity WHERE deleted = '0'";

        $result = mysqli_query($connection, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $response = [
                'count' => $row['count']
            ];
            echo json_encode($response);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error counting authors: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity){
        $holdId = $uri[2];
        $query = "SELECT * FROM $entity WHERE hold_id = $holdId and deleted = '0'";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $subjects = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $subjects[] = [
                    'hold_id' => $row['hold_id'],
                    'title' => $row['title'],
                    'accss_num' => $row['accss_num'],
                    'call_num' => $row['call_num'],
                    'published_year' => $row['published_year'],
                    'author_id' => $row['author_id'],
                    'copies' => $row['copies'],
                    'av_copies' => $row['av_copies'],
                    'category' => $row['category'],
                    'subjects' => $row['subjects']
                ];
            }

            // Send the subjects as a JSON response
            echo json_encode($subjects);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching subjects: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity && $uri[3] == "category"){
        $holdId = $uri[2];
        $query = "
                SELECT * 
                FROM holdings 
                WHERE category = (SELECT category FROM holdings WHERE hold_id = $holdId LIMIT 3) 
                  AND deleted = '0'
                ORDER BY RAND()
                LIMIT 3;
        ";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            $subjects = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $subjects[] = [
                    'hold_id' => $row['hold_id'],
                    'title' => $row['title'],
                    'accss_num' => $row['accss_num'],
                    'call_num' => $row['call_num'],
                    'published_year' => $row['published_year'],
                    'author_id' => $row['author_id'],
                    'copies' => $row['copies'],
                    'av_copies' => $row['av_copies'],
                    'category' => $row['category'],
                    'subjects' => $row['subjects']
                ];
            }

            // Send the subjects as a JSON response
            echo json_encode($subjects);
            http_response_code(200); // OK
        } else {
            // Database error
            $response = [
                'msg' => 'Error fetching subjects: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else {
        // Invalid request
        $response = [
            'msg' => 'Invalid request.'
        ];
        echo json_encode($response);
        http_response_code(400); // Bad Request
    }
} else if ($method == 'PUT') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);

    if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity) {
        // Get the subject ID from the URL
        $hold_id = $uri[2];
        $accId = $uri[3];

        $json = file_get_contents('php://input');

        // Decode the JSON to an associative array
        $_PUT = json_decode($json , true);

        $title = $_PUT['title'];
        $accssNum = $_PUT['accss_num'];
        $callNum = $_PUT['call_num'];
        $pubYear = $_PUT['published_year'];
        $authId = $_PUT['author_id'];
        $copies = $_PUT['copies'];
        $avCopies = $_PUT['av_copies'];
        $category = $_PUT['category'];
        $subjects = $_PUT['subjects'];
        
        // Prepare the SQL update query
        $query = "UPDATE $entity SET 
                    title = '$title', 
                    accss_num = '$accssNum', 
                    call_num = '$callNum', 
                    published_year = '$pubYear', 
                    author_id = '$authId', 
                    copies = '$copies', 
                    av_copies = '$avCopies', 
                    category = '$category', 
                    subjects = '$subjects'
                  WHERE hold_id = '$hold_id'";

        // Execute the query
        if (mysqli_query($connection, $query)) {
            // Successful update
            $log_message = "Holding updated: Title - " . $title . ".";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accId')";
            
            // Execute the logs insertion
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'Holding updated successfully.',
                    'id' => '$hold_id',
                ];
                echo json_encode($response);
                http_response_code(201);
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error updating holding: ' . mysqli_error($connection)
            ];
            echo json_encode($response);
            http_response_code(500); // Internal Server Error
        }
    } else {
        $response = ['msg' => 'Invalid request.'];
        echo json_encode($response);
        http_response_code(400); // Bad Request
    }
} else if ($method == 'DELETE') {
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);

    if ($cnt == 4 && $uri[0] == $api && $uri[1] == $entity) {
        $holdId = $uri[2];
        $accId = $uri[3];

        $query = "UPDATE $entity SET deleted = '1' WHERE hold_id = '$holdId'";

        if (mysqli_query($connection, $query)) {
            // Successful delete
            $log_message = "Holding deleted: Holding Id - " . $holdId . ".";
            $log_query = "INSERT INTO logs (activity, timestamp, admin_id) VALUES ('$log_message', NOW(), '$accId')";
            
            // Execute the logs insertion
            if (mysqli_query($connection, $log_query)) {
                // Successfully added to logs
                $response = [
                    'msg' => 'Holding deleted successfully.',
                ];
                http_response_code(201);
            }
        } else {
            // Database error
            $response = [
                'msg' => 'Error deleting holding: ' . mysqli_error($connection)
            ];
            http_response_code(500); // Internal Server Error
        }
        echo json_encode($response);
    }
} else {
    // Invalid method
    $response = [
        'msg' => 'Invalid method.'
    ];
    echo json_encode($response);
    http_response_code(405); // Method Not Allowed
}

// Close the database connection
mysqli_close($connection);
?>
