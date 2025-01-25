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

// Handle form submission for adding new item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $code = mysqli_real_escape_string($connection, $_POST['code']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $price = mysqli_real_escape_string($connection, $_POST['price']);
    $imageurl = mysqli_real_escape_string($connection, $_POST['imageurl']);

    // Check for duplicate code
    $query_check_code = "SELECT id FROM items WHERE code='$code'";
    $result_check_code = mysqli_query($connection, $query_check_code);

    if (mysqli_num_rows($result_check_code) > 0) {
        $error_message = "Item code already exists. Please choose a different code.";
    } else {
        // Check for duplicate description
        $query_check_description = "SELECT id FROM items WHERE description='$description'";
        $result_check_description = mysqli_query($connection, $query_check_description);

        if (mysqli_num_rows($result_check_description) > 0) {
            $error_message = "Item description already exists. Please choose a different description.";
        } else {
            // Insert new item if no duplicates found
            $query_add_item = "INSERT INTO items (code, description, price, imageurl) VALUES ('$code', '$description', '$price', '$imageurl')";
            if (mysqli_query($connection, $query_add_item)) {
                $success_message = "New item added successfully.";
            } else {
                $error_message = "Error adding item: " . mysqli_error($connection);
            }
        }
    }
}

// Handle item deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_item'])) {
    $item_id = mysqli_real_escape_string($connection, $_POST['item_id']);
    
    $query_delete_item = "DELETE FROM items WHERE id='$item_id'";
    if (mysqli_query($connection, $query_delete_item)) {
        $success_message = "Item deleted successfully.";
    } else {
        $error_message = "Error deleting item: " . mysqli_error($connection);
    }
}

// Fetch items from database
$query_items = "SELECT * FROM items";
$result_items = mysqli_query($connection, $query_items);

// Initialize an array to store fetched rows
$items = [];
if (mysqli_num_rows($result_items) > 0) {
    while ($row = mysqli_fetch_assoc($result_items)) {
        $items[] = $row;
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
    <title>Admin Items</title>
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

        .item-table, .add-item-form {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table th, .item-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .item-table th {
            background-color: #f0f0f0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-group button {
            padding: 10px 15px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #555;
        }

        .error-message, .success-message {
            color: red;
            margin-bottom: 15px;
        }

        .success-message {
            color: green;
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
        <h2>Manage Items</h2>

        <!-- Form for Adding New Item -->
        <h3>Add New Item</h3>
        <?php
        if (isset($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        if (isset($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        ?>
        <form method="POST" action="admin_items.php" class="add-item-form">
            <div class="form-group">
                <label for="code">Item Code:</label>
                <input type="text" id="code" name="code" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="imageurl">Image URL:</label>
                <input type="text" id="imageurl" name="imageurl" required>
            </div>
            <div class="form-group">
                <button type="submit" name="add_item">Add Item</button>
            </div>
        </form>

        <!-- Items Table -->
        <h3>Existing Items</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($items as $item) {
                    echo "<tr>";
                    echo "<td>{$item['id']}</td>";
                    echo "<td>{$item['code']}</td>";
                    echo "<td>{$item['description']}</td>";
                    echo "<td>₱" . number_format($item['price'], 2) . "</td>";
                    echo "<td>{$item['imageurl']}</td>";
                    echo "<td>
                            <form method='POST' action='admin_items.php' style='display:inline;'>
                                <input type='hidden' name='item_id' value='{$item['id']}'>
                                <button type='submit' name='delete_item' onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
