<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit;
}

// Database configuration
$dbhost = 'localhost';
$dbusername = 'root';
$dbpassword = '';
$dbname = 'yayawautorepairshop'; 
$connection = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);

// Check connection
if (mysqli_connect_error()) {
    die('Failed to connect to database');
}

// Fetch contacts from database
$query = "SELECT id, user_email, subject, message, name FROM contact";
$result = mysqli_query($connection, $query);

// Initialize an array to store fetched rows
$contacts = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
    }
}

// Close database connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Contact</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            display: flex;
            min-height: 100vh; /* Ensure full height */
        }
        
        .admin-panel {
            width: 250px; /* Fixed width for admin panel */
            background-color: #000;
            color: white;
            display: flex;
            flex-direction: column; /* Stack items vertically */
            padding: 20px;
        }

        .admin-panel h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .admin-links {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .admin-links li {
            margin-bottom: 10px;
        }

        .admin-links a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s;
            border-radius: 4px;
        }

        .admin-links a:hover {
            background-color: #333;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .content h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .contact-table {
            width: 100%;
            border-collapse: collapse;
        }

        .contact-table th, .contact-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .contact-table th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <h2>Admin Panel</h2>
        <ul class="admin-links">
        <li><a href="admin_panel.php">Dashboard</a></li>
            <li><a href="admin_users.php">Manage Users</a></li>
            <li><a href="admin_contact.php">Manage Contact</a></li>
            <li><a href="admin_items.php">Manage Items</a></li>
            <li><a href="admin_cart_sales.php">Cart Sales</a></li>
            <li><a href="admin_orders.php">Manage Orders</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h2>Contact Messages</h2>
        <table class="contact-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Email</th>
                    <th>kind of service</th>
                    <th>Message</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($contacts as $contact) {
                    echo "<tr>";
                    echo "<td>{$contact['id']}</td>";
                    echo "<td>{$contact['user_email']}</td>";
                    echo "<td>{$contact['subject']}</td>";
                    echo "<td>{$contact['message']}</td>";
                    echo "<td>{$contact['name']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
