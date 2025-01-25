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

// Fetch cart_sales data from database
$query_cart_sales = "
    SELECT 
        cs.user_id AS users_id,
        cs.cart_id,
        cs.item_description,
        cs.quantity,
        cs.price,
        (cs.quantity * cs.price) AS total_price,
        cs.transaction_date
    FROM cart_sales cs
";
$result_cart_sales = mysqli_query($connection, $query_cart_sales);

// Initialize an array to store fetched rows
$cart_sales = [];
if (mysqli_num_rows($result_cart_sales) > 0) {
    while ($row = mysqli_fetch_assoc($result_cart_sales)) {
        $cart_sales[] = $row;
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
    <title>Admin Cart Sales</title>
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

        .cart-sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-sales-table th, .cart-sales-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .cart-sales-table th {
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
        <h2>Cart Sales</h2>
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
                    echo "<tr>";
                    echo "<td>{$sale['users_id']}</td>";
                    echo "<td>{$sale['cart_id']}</td>";
                    echo "<td>{$sale['item_description']}</td>";
                    echo "<td>{$sale['quantity']}</td>";
                    echo "<td>{$sale['price']}</td>";
                    echo "<td>{$sale['total_price']}</td>";
                    echo "<td>{$sale['transaction_date']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
