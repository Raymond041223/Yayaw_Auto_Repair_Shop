<?php
session_start();

if (!isset($_SESSION['user-session'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables with empty values
$name = $email = $province_id = $city_id = $barangay_id = $address = $apartment = $postcode = $phone = $payment_method = "";
$message = "";
$cart = null; // Ensure $cart is defined

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch and sanitize input values
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $province_id = isset($_POST['province']) ? intval($_POST['province']) : 0;
    $city_id = isset($_POST['city']) ? intval($_POST['city']) : 0;
    $barangay_id = isset($_POST['barangay']) ? intval($_POST['barangay']) : 0;
    $address = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';
    $apartment = isset($_POST['apartment']) ? htmlspecialchars(trim($_POST['apartment'])) : '';
    $postcode = isset($_POST['postcode']) ? htmlspecialchars(trim($_POST['postcode'])) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $payment_method = isset($_POST['payment_method']) ? htmlspecialchars(trim($_POST['payment_method'])) : '';

    // Fetch user ID from session
    $user_id = (int)$_SESSION['user-session'];

    // Database connection
    $connection = mysqli_connect('localhost', 'root', '', 'yayawautorepairshop');
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Save or update billing details
    $query = "INSERT INTO billing_details (user_id, address, province_id, city_id, barangay_id, postal_code)
              VALUES (?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE address = VALUES(address), province_id = VALUES(province_id), city_id = VALUES(city_id), barangay_id = VALUES(barangay_id), postal_code = VALUES(postal_code)";
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'issiii', $user_id, $address, $province_id, $city_id, $barangay_id, $postcode);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Proceed to payment
            header("Location: process_payment.php");
            exit();
        } else {
            echo "Error saving billing details: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($connection);
    }

    // Fetch cart details
    $cart_query = mysqli_query($connection, "SELECT id FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
    if ($cart_query && mysqli_num_rows($cart_query) > 0) {
        $cart = mysqli_fetch_assoc($cart_query);

        if ($cart) {
            $cart_id = $cart['id'];

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

            // Insert into `cart` table
            $insert_cart_query = "INSERT INTO cart (users_id, address, contact_number, payment_method, total_amount, total_quantity, transaction_date, flag_checkout, flag_shipped)
                                  VALUES (?, ?, ?, ?, ?, ?, NOW(), 1, 0)";
            $stmt_insert_cart = mysqli_prepare($connection, $insert_cart_query);
            mysqli_stmt_bind_param($stmt_insert_cart, 'issssi', $user_id, $address, $phone, $payment_method, $total_amount, $total_quantity);
            mysqli_stmt_execute($stmt_insert_cart);

            // Get the newly inserted cart ID
            $new_cart_id = mysqli_insert_id($connection);

            // Insert into `cart_items` table
            $insert_cart_items_query = "INSERT INTO cart_items (users_id, cart_id, item_code, item_description, price, quantity)
                                        SELECT users_id, ?, item_code, item_description, price, quantity
                                        FROM cart_items
                                        WHERE cart_id = ?";
            $stmt_insert_cart_items = mysqli_prepare($connection, $insert_cart_items_query);
            mysqli_stmt_bind_param($stmt_insert_cart_items, 'ii', $new_cart_id, $cart_id);
            mysqli_stmt_execute($stmt_insert_cart_items);

            // Clear the original cart items
            $clear_cart_items_query = "DELETE FROM cart_items WHERE cart_id = ?";
            $stmt_clear_cart_items = mysqli_prepare($connection, $clear_cart_items_query);
            mysqli_stmt_bind_param($stmt_clear_cart_items, 'i', $cart_id);
            mysqli_stmt_execute($stmt_clear_cart_items);

            // Redirect based on payment method
            if ($payment_method === 'cod') {
                header("Location: process_payment.php?payment_method=cod");
                exit();
            } else {
                // Directly proceed to thankyou.php for GCash or other payment methods
                header("Location: thankyou.php");
                exit();
            }
        }
    } else {
        $message = "<p class='centered'>No recent transactions found.</p>";
    }

    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        /* Your CSS styles go here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .centered {
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .billing-details, .order-summary, .payment-method {
            margin: 20px 0;
        }
        .billing-details form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .billing-details label {
            width: 100%;
            max-width: 400px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .billing-details input, .billing-details select {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            margin: 5px 0 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .billing-details input[type="submit"] {
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }
        .billing-details input[type="submit"]:hover {
            background: #0056b3;
        }
        .order-summary ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            max-width: 400px;
            margin: auto;
        }
        .order-summary li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .order-summary li:last-child {
            border-bottom: none;
        }
        .order-summary strong {
            font-size: 18px;
        }
        .payment-method {
            max-width: 800px;
            margin: auto;
        }
        .payment-options {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .payment-option {
            width: 48%;
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .payment-option input[type="radio"] {
            margin-right: 10px;
        }
        .payment-option img {
            max-width: 60px;
            height: auto;
        }
        .payment-method input[type="submit"] {
            background: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 10px 20px;
        }
        .payment-method input[type="submit"]:hover {
            background: #218838;
        }
    </style>
    <script>
        function updateCities(provinceId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_cities.php?province_id=' + provinceId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('city').innerHTML = this.responseText;
                    document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>'; // Clear barangays
                }
            };
            xhr.send();
        }

        function updateBarangays(cityId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_barangays.php?city_id=' + cityId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('barangay').innerHTML = this.responseText;
                }
            };
            xhr.send();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Load provinces on page load
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_provinces.php', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('province').innerHTML = this.responseText;
                }
            };
            xhr.send();
        });
    </script>
</head>
<body>
<div class="container">
    <div class="billing-details">
        <h2>Billing Details</h2>
        <form action="checkout.php" method="post">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            
            <label for="province">Province:</label>
            <select id="province" name="province" onchange="updateCities(this.value)" required>
                <option value="">Select Province</option>
                <!-- Options will be populated by JavaScript -->
            </select>
            
            <label for="city">City:</label>
            <select id="city" name="city" onchange="updateBarangays(this.value)" required>
                <option value="">Select City</option>
                <!-- Options will be populated by JavaScript -->
            </select>
            
            <label for="barangay">Barangay:</label>
            <select id="barangay" name="barangay" required>
                <option value="">Select Barangay</option>
                <!-- Options will be populated by JavaScript -->
            </select>
            
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            
            <label for="apartment">Apartment/Suite:</label>
            <input type="text" id="apartment" name="apartment" value="<?php echo htmlspecialchars($apartment); ?>">
            
            
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            
            <div class="payment-method">
                <h2>Payment Method</h2>
                <div class="payment-options">
                    <div class="payment-option">
                        <input type="radio" id="gcash" name="payment_method" value="gcash" <?php if ($payment_method == 'gcash') echo 'checked'; ?> required>
                        <label for="gcash">
                            GCash
                        </label>
                        <img src="image/GCash.png" alt="GCash">
                    </div>
                    <div class="payment-option">
                        <input type="radio" id="cod" name="payment_method" value="cod" <?php if ($payment_method == 'cod') echo 'checked'; ?> required>
                        <label for="cod">
                            Cash on Delivery
                        </label>
                        <img src="image/COD.webp" alt="Cash on Delivery">
                    </div>
                </div>
                
                <input type="hidden" name="redirect_to" value="process_payment.php">
                <input type="submit" value="Place Order">
            </div>
        </form>
        <?php echo $message; ?>
    </div>

    <?php if ($cart): ?>
    <div class="order-summary">
        <h2>Order Summary</h2>
        <ul>
            <?php while ($item = mysqli_fetch_assoc($cart_sales_result)): ?>
            <li>
                <?php echo htmlspecialchars($item['item_description']); ?>
                <strong><?php echo number_format($item['price'], 2); ?> x <?php echo (int)$item['quantity']; ?></strong>
                <span><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
