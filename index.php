<?php
session_start();

// Function to validate password strength
function validatePassword($pwd, &$errors) {
    $errors_init = $errors;

    if (strlen($pwd) < 8) {
        $errors[] = "Password is too short (must be at least 8 characters).";
    }

    if (!preg_match("#[0-9]+#", $pwd)) {
        $errors[] = "Password must include at least one number.";
    }

    if (!preg_match("#[a-zA-Z]+#", $pwd)) {
        $errors[] = "Password must include at least one letter.";
    }

    return ($errors == $errors_init);
}

// Function to hash password securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Database connection configuration
$dbhost = 'localhost';
$dbusername = 'root';
$dbpassword = '';
$dbname = 'yayawautorepairshop';

// Error message initialization
$errorMessage = '';

// Handle registration process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Establish database connection
    $connection = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);
    
    // Check connection
    if (mysqli_connect_error()) {
        $errorMessage = 'Failed to connect to database';
    } else {
        // Sanitize user inputs
        $first_name = mysqli_real_escape_string($connection, $_POST['first-name']);
        $last_name = mysqli_real_escape_string($connection, $_POST['last-name']);
        $email = mysqli_real_escape_string($connection, $_POST['emails']);
        $password = mysqli_real_escape_string($connection, $_POST['password']);

        // Validate password strength
        $errors = [];
        if (!validatePassword($password, $errors)) {
            $errorMessage = implode("<br>", $errors);
        } else {
            // Hash password
            $hashed_password = hashPassword($password);

            // Check if email already exists
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $record = mysqli_fetch_assoc($result);

            if (!empty($record)) {
                $errorMessage = 'Email already exists. Please use a different email address.';
            } else {
                // Insert user data into database
                $query = "INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "ssss", $email, $hashed_password, $first_name, $last_name);
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    $_SESSION['user-session'] = mysqli_insert_id($connection);
                    header('Location: home.php');
                    exit();
                } else {
                    $errorMessage = 'Registration failed. Please try again later.';
                }
            }
        }

        // Close statement and connection

        mysqli_close($connection);
    }
}

// Handle login process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Establish database connection
    $connection = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);

    // Check connection
    if (mysqli_connect_error()) {
        $errorMessage = 'Failed to connect to database';
    } else {
        // Sanitize user inputs
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $password = mysqli_real_escape_string($connection, $_POST['password']);

        // Fetch user data based on email
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $record = mysqli_fetch_assoc($result);

        if ($record) {
            // Verify hashed password
            if (password_verify($password, $record['password'])) {
                $_SESSION['user-session'] = $record['id'];
                header('Location: home.php');
                exit();
            } else {
                $errorMessage = 'Incorrect email or password.';
            }
        } else {
            $errorMessage = 'Incorrect email or password.';
        }

        

        // Close statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="login.css">
    <title>Login & Registration</title>
</head>
<body>
<div class="wrapper">
    <nav class="navigations">
        <div class="navigations-logo">
            <p>Yayaw Auto Repair Shop</p>
        </div>
        <div class="navigations-menu" id="navigationsMenu">
        </div>
        <div class="navigations-button">
            <button class="btn white-btn" id="loginBtn" class="a" href="home.html" onclick="login()">Sign In</button>
            <button class="btn" id="registerBtn" onclick="register()">Sign Up</button>
            <button class="btn admin-btn" onclick="goToAdmin()">Admin</button>
        
        </div>
        <div class="navigations-menu-btn">
            <i class="a" href="home.php" onclick="myMenuFunction()"></i>
        </div>
    </nav>

    <div class="form-box">
        <form class="login-container" id="loginForm" method="post">
            <div class="top">
                <span>Don't have an account? <a href="#" onclick="register()">Sign Up</a></span>
                <header>Login</header>
            </div>

            <?php if (!empty($errorMessage)): ?>
            <p style="background: red; color: white; border-radius: 10px; padding: 5px 10px;"><?php echo $errorMessage ?></p>
            <?php endif ?>

            <div class="input-box">
                <input type="text" name="email" class="input-field" placeholder="Email" required>
                <i class="bx bx-user"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" class="input-field" placeholder="Password" required>
                <i class="bx bx-lock-alt"></i>
            </div>
            <div class="input-box">
                <input type="submit" class="submit" name="login" value="Sign In">
            </div>
            <div class="awesome">
                <div class="login">
                    <input type="checkbox" id="login-check">
                    <label for="login-check"> Remember Me</label>
                </div>
                <div class="forgot">
                    <label><a href="#">Forgot password?</a></label>
                </div>
            </div>
        </form>

        <form class="register-container" id="registerForm" method="post">
            <div class="top">
                <span>Have an account? <a href="home.php" onclick="login()">Login</a></span>
                <header>Sign Up</header>
            </div>
            <div class="two-forms">
                <div class="input-box">
                    <input type="text" class="input-field" name="first-name" placeholder="First name" required>
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="text" class="input-field" name="last-name" placeholder="Last name" required>
                    <i class="bx bx-user"></i>
                </div>
            </div>
            <div class="input-box">
                <input type="email" class="input-field" name="emails" placeholder="Email" required>
                <i class="bx bx-envelope"></i>
            </div>
            <div class="input-box">
                <input type="password" class="input-field" name="password" placeholder="Password" required>
                <i class="bx bx-lock-alt"></i>
            </div>
            <div class="input-box">
                <input type="submit" class="submit" name="register" value="Register">
            </div>
            <div class="two-col">
                <div class="one">
                    <input type="checkbox" id="register-check">
                    <label for="register-check"> Remember Me</label>
                </div>
                <div class="two">
                    <label><a href="#">Terms & conditions</a></label>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function myMenuFunction() {
        var i = document.getElementById("navMenu");

        if(i.className === "nav-menu") {
            i.className += " responsive";
        } else {
            i.className = "nav-menu";
        }
    }

    var a = document.getElementById("loginBtn");
    var b = document.getElementById("registerBtn");
    var x = document.getElementById("loginForm");
    var y = document.getElementById("registerForm");

    function login() {
        x.style.left = "4px";
        y.style.right = "-520px";
        a.className += " white-btn";
        b.className = "btn";
        x.style.opacity = 1;
        y.style.opacity = 0;
    }

    function register() {
        x.style.left = "-510px";
        y.style.right = "5px";
        a.className = "btn";
        b.className += " white-btn";
        x.style.opacity = 0;
        y.style.opacity = 1;
    }

    function goToAdmin() {
        window.location.href = "admin_login.php";
    }


</script>

<script>
    if ('WebSocket' in window) {
        (function () {
            function refreshCSS() {
                var sheets = [].slice.call(document.getElementsByTagName("link"));
                var head = document.getElementsByTagName("head")[0];
                for (var i = 0; i < sheets.length; ++i) {
                    var elem = sheets[i];
                    var parent = elem.parentElement || head;
                    parent.removeChild(elem);
                    var rel = elem.rel;
                    if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
                        var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
                        elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
                    }
                    parent.appendChild(elem);
                }
            }
            var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
            var address = protocol + window.location.host + window.location.pathname + '/ws';
            var socket = new WebSocket(address);
            socket.onmessage = function (msg) {
                if (msg.data == 'reload') window.location.reload();
                else if (msg.data == 'refreshcss') refreshCSS();
            };
            if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
                console.log('Live reload enabled.');
                sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
            }
        })();
    }
    else {
        console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
    }
</script>
</body>
</html>
