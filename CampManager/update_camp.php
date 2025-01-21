<?php
    $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres");
    $query = "select start_date, duration, coordinator_id, location_id, min_age, max_age, participant_nr, staff_nr 
              from camp where camp_id = '$_POST[id]'
              order by camp_id"; 
    $result = pg_query($db, $query); 
    $row = pg_fetch_row($result);
    
    if($row) {
        echo "<form action=\"index.php\" method=\"POST\" onsubmit=\"return checkform(event)\">
            <label>Start date:</label>
            <input type=\"date\" id=\"sdate\" value=$row[0] required><br>
            <label>Duration:</label>
            <input type=\"text\" id=\"duration\" value=$row[1] required><br>
            <label for=\"coord\">Coordinator:</label>
            <select name=\"coord\" id=\"coord\">";
        $query2 = "select p.person_id, CONCAT(p.first_name, ' ', p.last_name) 
                    from person p join staff s on p.person_id = s.person_id
                    where s.can_be_coordinator = true";
        $result2 = pg_query($db, $query2);
        while ($row2 = pg_fetch_row($result2)) {
            if($row2[0] == $row[2]){
                echo "<option value=\"coord_$row2[0]\" selected>$row2[1]</option>";
            }else{
                echo "<option value=\"coord_$row2[0]\">$row2[1]</option>";
            }
        }
        echo "</select><br>";
        echo "<label for=\"loc\">Location:</label>
              <select name=\"loc\" id=\"loc\">";
        $query2 = "select location_id, address, beds_nr
                    from location";
        $result2 = pg_query($db, $query2);
        while ($row2 = pg_fetch_row($result2)) {
            if($row2[0] == $row[3]){
                echo "<option value=\"loc_$row2[0]\" selected>$row2[1] -> $row2[2] beds</option>";
            }else{
                echo "<option value=\"loc_$row2[0]\">$row2[1] -> $row2[2] beds</option>";
            }
        }
        echo "</select><br>";
        echo "<label>Minimum age:</label>
            <input type=\"text\" id=\"min_age\" required value=$row[4]><br>
            <label>Maximum age:</label>
            <input type=\"text\" id=\"max_age\" required value=$row[5]><br>
            <label>Participants:</label>
            <input type=\"text\" id=\"part\" required value=$row[6]><br>
            <label>Staff Members: $row[7]</label><br>";
        echo "<input type=\"submit\" value=\"Submit\">";
        echo "</form>";
        echo "<div id=\"div\"></div>";

        echo "<script>
        function isInteger(str) { 
    if(str.length >= 9)
        return false;
    for(var i = 0; i < str.length; i++) {
        if(str[i] < 0 || str[i] > 9)
            return false;
    } 
    if(str[0] === '0' && str.length > 1)
        return false;
    return true;
}
        
        async function checkform(e) {
    e.preventDefault();

    var min_age = document.getElementById(\"min_age\").value;
    var max_age = document.getElementById(\"max_age\").value;
    var part = document.getElementById(\"part\").value;
    var duration = document.getElementById(\"duration\").value;
    var sdate = document.getElementById(\"sdate\").value;
    if(new Date(sdate) <= new Date()) {
        alert(\"You have to choose a date in the future!\");
        return false;
    }
    if(!isInteger(duration) || parseInt(duration) <= 0 || parseInt(duration) > 20) {
        alert(\"Duration must be an integer between 1 and 20\");
        return false;
    }
    if(!isInteger(min_age) || parseInt(min_age) < 8 || parseInt(min_age) > 18) {
        alert(\"Minimum age must be between 8 and 18\");
        return false;
    }
    if(!isInteger(max_age) || parseInt(max_age) < 9 || parseInt(max_age) > 20 || parseInt(max_age) <= parseInt(min_age)) {
        alert(\"Maximum age must be between 9 and 20 and greater than the minimum age\");
        return false;
    }
    if(!isInteger(part) || parseInt(part) <= 30) {
        alert(\"Participant number must be an integer greater than 30\");
        return false;
    }
    
     var location_id = document.getElementById(\"loc\").value.split('_')[1];
     var response = await fetch('http://localhost:3000/get_location.php', {
         method: \"POST\",
         headers: {
             'Content-Type': 'application/json',
         },
         body: JSON.stringify({location_id: location_id}),
     });
       var result = await response.json();
     if(parseInt(result.beds) < $row[7] + parseInt(part) + 1) {
         alert(\"Not enough beds at location\");
         return false;
     }
         var coordinator_id = document.getElementById(\"coord\").value.split('_')[1];
     await updateCamp('$_POST[id]', coordinator_id, location_id, part, sdate, duration, min_age, max_age);
    document.getElementById(\"div\").innerHTML = \"SUCCESS!\";
    }
    async function updateCamp(camp_id, coordinator_id, location_id, part, sdate, duration, min_age, max_age) {
    var response = await fetch('http://localhost:3000/update_update.php', {
        method: \"POST\",
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({camp_id: camp_id, coordinator_id: coordinator_id, location_id: location_id, part: part, sdate: sdate, duration: duration, min_age: min_age, max_age: max_age}),
    });
    var result = await response.json();
    }</script>";
    } else {
        echo "Not a valid id";
    }
?>