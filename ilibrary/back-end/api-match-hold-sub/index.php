<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
header('Content-type: application/json');

$api = "v1";
$entity = "holdings";

include '../db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if($method == 'GET'){
    $param = $_REQUEST['p'];
    $param = rtrim($param, "/");
    $uri = explode('/', $param);
    $cnt = count($uri);
    
    if ($cnt == 2 && $uri[0] == $api && $uri[1] == $entity) {
        // If no specific filtering is required
        $response = [
            'msg' => 'No task for this condition.'
        ];
        echo json_encode($response);
        http_response_code(200);

    } else if ($cnt == 3 && $uri[0] == $api && $uri[1] == $entity) {
        $subId = $uri[2]; // Example subject ID to filter by
        $query = "SELECT * FROM holdings WHERE deleted = '0'"; // Only fetch non-deleted records
        
        $result = mysqli_query($connection, $query);
        $filteredData = [];
        $firstHoldingCategory = null;

        if ($result) {
            // Retrieve the first holding
            $firstHolding = mysqli_fetch_assoc($result);
            if ($firstHolding) {
                // Store the category of the first holding
                $firstHoldingCategory = $firstHolding['category'];
            }

            // If no category is found, return an empty response
            if ($firstHoldingCategory === null) {
                echo json_encode([]);
                http_response_code(200);
                exit;
            }

            // Fetch all holdings that have the same category
            $querySameCategory = "SELECT * FROM holdings WHERE deleted = '0' AND category = '$firstHoldingCategory'"; // Get at most 10 holdings
            $resultSameCategory = mysqli_query($connection, $querySameCategory);

            // Fetch holdings with the same category
            $holdingsSameCategory = [];
            while ($row = mysqli_fetch_assoc($resultSameCategory)) {
                $holdingsSameCategory[] = $row;
            }

            // Prepare the filtered data (top 3 random holdings with the same category)
            foreach ($holdingsSameCategory as $row) {
                $filteredData[] = [
                    'hold_id' => $row['hold_id'],
                    'title' => $row['title'],
                    'accss_num' => $row['accss_num'],
                    'call_num' => $row['call_num'],
                    'published_year' => $row['published_year'],
                    'author_id' => $row['author_id'],
                    'copies' => $row['copies'],
                    'av_copies' => $row['av_copies'],
                    'subjects' => $row['subjects'],
                    'category' => $row['category']
                ];
            }
        
            // Output the filtered data
            echo json_encode($filteredData);
            http_response_code(200); // OK
        } else {
            // Handle database query failure
            $response = [
                'msg' => 'Error fetching data: ' . mysqli_error($connection)
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
