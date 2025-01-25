<?php
session_start();

if (!isset($_SESSION['user-session']))
  header("Location: index.php");

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

// Handle Add to Cart Logic
if (isset($_POST['add_to_cart'])) {
    $product_code = mysqli_real_escape_string($connection, $_POST['product_code']);
    $product_name = mysqli_real_escape_string($connection, $_POST['product_name']);
    $product_price = (float)$_POST['product_price'];
    $user_id = (int)$_SESSION['user-session'];

    // Check if a cart exists for the user
    $cart_query = mysqli_query($connection, "SELECT * FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
    if (mysqli_num_rows($cart_query) === 0) {
        // Create a new cart
        mysqli_query($connection, "INSERT INTO cart (users_id, address, contact_number, payment_method, total_amount, total_quantity, transaction_date, flag_checkout, flag_shipped) VALUES ($user_id, '', '', '', 0, 0, NULL, 0, 0)");
        $cart_id = mysqli_insert_id($connection);
    } else {
        $cart = mysqli_fetch_assoc($cart_query);
        $cart_id = $cart['id'];
    }

    // Check if item already exists in cart_items for this cart
    $existing_cart_item_query = mysqli_query($connection, "SELECT * FROM cart_items WHERE cart_id = $cart_id AND item_code = '$product_code'");
    if (mysqli_num_rows($existing_cart_item_query) > 0) {
        // If item already exists, update quantity
        $existing_cart_item = mysqli_fetch_assoc($existing_cart_item_query);
        $new_quantity = $existing_cart_item['quantity'] + 1;
        mysqli_query($connection, "UPDATE cart_items SET quantity = $new_quantity WHERE item_code = '$product_code' AND cart_id = $cart_id");
    } else {
        // If item does not exist, insert new item
        mysqli_query($connection, "INSERT INTO cart_items (users_id, cart_id, item_code, item_description, price, quantity) VALUES ($user_id, $cart_id, '$product_code', '$product_name', $product_price, 1)");
    }

    // Redirect to the same page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Remove from cart logic
if (isset($_POST['remove_from_cart'])) {
    $product_code = mysqli_real_escape_string($connection, $_POST['product_code']);
    $user_id = (int)$_SESSION['user-session'];

    // Get the cart id for this user
    $cart_query = mysqli_query($connection, "SELECT id FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
    $cart = mysqli_fetch_assoc($cart_query);
    $cart_id = $cart['id'];

    // Remove item from cart_items
    mysqli_query($connection, "DELETE FROM cart_items WHERE item_code = '$product_code' AND cart_id = $cart_id LIMIT 1");

    // Redirect to prevent form resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Clear cart logic
if (isset($_POST['clear_cart'])) {
    $user_id = (int)$_SESSION['user-session'];

    // Get the cart id for this user
    $cart_query = mysqli_query($connection, "SELECT id FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
    $cart = mysqli_fetch_assoc($cart_query);
    $cart_id = $cart['id'];

    // Remove all items from cart_items
    mysqli_query($connection, "DELETE FROM cart_items WHERE cart_id = $cart_id");

    // Redirect to prevent form resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Confirm payment logic
if (isset($_POST['confirm_payment'])) {
    $user_id = (int)$_SESSION['user-session'];

    // Get the cart id for this user
    $cart_query = mysqli_query($connection, "SELECT id FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
    $cart = mysqli_fetch_assoc($cart_query);

    if ($cart) {
        $cart_id = $cart['id'];

        // Check if the cart has items
        $cart_items_query = mysqli_query($connection, "SELECT * FROM cart_items WHERE cart_id = $cart_id");
        if (mysqli_num_rows($cart_items_query) > 0) {
            // Redirect to checkout
            header('Location: checkout.php');
            exit();
        } else {
            // Set an error message for empty cart
            $_SESSION['checkout_error'] = 'Your cart is empty. Please add items before checking out.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        // Set an error message for no active cart
        $_SESSION['checkout_error'] = 'No active cart found. Please try again.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch products from the database
$product_result = mysqli_query($connection, "SELECT * FROM items");

// Fetch items in cart
$user_id = (int)$_SESSION['user-session'];
$cart_query = mysqli_query($connection, "SELECT id FROM cart WHERE users_id = $user_id AND flag_checkout = 0");
$cart = mysqli_fetch_assoc($cart_query);
$cart_id = $cart['id'] ?? 0;
$cart_result = mysqli_query($connection, "SELECT * FROM cart_items WHERE cart_id = $cart_id");

// Calculate total amount in cart
$total_amount = 0;
$total_quantity = 0;
$cart_items = [];
if ($cart_result && mysqli_num_rows($cart_result) > 0) {
    while ($record = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $record;
        $item_total = $record['price'] * $record['quantity'];
        $total_amount += $item_total;
        $total_quantity += $record['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="cart.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <style>

         /* Ensure header has a black background */
#header {
    width: 100%;
    text-align: center;
    background-color: #000000; /* Black background */
    padding: 5px;
    box-shadow: 0 5px 10px 0px #dbd7d7;
}

#header img {
    max-width: 30px;
}

        /* Add background image for the entire page, including below the header */
body {
    background: url('image/background\ for\ cart.jfif') no-repeat center center fixed;
    background-size: cover; /* Ensures the background image covers the entire page */
}

/* To ensure the background image appears behind content */
.wrapper, .columns {
    background: rgba(255, 255, 255, 0.8); /* Slightly transparent white background */
    padding: 15px;
    border-radius: 8px; /* Optional: for rounded corners */
}
        .product-card {
            margin: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: #fff;
            text-align: center;
        }
        .product-image {
            width: 100%;
            height: 188px;
            object-fit: cover;
            margin: 10px auto;
        }
        /* Dropdown menu adjustments */
        .dropdown-menu {
            padding: 0; /* Remove default padding */
            top: 50px !important; /* Positioning adjustments */
            width: 350px !important; /* Fixed width */
            left: -253px !important; /* Position adjustments */
            box-shadow: 0px 5px 30px black; /* Shadow effect */
        }
        /* Container for cart items in the dropdown */
        .cart-detail {
            display: flex;
            align-items: center;
            padding: 10px; /* Space inside each item */
            border-bottom: 1px solid #ddd; /* Bottom border */
            box-sizing: border-box;
        }
        /* Ensure image size is consistent */
        .cart-detail-img img {
            height: 71px !important; 
            width: 65px !important;
            object-fit: cover; /* Ensure images cover the area */
        }
        /* Product details styling */
        .cart-detail-product {
            flex: 1; /* Allow the details to take remaining space */
            margin-left: 10px; /* Space between image and text */
        }
        /* Remove button styling */
        .remove-button {
            flex-shrink: 0; /* Prevent the button from shrinking */
            margin-left: auto; /* Push button to the right */
        }
        /* Remove button styling */
        .remove-button button {
            width: 100px; /* Fixed width for consistency */
            height: 30px; /* Fixed height for consistency */
            background-color: #dc3545; /* Bootstrap red color */
            color: #fff; /* White text */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            box-shadow: none !important; /* Remove shadow */
            display: flex; /* Use flexbox for centering */
            align-items: center; /* Center items vertically */
            justify-content: center; /* Center items horizontally */
            font-size: 14px; /* Adjust font size as needed */
        }
        /* Adjust button on hover */
        .remove-button button:hover,
        .remove-button button:focus {
            opacity: 0.9; /* Slightly transparent on hover */
        }
        /* Adjust dropdown menu arrow */
        .dropdown-menu:before {
            content: " ";
            position: absolute;
            top: -10px; /* Adjust positioning */
            right: 10px; /* Adjust positioning */
            border: 10px solid transparent;
            border-bottom-color: #fff; /* Arrow color */
        }
    </style>
</head>
<body class="bg-info">
<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <a class="navbar-brand" href="#">YAYAW AUTO REPAIR SHOP</a>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="home.php">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
        </li>
        <li class="nav-item dropdown">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-shopping-cart" aria-hidden="true"></i> Cart <span class="badge badge-pill badge-danger"><?= $total_quantity ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php if (empty($cart_items)): ?>
                    <p class="dropdown-item">Your cart is empty</p>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-detail">
                        <div class="cart-detail-img">
                            <?php
                            $imageURL = !empty($item['item_image']) ? htmlspecialchars($item['item_image']) : 'https://via.placeholder.com/150';
                            ?>
                            <img src="<?= $imageURL ?>" alt="<?= htmlspecialchars($item['item_description']) ?>" class="img-fluid">
                        </div>
                        <div class="cart-detail-product">
                            <p><?= htmlspecialchars($item['item_description']) ?></p>
                            <span class="price text-info">₱<?= number_format($item['price'], 2) ?></span>
                            <span class="count"> Quantity: <?= $item['quantity'] ?></span>
                        </div>
                        <div class="remove-button">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_code" value="<?= htmlspecialchars($item['item_code']) ?>">
                                <button class="btn btn-danger btn-sm" name="remove_from_cart">Remove</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <p>Total: <span class="text-info">₱<?= number_format($total_amount, 2) ?></span></p>
                        <form method="POST">
                            <button class="btn btn-primary btn-block" name="clear_cart">Clear Cart</button>
                            <button class="btn btn-primary btn-block" name="confirm_payment">Checkout</button>
                        </form>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</nav>

<div class="container mt-5">
    <!-- Display error message -->
    <?php if (isset($_SESSION['checkout_error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['checkout_error']) ?>
        </div>
        <?php unset($_SESSION['checkout_error']); ?>
    <?php endif; ?>

    <!-- Product listing -->
    <div class="row">
        <?php while ($product = mysqli_fetch_assoc($product_result)): ?>
        <div class="col-lg-3 col-md-5 col-sm-3 mb-3">
            <div class="product-card card">
                <?php
                // Fetch image URL directly from the database
                $imageURL = !empty($product['imageurl']) ? htmlspecialchars($product['imageurl']) : '?';
                ?>
                <img src="<?= $imageURL ?>" class="product-image" alt="<?= htmlspecialchars($product['description']) ?>">

                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($product['description']) ?></h5>
                    <p class="card-text"><strong>₱<?= number_format($product['price'], 2) ?></strong></p>
                    <form method="POST">
                        <input type="hidden" name="product_code" value="<?= htmlspecialchars($product['code']) ?>">
                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['description']) ?>">
                        <input type="hidden" name="product_price" value="<?= htmlspecialchars($product['price']) ?>">
                        <button class="btn btn-primary" name="add_to_cart">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>