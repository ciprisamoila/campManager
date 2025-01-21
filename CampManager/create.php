<!DOCTYPE html>
<html>
<head>
<?php $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres"); ?>
<style>
    .checkbox-container {
        display: grid;
        grid-template-columns: auto auto auto;
    }
</style>
</head>
<body onload="checkboxes()">

<h1>Camp Manager Application</h1>
<ul>
    <li><a href="index.php">View</a></li>
    <li><a href="create.php">Create</a></li>
    <li><a href="update.php">Update</a></li>
    <li><a href="delete.php">Delete</a></li>
</ul>
<h2>Camp Create</h2>
<form method="POST" onsubmit="return checkform(event)">
    <label>Start date:</label>
    <input type="date" id="sdate" required><br>
    <label>Duration:</label>
    <input type="text" id="duration" required><br>
    <label for="coord">Coordinator:</label>
    <select name="coord" id="coord">
        <?php
            $query = "select p.person_id, CONCAT(p.first_name, ' ', p.last_name) 
                      from person p join staff s on p.person_id = s.person_id
                      where s.can_be_coordinator = true";
            $result = pg_query($db, $query);
            while ($row = pg_fetch_row($result)) {
                echo "<option value=\"coord_$row[0]\">$row[1]</option>";
            }
        ?>
    </select><br>
    <label for="loc">Location:</label>
    <select name="loc" id="loc">
        <?php
            $query = "select location_id, address, beds_nr
                      from location";
            $result = pg_query($db, $query);
            while ($row = pg_fetch_row($result)) {
                echo "<option value=\"loc_$row[0]\">$row[1] -> $row[2] beds</option>";
            }
        ?>
    </select><br>
    <label>Minimum age:</label>
    <input type="text" id="min_age" required><br>
    <label>Maximum age:</label>
    <input type="text" id="max_age" required><br>
    <label>Participants:</label>
    <input type="text" id="part" required><br>
    <label>Staff Members:</label>
    <input type="text" id="smlabel" oninput="checkboxes()" value="0" required><br>
    <label id="slabel">Staff:</label><br>
    <div class="checkbox-container">
    <?php
        $query = "select p.person_id, CONCAT(p.first_name, ' ', p.last_name)
                  from person p join staff s on p.person_id = s.person_id";
        $result = pg_query($db, $query);
        while ($row = pg_fetch_row($result)) {
            echo "<label><input type=\"checkbox\" onchange=\"checkboxes()\" id=\"s_$row[0]\">$row[1]</option></label>";
        }
    ?>
    </div>
    <input type="submit" value="Submit">
</form>

<p id="message"></p>

</body>
</html>
<script>
function checkboxes(){
    var inputElems = document.getElementsByTagName("input");
    var count = 0;
    var limit = document.getElementById("smlabel").value;
    for (var i=0; i<inputElems.length; i++) {
        if (inputElems[i].type === "checkbox" && inputElems[i].checked === true){
            count++;
        }
    }
    for(var i=0; i<inputElems.length; i++) {
        if(inputElems[i].type === "checkbox") {
            if(inputElems[i].checked === false && count >= parseInt(limit)) {
                inputElems[i].disabled = true;
            } else {
                inputElems[i].disabled = false;
            }
        }
    }
    var label = document.getElementById("slabel");
    label.innerHTML = "Staff: " + count + "/" + limit;
}
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

    var min_age = document.getElementById("min_age").value;
    var max_age = document.getElementById("max_age").value;
    var part = document.getElementById("part").value;
    var staff = document.getElementById("smlabel").value;
    var duration = document.getElementById("duration").value;
    var sdate = document.getElementById("sdate").value;
    if(new Date(sdate) <= new Date()) {
        alert("You have to choose a date in the future!");
        return false;
    }
    if(!isInteger(duration) || parseInt(duration) <= 0 || parseInt(duration) > 20) {
        alert("Duration must be an integer between 1 and 20");
        return false;
    }
    if(!isInteger(min_age) || parseInt(min_age) < 8 || parseInt(min_age) > 18) {
        alert("Minimum age must be between 8 and 18");
        return false;
    }
    if(!isInteger(max_age) || parseInt(max_age) < 9 || parseInt(max_age) > 20 || parseInt(max_age) <= parseInt(min_age)) {
        alert("Maximum age must be between 9 and 20 and greater than the minimum age");
        return false;
    }
    if(!isInteger(part) || parseInt(part) <= 30) {
        alert("Participant number must be an integer greater than 30");
        return false;
    }
    if(!isInteger(staff) || parseInt(staff) <= 10) {
        alert("Staff number must be an integer greater than 10");
        return false;
    }
    
    var location_id = document.getElementById("loc").value.split('_')[1];
    var response = await fetch('http://localhost:3000/get_location.php', {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({location_id: location_id}),
    });
    var result = await response.json();
    if(parseInt(result.beds) < parseInt(part) + parseInt(staff) + 1) {
        alert("Not enough beds at location");
        return false;
    }

    var coordinator_id = document.getElementById("coord").value.split('_')[1];
    var staff2 = document.getElementById("s_" + coordinator_id);
    if(staff2.checked === true) {
        alert("Coordinator cannot be staff member");
        return false;
    }

    var checkBoxes = document.querySelectorAll('input[type=checkbox]:checked');
    if(checkBoxes.length < parseInt(staff)) {
        alert("Not enough staff members");
        return false;
    }

    var camp_id = await createCamp(coordinator_id, location_id, part, staff, sdate, duration, min_age, max_age);

    for(var i = 0; i < checkBoxes.length; i++) {
        await createParticipation(camp_id, checkBoxes[i].id.split('_')[1]);
    }

    document.getElementById("message").innerHTML = "SUCCESS!";
}
async function createCamp(coordinator_id, location_id, part, staff, sdate, duration, min_age, max_age) {
    var response = await fetch('http://localhost:3000/create_camp.php', {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({coordinator_id: coordinator_id, location_id: location_id, part: part, staff: staff, sdate: sdate, duration: duration, min_age: min_age, max_age: max_age}),
    });
    var result = await response.json();
    return result.id;
}
async function createParticipation(camp_id, person_id) {
    var response = await fetch('http://localhost:3000/create_participation.php', {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({camp_id: camp_id, person_id: person_id, role: "staff"}),
    });
    var result = await response.json();
}
</script>