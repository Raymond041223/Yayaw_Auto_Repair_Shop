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

// Function to cancel order
function cancelOrder($connection, $cartId)
{
    // Delete the order from cart
    $delete_query = "DELETE FROM cart WHERE id = ?";
    $stmt_delete = mysqli_prepare($connection, $delete_query);
    mysqli_stmt_bind_param($stmt_delete, "i", $cartId);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);

    // Also delete items associated with this cart
    $delete_items_query = "DELETE FROM cart_items WHERE cart_id = ?";
    $stmt_delete_items = mysqli_prepare($connection, $delete_items_query);
    mysqli_stmt_bind_param($stmt_delete_items, "i", $cartId);
    mysqli_stmt_execute($stmt_delete_items);
    mysqli_stmt_close($stmt_delete_items);

    // Also delete records from cart_sales
    $delete_sales_query = "DELETE FROM cart_sales WHERE cart_id = ?";
    $stmt_delete_sales = mysqli_prepare($connection, $delete_sales_query);
    mysqli_stmt_bind_param($stmt_delete_sales, "i", $cartId);
    mysqli_stmt_execute($stmt_delete_sales);
    mysqli_stmt_close($stmt_delete_sales);
}

// Function to ship order
function shipOrder($connection, $cartId)
{
    // Update the cart flag_shipped to true
    $update_query = "UPDATE cart SET flag_shipped = 1 WHERE id = ?";
    $stmt_update = mysqli_prepare($connection, $update_query);
    mysqli_stmt_bind_param($stmt_update, "i", $cartId);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
}

// Handle cancel order action
if (isset($_POST['cancel_order'])) {
    $cartId = $_POST['cart_id'];
    cancelOrder($connection, $cartId);
}

// Handle ship order action
if (isset($_POST['ship_order'])) {
    $cartId = $_POST['cart_id'];
    shipOrder($connection, $cartId);
}

// Fetch orders from cart table where flag_shipped is 0 (not yet shipped)
$query = "
    SELECT 
        id AS cart_id,
        users_id,
        address,
        contact_number,
        total_quantity,
        total_amount,
        transaction_date
    FROM cart
    WHERE flag_shipped = 0
";

$result = mysqli_query($connection, $query);

// Initialize an array to store fetched orders
$carts = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $carts[] = $row;
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
    <title>Orders - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            display: flex;
            min-height: 100vh;
        }

        .admin-panel {
            width: 250px;
            background-color: #000;
            color: white;
            display: flex;
            flex-direction: column;
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

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .order-table th,
        .order-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .order-table th {
            background-color: #f0f0f0;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .order-actions button {
            padding: 6px 12px;
            cursor: pointer;
            border: none;
            outline: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .order-actions button:hover {
            background-color: #333;
            color: white;
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
        <h2>Orders</h2>
        <table class="order-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Address</th>
                    <th>Contact Number</th>
                    <th>Total Quantity</th>
                    <th>Total Amount</th>
                    <th>Transaction Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carts as $cart) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cart['users_id']); ?></td>
                        <td><?php echo htmlspecialchars($cart['address']); ?></td>
                        <td><?php echo htmlspecialchars($cart['contact_number']); ?></td>
                        <td><?php echo htmlspecialchars($cart['total_quantity']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($cart['total_amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($cart['transaction_date']); ?></td>
                        <td class="order-actions">
                            <form method="post" action="">
                                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart['cart_id']); ?>">
                                <button type="submit" name="cancel_order">Cancel Order</button>
                            </form>
                            <form method="post" action="">
                                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart['cart_id']); ?>">
                                <button type="submit" name="ship_order">Ship Order</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
