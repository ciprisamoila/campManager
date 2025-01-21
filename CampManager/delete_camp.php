<?php
    $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres");
    $query = "delete from camp where camp_id = '$_POST[id]' returning *"; 
    $result = pg_query($db, $query); 
    $row = pg_fetch_row($result);
    if($row) {
        echo "Succes!";
    } else {
        echo "Not a valid id";
    }
?>