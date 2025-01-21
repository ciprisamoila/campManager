<?php
    $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres");

    $data = json_decode(file_get_contents("php://input"), true);
    
    if(isset($data['location_id'])) {
        $location_id = $data['location_id'];

        $query = "select beds_nr from location where location_id = " . $location_id;
        $result = pg_query($db, $query);

        $row = pg_fetch_row($result);
        echo json_encode(["beds" => $row[0]]);
    }
?>