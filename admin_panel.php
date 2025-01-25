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
$query_contacts = "SELECT id, user_email, subject, message, name FROM contact ORDER BY id DESC LIMIT 5";
$result_contacts = mysqli_query($connection, $query_contacts);

// Initialize an array to store fetched rows
$contacts = [];
if (mysqli_num_rows($result_contacts) > 0) {
    while ($row = mysqli_fetch_assoc($result_contacts)) {
        $contacts[] = $row;
    }
}

// Fetch the latest cart sales data
$cart_sales_query = "
    SELECT 
        cs.user_id AS users_id,
        cs.cart_id,
        cs.item_description,
        cs.quantity,
        cs.price,
        (cs.quantity * cs.price) AS total_price,
        cs.transaction_date
    FROM cart_sales cs
    ORDER BY cs.transaction_date DESC
    LIMIT 5
";
$cart_sales_result = mysqli_query($connection, $cart_sales_query);

// Initialize an array to store the latest cart sales
$cart_sales = [];
if (mysqli_num_rows($cart_sales_result) > 0) {
    while ($row = mysqli_fetch_assoc($cart_sales_result)) {
        $cart_sales[] = $row;
    }
}

// Fetch the latest 5 users
$user_query = "SELECT id, first_name, last_name, email FROM users ORDER BY id DESC LIMIT 5";
$user_result = mysqli_query($connection, $user_query);

// Initialize an array to store the latest users
$latest_users = [];
if (mysqli_num_rows($user_result) > 0) {
    while ($row = mysqli_fetch_assoc($user_result)) {
        $latest_users[] = $row;
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
    <title>Admin Panel</title>
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

        .contact-table, .cart-sales-table, .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .contact-table th, .contact-table td,
        .cart-sales-table th, .cart-sales-table td,
        .user-table th, .user-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .contact-table th, .cart-sales-table th, .user-table th {
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
        <h2>Latest Contact Messages</h2>
        <table class="contact-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Email</th>
                    <th>Subject</th>
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

        <h2>Latest Cart Sales</h2>
        <table class="cart-sales-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Cart ID</th>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($cart_sales as $sale) {
                    $formatted_date = date('F j, Y, g:i a', strtotime($sale['transaction_date']));
                    echo "<tr>";
                    echo "<td>{$sale['users_id']}</td>";
                    echo "<td>{$sale['cart_id']}</td>";
                    echo "<td>{$sale['item_description']}</td>";
                    echo "<td>{$sale['quantity']}</td>";
                    echo "<td>₱" . number_format($sale['price'], 2) . "</td>";
                    echo "<td>₱" . number_format($sale['total_price'], 2) . "</td>";
                    echo "<td>{$formatted_date}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Latest Users</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($latest_users as $user) {
                    echo "<tr>";
                    echo "<td>{$user['id']}</td>";
                    echo "<td>{$user['first_name']}</td>";
                    echo "<td>{$user['last_name']}</td>";
                    echo "<td>{$user['email']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
