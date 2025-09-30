<?php
session_start();

// Check if the user is already logged in, if yes then redirect to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
} else {
    // If not logged in, redirect to login page
    header("location: login.php");
    exit;
}
?>