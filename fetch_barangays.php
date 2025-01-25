<?php
$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;

$connection = mysqli_connect('localhost', 'root', '', 'yayawautorepairshop');

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT id, name FROM barangays WHERE city_id = ? ORDER BY name";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $city_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    }
} else {
    echo '<option value="">No barangays found</option>';
}

mysqli_stmt_close($stmt);
mysqli_close($connection);
?>
