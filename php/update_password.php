<?php
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';
session_start();

if (!isset($_SESSION['user_id_reset'])) {
    die("No user is authorised to reset the password.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $commonPasswordsFile = __DIR__ . '/common_passwords.txt';

    //checks the password against a list of 10 MILLION common passwords and propmpts the user to 
    //make another stronger password if it is in the list to reduce against DICTIONARY ATTACKS
    if (file_exists($commonPasswordsFile)) {
        $commonPasswords = file($commonPasswordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (in_array($newPassword, $commonPasswords)) {
            die("Please use a stronger password.");  
        }
    } else {
        
        die("An error occurred during registration!");
        
        exit();
    }

    //validates the  password strength
    if (strlen($newPassword) < 8 || !preg_match("/[A-Z]/", $newPassword) || !preg_match("/[a-z]/", $newPassword) || !preg_match("/[0-9]/", $newPassword) || !preg_match("/[@$!%*?&]/", $newPassword)) {
        die("Password must be at least 8 characters long and include an uppercase letter, lowercase letter, number, and special character.");
    }

    //checks if passwords match
    if ($newPassword !== $confirmPassword) {
        die("Passwords do not match.");
    }

    //hashes the new password
    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $userId = $_SESSION['user_id_reset'];

    //updates the users password in the database to the new password
    $stmt = $pdo->prepare("UPDATE users SET password_hashed = ?, reset_token = NULL, reset_expiry = NULL WHERE user_id = ?");
    if ($stmt->execute([$passwordHash, $userId])) {
        echo "Password successfully updated.";
    } else {
        echo "Error: Unable to update password.";
    }

    unset($_SESSION['user_id_reset']);
    unset($_SESSION['reset_token']);
}
?>

