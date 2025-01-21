<?php
    $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres");

    $data = json_decode(file_get_contents("php://input"), true);
    
    $coordinator_id = (int) $data['coordinator_id'];
    $location_id = (int) $data['location_id'];
    $part = (int) $data['part'];
    $camp_id = (int) $data['camp_id'];
    $sdate = $data['sdate'];
    $duration = (int) $data['duration'];
    $min_age = (int) $data['min_age'];
    $max_age = (int) $data['max_age'];

    $query = "update camp set coordinator_id=$1, location_id=$2, participant_nr=$3, start_date=$4, duration=$5, min_age=$6, max_age=$7 where camp_id=$8";
    $result = pg_query_params($db, $query, [
        $coordinator_id, $location_id, $part, $sdate, $duration, $min_age, $max_age, $camp_id
    ]);
    echo json_encode(["status" => "success"]);
?>