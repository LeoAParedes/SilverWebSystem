<?php
session_start();
require_once("connect.php");
require_once("functions.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logoutUser($pdo,   $username); // Call the logout function
}
?>