<?php
session_start();

if (!isset($_SESSION['user-session'])) {
    header("Location: index.php");
    exit();
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

// Get user ID from session
$user_id = intval($_SESSION['user-session']);

// Fetch the latest transaction details
$query = "
    SELECT * 
    FROM orders
    WHERE user_id = ? AND status = 'Pending'
    ORDER BY transaction_date DESC 
    LIMIT 1
";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Initialize variables to store transaction details and cart items
$transaction = null;
$cart_sales_result = null;

if ($result && mysqli_num_rows($result) > 0) {
    $transaction = mysqli_fetch_assoc($result);

    // Fetch order items related to the transaction
    $fetch_order_items_query = "
        SELECT item_description, quantity, price, (price * quantity) AS total_price
        FROM order_items
        WHERE order_id = ?
    ";
    $stmt_order_items = mysqli_prepare($connection, $fetch_order_items_query);
    mysqli_stmt_bind_param($stmt_order_items, 'i', $transaction['id']);
    mysqli_stmt_execute($stmt_order_items);
    $cart_sales_result = mysqli_stmt_get_result($stmt_order_items);

    // Close the prepared statement for fetching order items
    mysqli_stmt_close($stmt_order_items);

    // Fetch payment method description
    $fetch_payment_method_query = "
        SELECT method_name 
        FROM payment_methods 
        WHERE id = ?
    ";
    $stmt_payment_method = mysqli_prepare($connection, $fetch_payment_method_query);
    mysqli_stmt_bind_param($stmt_payment_method, 'i', $transaction['payment_method_id']);
    mysqli_stmt_execute($stmt_payment_method);
    $payment_method_result = mysqli_stmt_get_result($stmt_payment_method);

    $payment_method_name = '';
    if ($payment_method_row = mysqli_fetch_assoc($payment_method_result)) {
        $payment_method_name = $payment_method_row['method_name'];
    }

    // Close the prepared statement for payment method
    mysqli_stmt_close($stmt_payment_method);
} else {
    echo "No recent transactions found.";
}

// Close the prepared statement for fetching transaction details
mysqli_stmt_close($stmt);

// Close connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Purchase!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
        }
        p {
            font-size: 18px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thank You for Your Purchase!</h1>
        <?php if ($transaction) : ?>
            <p>Your payment has been successfully processed.</p>
            <p>Transaction Date: <?php echo htmlspecialchars($transaction['transaction_date']); ?></p>
            <p>Amount Paid: ₱<?php echo number_format($transaction['total_amount'], 2); ?></p>
            <p>Quantity: <?php echo htmlspecialchars($transaction['total_quantity']); ?></p>
            <p>Address: <?php echo htmlspecialchars($transaction['address']); ?></p>
            <p>Contact Number: <?php echo htmlspecialchars($transaction['phone']); ?></p>
            <p>Status: <?php echo htmlspecialchars($transaction['status']); ?></p>
            <p>Payment Method: <?php echo htmlspecialchars($payment_method_name); ?></p>
        <?php else: ?>
            <p>No recent transactions found.</p>
        <?php endif; ?>

        <!-- Order Items -->
        <h2>Order Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cart_sales_result && mysqli_num_rows($cart_sales_result) > 0) : ?>
                    <?php while ($item = mysqli_fetch_assoc($cart_sales_result)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>₱<?php echo number_format($item['total_price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No items found in your order.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Link to continue shopping -->
        <a href="home.php" class="btn">Continue Shopping</a>
    </div>
</body>
</html>
