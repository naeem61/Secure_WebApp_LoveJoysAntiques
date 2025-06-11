<?php
require 'db.php';
require_once 'security_headers.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT user_id, verification_expiry FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    //checks if token is valid and has not expired
    if ($user && strtotime($user['verification_expiry']) > time()) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_expiry = NULL WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        echo "Account verified, you can now log in";
    } else {
        echo "This verification link is invalid or has expired";
    }
} else {
    echo "No verification token provided.";
}

?>

