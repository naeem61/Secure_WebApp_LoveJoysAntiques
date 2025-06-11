<?php

require 'db.php';
require 'csrf_token.php'; 
require_once 'security_headers.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

//checking CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
}

function sendResetEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lovejoyantiques4@gmail.com';
        $mail->Password = 'qdhi mshe wkmo blxs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

       
        $mail->setFrom('lovejoyantiques4@gmail.com');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';

    
        $resetLink = "http://localhost/CompSecCW/php/reset_password.php?token=$token";
        $mail->Body = "Click the link to reset your password: <a href='$resetLink'>$resetLink</a>";

        $mail->send();
        echo "A reset link has been sent to your email.";
    } catch (Exception $e) {
        echo "Message could not be sent. Error: {$mail->ErrorInfo}";
    }
}

if (!empty($_POST['email'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(16));
    date_default_timezone_set('Europe/London');
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); //token expiry time - set to 15 minutes

   
    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
    if ($stmt->execute([$token, $expiry, $email])) {
        sendResetEmail($email, $token);
    } else {
        echo "Could not start the password reset. Please try again.";
    }
} else {
    die("Email field is empty or not set.");
}
?>
