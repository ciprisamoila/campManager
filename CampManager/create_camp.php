<?php
    $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres");

    $data = json_decode(file_get_contents("php://input"), true);
    
    $coordinator_id = (int) $data['coordinator_id'];
    $location_id = (int) $data['location_id'];
    $part = (int) $data['part'];
    $staff = (int) $data['staff'];
    $sdate = $data['sdate'];
    $duration = (int) $data['duration'];
    $min_age = (int) $data['min_age'];
    $max_age = (int) $data['max_age'];

    $query = "insert into camp (coordinator_id, location_id, participant_nr, staff_nr, start_date, duration, min_age, max_age) values ($1, $2, $3, $4, $5, $6, $7, $8) returning camp_id";
    $result = pg_query_params($db, $query, [
        $coordinator_id, $location_id, $part, $staff, $sdate, $duration, $min_age, $max_age
    ]);

    $row = pg_fetch_assoc($result);
    $last_id = $row['camp_id'];
    echo json_encode(["id" => $last_id]);
?>