<?php
$connection = mysqli_connect('localhost', 'root', '', 'yayawautorepairshop');

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT id, name FROM provinces ORDER BY name";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    }
} else {
    echo '<option value="">No provinces found</option>';
}

mysqli_close($connection);
?>
