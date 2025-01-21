<?php
    $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres");

    $data = json_decode(file_get_contents("php://input"), true);
    
    $camp_id = (int) $data['camp_id'];
    $person_id = (int) $data['person_id'];
    $role = $data['role'];

    $query = "insert into participation (camp_id, person_id, role) values ($1, $2, $3)";
    $result = pg_query_params($db, $query, [
        $camp_id, $person_id, $role
    ]);
    
    if($result) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "failure"]);
    }
?>