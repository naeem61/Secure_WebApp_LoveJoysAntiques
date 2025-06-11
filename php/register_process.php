<?php
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

    //function to send verification email securley and encrypted using ENCRYPTION_STARTTLS
    function sendVerificationEmail($email, $token) {
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
            $mail->Subject = 'Account Verification';
    
            $verificationLink = "http://localhost/CompSecCW/php/verify.php?token=$token";
            $mail->Body = "Click the link to verify your account: <a href='$verificationLink'>$verificationLink</a>";
            $mail->send();
        } catch (Exception $e) {
            echo "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //checks if the CSRF token from the form matches the session token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    //gets the  security questions and answers
    $securityQuestion1 = $_POST['security_question_1'];
    $securityAnswer1 = password_hash($_POST['security_answer_1'], PASSWORD_BCRYPT);
    $securityQuestion2 = $_POST['security_question_2'];
    $securityAnswer2 = password_hash($_POST['security_answer_2'], PASSWORD_BCRYPT);
    $securityQuestion3 = $_POST['security_question_3'];
    $securityAnswer3 = password_hash($_POST['security_answer_3'], PASSWORD_BCRYPT);


    
    $commonPasswordsFile = __DIR__ . '/common_passwords.txt';

    //checks the password against a list of 10 MILLION common passwords and propmpts the user to 
    //make another stronger password if it is in the list to reduce against DICTIONARY ATTACKS
    if (file_exists($commonPasswordsFile)) {
        $commonPasswords = file($commonPasswordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (in_array($password, $commonPasswords)) {
            $_SESSION['message'] = "Please use a stronger password.";
            header("Location: register.php");
            exit();
        }
    } else {
        
        error_log("Common passwords file not found at: " . $commonPasswordsFile);
        $_SESSION['message'] = "An error occurred during registration!";
        header("Location: register.php");
        exit();
    }

    //validates the phone number format
    if (!preg_match('/^\+?[0-9\s\-()]{7,15}$/', $phone)) {
        $_SESSION['message'] = "Invalid phone number";
        header("Location: register.php");
        exit();
    }

    //validates the email format (Using FILTER_VALIDATE_EMAIL)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email address.";
        header("Location: register.php");
        exit();
    }

    //validates the password strength
    if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[@$!%*?&]/", $password)) {
        $_SESSION['message'] = "Password must be at least 8 characters long and include an uppercase letter, lowercase letter, number, and special character.";
        header("Location: register.php");
        exit();
    }
    
    //checks if password matches
    if ($password !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    //hashing password (using PASSWORD_BCRYPT)
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

     //checks if email already exists (so shouldnt register again. However, if this wasnt here, it wouldnt make a 
     //difference as the database doesnt allow a duplicate entry of email anyway, so it will just go blank. However, I 
     //added thsi so users get a message saying email already exists and to register with a new one)
     $checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
     $checkEmailStmt->execute([$email]);
     if ($checkEmailStmt->fetchColumn() > 0) {
         $_SESSION['message'] = "A user with this email already exists. Please log in or use a different email.";
         header("Location: register.php");
         exit();
     }

    //REGISTERING VERIFICATION AUTHENTICATION BY EMAIL
    //generates a verification token and expiry so the link being sent is unique, and only that one can be used to 
    //confirm registering. The link also expires after 15 minutes so have to conform within that time
    $token = bin2hex(random_bytes(16));
    $verificationExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    //inserting user data with prepared statements
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hashed, name, phone_number, verification_token, verification_expiry, security_question_1, 
    security_answer_1, security_question_2, security_answer_2, security_question_3, security_answer_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $passwordHash, $name, $phone, $token, $verificationExpiry, $securityQuestion1, 
    $securityAnswer1, $securityQuestion2, $securityAnswer2, $securityQuestion3, $securityAnswer3]);

   //REGISTERING VERIFICATION  - SENDING EMAIL
    sendVerificationEmail($email, $token);
    $_SESSION['message'] = "Registration successful! Please check your email to verify your account.";
    header("Location: register.php");
    exit();


}
?>