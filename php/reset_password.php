<?php

require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';
session_start();

if (!isset($_GET['token'])) {
    die("Token not provided.");
}

$token = $_GET['token'];

//checks if token exists and is not expired
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    $_SESSION['user_id_reset'] = $user['user_id'];
    $_SESSION['reset_token'] = $token;
} else {
    die("Invalid or expired token.");
}
?>

<head><link rel="stylesheet" href="css/styles.css"></head>
<form method="post" action="update_password.php">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" id="new_password" required>
    
    <label for="confirm_password">Confirm New Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    
    <button type="submit">Reset Password</button>
    <button type="button" onclick="window.location.href='login.php'">Back to Login</button>
</form>
