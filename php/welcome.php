<?php
require_once 'security_headers.php';
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <?php if ($isAdmin): ?>
            <button onclick="window.location.href='admin_requests.php'">Admin Requests</button>
        <?php else: ?>
            <button onclick="window.location.href='request_evaluation.php'">Request Evaluation</button>
        <?php endif; ?>
        <button onclick="window.location.href='password_change.php'">Change Password</button>
        <button onclick="window.location.href='logout.php'">Sign Out</button>
    </div>
</body>
</html>
