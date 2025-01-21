<!DOCTYPE html>
<html>
<head>

<style>
table, th, td {
    border:1px solid black;
}
</style>
<?php $db = pg_connect("host=localhost port=5432 dbname='CampManager' user=postgres password=postgres"); ?>
</head>
<body>

<h1>Camp Manager Application</h1>
<ul>
    <li><a href="index.php">View</a></li>
    <li><a href="create.php">Create</a></li>
    <li><a href="update.php">Update</a></li>
    <li><a href="delete.php">Delete</a></li>
</ul>
<h2>Camp Update</h2>
<table style="width:100%" class="content">
    <tr>
        <th>Id</th>
        <th>Period</th>
        <th>Coordinator</th>
        <th>Location</th>
        <th>Age</th>
        <th>Participants</th>
        <th>Staff Members</th>
    </tr>

    <?php
    
    $query = "select start_date, duration, CONCAT(p.first_name, ' ', p.last_name), l.address, min_age, max_age, participant_nr, staff_nr, camp_id 
              from camp c join person p on coordinator_id = p.person_id
                        join location l on c.location_id = l.location_id 
              order by camp_id";
    $result = pg_query($db, $query);
    while ($row = pg_fetch_row($result)) {
        echo "<tr>";
        echo "<td>$row[8]</td>";
        $date = date_create($row[0]);
        date_add($date, date_interval_create_from_date_string((intval($row[1], 10) - 1) . " days"));
        echo "<td>$row[0] -> ";
        echo date_format($date, "Y-m-d");
        echo "</td>";
        echo "<td>$row[2]</td>";
        echo "<td>$row[3]</td>";
        echo "<td>$row[4] -> $row[5]</td>";
        echo "<td>$row[6]</td>";
        echo "<td>$row[7]</td>";
        echo "</tr>";
    }
    ?>

</table>
<div id="div"></div>
<form action="update_camp.php" method="POST">
    <label>Id:</label>
    <input type="text" name="id" id="id"><br>
    <input type="submit" value="Submit">
</form>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const content = document.querySelector('.content'); 
    const itemsPerPage = 10;
    let currentPage = 0;
    const items = Array.from(content.getElementsByTagName('tr')).slice(1);
    function showPage(page) {
        const startIndex = page * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        items.forEach((item, index) => {
            item.classList.toggle('hidden', index < startIndex || index >= endIndex);
        });
        updateActiveButtonStates();
    }

    function createPageButtons() {
        const totalPages = Math.ceil(items.length / itemsPerPage);
        const paginationDiv = document.getElementById("div");
        paginationDiv.classList.add('pagination');

        const firstButton = document.createElement('button');
        firstButton.textContent = "<<";
        firstButton.addEventListener('click', () => {
            currentPage = 0;
            showPage(currentPage);
            updateActiveButtonStates();
        });
        paginationDiv.appendChild(firstButton);

        const prevButton = document.createElement('button');
        prevButton.textContent = "<";
        prevButton.addEventListener('click', () => {
            if(currentPage > 0) {
                currentPage--;
                showPage(currentPage);
                updateActiveButtonStates();
            }
        });
        paginationDiv.appendChild(prevButton);

        for (let i = 0; i < totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i + 1;
            pageButton.addEventListener('click', () => {
                currentPage = i;
                showPage(currentPage);
                updateActiveButtonStates();
            });
            paginationDiv.appendChild(pageButton);
        }

        const nextButton = document.createElement('button');
        nextButton.textContent = ">";
        nextButton.addEventListener('click', () => {
            if(currentPage < totalPages - 1) {
                currentPage++;
                showPage(currentPage);
                updateActiveButtonStates();
            }
        });
        paginationDiv.appendChild(nextButton);

        const lastButton = document.createElement('button');
        lastButton.textContent = ">>";
        lastButton.addEventListener('click', () => {
            currentPage = totalPages - 1;
            showPage(currentPage);
            updateActiveButtonStates();
        });
        paginationDiv.appendChild(lastButton);
    }
    createPageButtons();
    showPage(currentPage);

    function updateActiveButtonStates() {
        const pageButtons = document.querySelectorAll('.pagination button');
        pageButtons.forEach((button, index) => {
            if (index - 2 === currentPage) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }
});


</script>
<style>
.hidden {
  display: none;
}
.pagination {
  text-align: center;
  margin-top: 20px;
}
.pagination button {
  padding: 5px 10px;
  margin: 0 5px;
}
.pagination button.active {
  background-color:rgb(165, 165, 165);
  border: none;
}
</style>
