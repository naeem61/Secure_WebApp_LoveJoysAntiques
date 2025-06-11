<?php
session_start();
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';

//checks CSRF token validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }


    $email = $_POST['email_for_questions'];
    $answer1 = $_POST['security_answer_1'];
    $answer2 = $_POST['security_answer_2'];
    $answer3 = $_POST['security_answer_3'];

    //gets security question answers from the database
    $stmt = $pdo->prepare("SELECT user_id, security_answer_1, security_answer_2, security_answer_3 FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        //verifies answers thayt were hashed
        if (password_verify($answer1, $user['security_answer_1']) &&
            password_verify($answer2, $user['security_answer_2']) &&
            password_verify($answer3, $user['security_answer_3'])) {
            
            $_SESSION['user_id_reset'] = $user['user_id'];
            header("Location: reset_password_by_security.php?email=" . urlencode($email));
            exit();
        } else {
            echo "Incorrect answers. Please try again.";
        }
    } else {
        echo "No account associated with that email.";
    }
}
?>


