<?php
session_start();

// Check if user session is set
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

// Get payment method from query parameter
$payment_method = isset($_GET['payment_method']) ? htmlspecialchars(trim($_GET['payment_method'])) : '';

$valid_payment_methods = ['gcash', 'cod'];

// Fetch user ID from session
$user_id = (int)$_SESSION['user-session'];

// Fetch cart details
$cart_query = mysqli_query($connection, "SELECT id FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
$cart = mysqli_fetch_assoc($cart_query);

if ($cart) {
    $cart_id = $cart['id'];

    // Fetch billing details (replace with actual fetching from session or form)
    $address = 'Example Address'; // Replace with actual value
    $apartment = 'Apt 1'; // Replace with actual value
    $town_city = 'City'; // Replace with actual value
    $postcode = '12345'; // Replace with actual value
    $phone = '123-456-7890'; // Replace with actual value

    // Calculate total amount and quantity
    $total_amount = 0;
    $total_quantity = 0;

    $fetch_cart_sales_query = "SELECT item_code, item_description, price, quantity FROM cart_items WHERE cart_id = ?";
    $stmt_cart_sales = mysqli_prepare($connection, $fetch_cart_sales_query);
    mysqli_stmt_bind_param($stmt_cart_sales, 'i', $cart_id);
    mysqli_stmt_execute($stmt_cart_sales);
    $cart_sales_result = mysqli_stmt_get_result($stmt_cart_sales);

    while ($item = mysqli_fetch_assoc($cart_sales_result)) {
        $total_amount += $item['price'] * $item['quantity'];
        $total_quantity += $item['quantity'];
    }

    // Fetch payment method ID
    $fetch_payment_method_id_query = "SELECT id FROM payment_methods WHERE method_name = ?";
    $stmt_payment_method = mysqli_prepare($connection, $fetch_payment_method_id_query);
    mysqli_stmt_bind_param($stmt_payment_method, 's', $payment_method);
    mysqli_stmt_execute($stmt_payment_method);
    $payment_method_result = mysqli_stmt_get_result($stmt_payment_method);

    if ($payment_method_row = mysqli_fetch_assoc($payment_method_result)) {
        $payment_method_id = $payment_method_row['id'];
    } else {
        // Redirect to thankyou.php if payment method is not valid
        header("Location: thankyou.php");
        exit();
    }

    // Insert into `orders` table
    $insert_order_query = "INSERT INTO orders (user_id, address, apartment, town_city, postcode, phone, payment_method_id, total_amount, total_quantity, status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt_insert_order = mysqli_prepare($connection, $insert_order_query);
    mysqli_stmt_bind_param($stmt_insert_order, 'isssssiii', $user_id, $address, $apartment, $town_city, $postcode, $phone, $payment_method_id, $total_amount, $total_quantity);
    
    if (!mysqli_stmt_execute($stmt_insert_order)) {
        die('Execute failed: ' . mysqli_stmt_error($stmt_insert_order));
    }

    // Get the newly inserted order ID
    $new_order_id = mysqli_insert_id($connection);

    // Insert into `order_items` table
    $insert_order_items_query = "INSERT INTO orders (order_id, item_code, item_description, price, quantity)
                                 SELECT ?, item_code, item_description, price, quantity
                                 FROM cart_items
                                 WHERE cart_id = ?";
    $stmt_insert_order_items = mysqli_prepare($connection, $insert_order_items_query);
    mysqli_stmt_bind_param($stmt_insert_order_items, 'ii', $new_order_id, $cart_id);
    
    if (!mysqli_stmt_execute($stmt_insert_order_items)) {
        die('Execute failed: ' . mysqli_stmt_error($stmt_insert_order_items));
    }

    

    // Redirect to thankyou.php
    header("Location: thankyou.php");
    exit();
} else {
    die("No recent transactions found.");
}

// Close connection
mysqli_close($connection);
?>
