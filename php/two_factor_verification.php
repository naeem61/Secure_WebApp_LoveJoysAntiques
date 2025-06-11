<?php
session_start();
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $twoFactorCode = $_POST['two_factor_code'];

    //input validation to ensure no weird input is processed
    if (!preg_match('/^\d{6}$/', $twoFactorCode)) {
        $_SESSION['message'] = "Invalid code format.";
        header("Location: two_factor_verification.php");
        exit();
    }

    //gets the  2FA details for the user
    $stmt = $pdo->prepare("SELECT two_factor_code, two_factor_expiry FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($twoFactorCode, $user['two_factor_code']) && strtotime($user['two_factor_expiry']) > time()) {
            // Clear 2FA details
            $stmt = $pdo->prepare("UPDATE users SET two_factor_code = NULL, two_factor_expiry = NULL WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            //if all is correct, ie the code is teh same then they can enter their account
            header("Location: welcome.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid or expired 2FA code. Please try again.";
        }
    } else {
        $_SESSION['message'] = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/styles.css"></head>
<body>
    <form method="post">
        <h1>Enter 6 digit 2FA code sent to your email</h1>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="two_factor_code" placeholder="Enter 2FA Code" required>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            unset($_SESSION['message']);
        }
        ?>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
