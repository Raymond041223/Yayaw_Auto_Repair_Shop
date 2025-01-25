<?php
session_start();
unset($_SESSION['user-session']);
 header("Location: home.php");