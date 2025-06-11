<?php
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$questions = [];
$emailError = '';
//checks if email is stored in session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
} else {
    echo "Error: Email not found in session.";
    exit();
}

if ($email) {
    //gets security questions based on the email stored in session ie logged in
    $stmt = $pdo->prepare("SELECT security_question_1, security_question_2, security_question_3 FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $questions = [
            'question_1' => $user['security_question_1'],
            'question_2' => $user['security_question_2'],
            'question_3' => $user['security_question_3']
        ];
    } else {
        $emailError = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php if ($email && !empty($questions)): ?>
        <form method="post" action="password_recovery_security.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="email_for_questions" value="<?php echo htmlspecialchars($email); ?>">

            <label><?php echo htmlspecialchars($questions['question_1']); ?></label>
            <input type="text" name="security_answer_1" placeholder="Your Answer" required>

            <label><?php echo htmlspecialchars($questions['question_2']); ?></label>
            <input type="text" name="security_answer_2" placeholder="Your Answer" required>

            <label><?php echo htmlspecialchars($questions['question_3']); ?></label>
            <input type="text" name="security_answer_3" placeholder="Your Answer" required>

            <button type="submit" name="reset_option" value="security_questions">Submit Answers</button>
        </form>
    <?php elseif ($emailError): ?>
        <p><?php echo $emailError; ?></p>
    <?php endif; ?>
</body>
</html>
