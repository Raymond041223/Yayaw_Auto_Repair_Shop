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

// Fetch users from database
$query_users = "SELECT * FROM users";
$result_users = mysqli_query($connection, $query_users);

// Initialize an array to store fetched rows
$users = [];
if (mysqli_num_rows($result_users) > 0) {
    while ($row = mysqli_fetch_assoc($result_users)) {
        $users[] = $row;
    }
}

// Handle delete user request
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    mysqli_query($connection, "DELETE FROM users WHERE id = $user_id");
    header("Location: admin_users.php");
    exit;
}

// Handle add user request
if (isset($_POST['add_user'])) {
    $first_name = mysqli_real_escape_string($connection, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($connection, $_POST['last_name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password

    // Check if email already exists
    $query_check_email = "SELECT * FROM users WHERE email='$email'";
    $result_check_email = mysqli_query($connection, $query_check_email);

    if (mysqli_num_rows($result_check_email) > 0) {
        $email_error = "Email already exists!";
    } else {
        mysqli_query($connection, "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')");
        header("Location: admin_users.php");
        exit;
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
    <title>Admin Panel - Manage Users</title>
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

        .user-table, .form-container {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th, .user-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .user-table th {
            background-color: #f0f0f0;
        }

        .form-container {
            margin: 20px 0;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-container input[type=text],
        .form-container input[type=email],
        .form-container input[type=password] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .form-container button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #0056b3;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn.delete {
            background-color: #dc3545;
        }

        .btn.delete:hover {
            background-color: #c82333;
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            margin-bottom: 10px;
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
        <h2>Manage Users</h2>
        
        <!-- Add User Form -->
        <div class="form-container">
            <h3>Add New User</h3>
            <form method="POST">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" name="add_user">Add User</button>
                <?php if (isset($email_error)) { ?>
                    <p class="error"><?php echo $email_error; ?></p>
                <?php } ?>
            </form>
        </div>

        <!-- Users Table -->
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>{$user['id']}</td>";
                    echo "<td>{$user['first_name']}</td>";
                    echo "<td>{$user['last_name']}</td>";
                    echo "<td>{$user['email']}</td>";
                    echo "<td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='user_id' value='{$user['id']}'>
                            <button type='submit' name='delete_user' class='btn delete'>Delete</button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        
    </div>

    <script>
        function populateUpdateForm(id, firstName, lastName, email) {
            document.getElementById('update_user_id').value = id;
            document.getElementById('update_first_name').value = firstName;
            document.getElementById('update_last_name').value = lastName;
            document.getElementById('update_email').value = email;
            document.getElementById('update-form').style.display = 'block';
        }
    </script>
</body>
</html>
