<?php
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';

$maxAttempts = 5; //amount of login attempts till locked out of account (set to 5)
$lockoutTime = 60 * 60; //lockout time (set to 1 hour) 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

//function to send 2FA code by email securley and encrypted using ENCRYPTION_STARTTLS
function sendTwoFactorEmail($email, $twoFactorCode) {
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
        $mail->Subject = 'Your Two-Factor Authentication Code';
        $mail->Body = "Your 2FA code is: <strong>$twoFactorCode</strong>. This code is valid for 10 minutes.";
        $mail->send();
    } catch (Exception $e) {
        echo "2FA email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }


    
    //secret key
    $secretKey = "6Lcyn3sqAAAAAF-pGsnzj42QoilnpeShZieJ-b4w";
    
    //reCAPTCHA response
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    //verifying reCAPTCHA
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);
    
    if (!$responseKeys["success"]) {
        $_SESSION['message'] = "CAPTCHA verification failed. Please try again.";
        header("Location: login.php");
        exit();
    }
          

    $email = $_POST['email'];
    $password = $_POST['password'];

    //gets the the usres login attempts and lockout time
    $stmt = $pdo->prepare("SELECT user_id, name, password_hashed, role, login_attempts, lockout_time, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {

        if (!$user['is_verified']) {
            die("Please verify your email address before logging in.");
        }
        
        $currentTime = time();

    //checks if user is locked out AND if the lockout time has expired
        if ($user['login_attempts'] >= $maxAttempts && ($currentTime - strtotime($user['lockout_time'])) < $lockoutTime) {
            $remainingLockout = ceil(($lockoutTime - ($currentTime - strtotime($user['lockout_time']))) / 60);
            $_SESSION['message'] = "Account is locked. Try again after $remainingLockout minutes.";
            header("Location: login.php");
            exit();

        } elseif ($user['login_attempts'] >= $maxAttempts && ($currentTime - strtotime($user['lockout_time'])) >= $lockoutTime) {
        //resets  the login attempts and lockout time if the lockout period has expired
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, lockout_time = NULL WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            $user['login_attempts'] = 0;
        }

        //checks the password against the hashed password from registering using password_verify()
        if (password_verify($password, $user['password_hashed'])) {
            //resets login attempts and lockout time if  successful login
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, lockout_time = NULL WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email'] = $email;

            //creates a RANDOM 6-digit 2FA code
            //2FA - USERS LOGGING IN HAVE TO AUTHENTICATE BY TYPING IN A RANDOM 6 DIGIT CODE THAT IS SENT TO THEIR EMAIL
            $twoFactorCode = random_int(100000, 999999);
            //hashes the 2FA code
            $hashedCode = password_hash($twoFactorCode, PASSWORD_BCRYPT); 
            //sets a expiry time for the code - set at 10 minutes
            $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes')); 


            $stmt = $pdo->prepare("UPDATE users SET two_factor_code = ?, two_factor_expiry = ? WHERE user_id = ?");
            $stmt->execute([$hashedCode, $expiryTime, $user['user_id']]);

            //SENDS 2FA COSE BY EMAIL SECURELY
            sendTwoFactorEmail($email, $twoFactorCode);

            $_SESSION['user_id'] = $user['user_id'];
            header("Location: two_factor_verification.php");
            exit();
        } else {
            //increments login attempts if incorrect details
            $attempts = $user['login_attempts'] + 1;
            if ($attempts >= $maxAttempts) {
                //locks the user account as hit max attempts, and sets when lockout tiem ends
                $lockoutTimeEnds = date('Y-m-d H:i:s', time() + $lockoutTime);
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, lockout_time = ? WHERE user_id = ?");
                $stmt->execute([$attempts, $lockoutTimeEnds, $user['user_id']]);
                $_SESSION['message'] = "Account is locked due to too many failed login attempts. Try again after 1 hour.";
                header("Location: login.php");
                exit();
            } else {
                //updates teh  attempts without locking out
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = ? WHERE user_id = ?");
                $stmt->execute([$attempts, $user['user_id']]);
                $_SESSION['message'] = "Invalid email or password.";
                header("Location: login.php");
                exit();
            }       
        }
    } else {
        $_SESSION['message'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }   
}
?>
