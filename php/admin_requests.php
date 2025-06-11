<?php
require_once 'security_headers.php';
session_start();
//prevents unauthorised access from users by veryfying user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//makes sure that only admins can view the lists
if ($_SESSION['role'] !== 'admin') {
    header("Location: request_evaluation.php");
    exit();
}

require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Requests</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="request-container">
        <?php
        $stmt = $pdo->query("SELECT * FROM requests");
        while ($row = $stmt->fetch()) {
            //displays the lists
            echo "<div class='request-card'>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars($row['description']) . "</p>";
            echo "<p><strong>Contact:</strong> " . htmlspecialchars($row['contact_preference']) . "</p>";
            echo "<img src='" . htmlspecialchars($row['photo_path']) . "'class='request-image'>";
            echo "</div>";     
        }
        ?>
        <button class onclick="window.location.href='logout.php'">Sign Out</button>
    </div>
</body>
</html>
