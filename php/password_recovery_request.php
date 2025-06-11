<?php
session_start();
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- form for sending reset link -->
     
    <form method="post" action="send_reset_link.php">
    <h1>Password Recovery</h1>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
        <button onclick="window.location.href='logout.php'">Home</button>
    </form>

</body>
</html>
