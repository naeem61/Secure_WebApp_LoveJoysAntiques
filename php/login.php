<?php
require 'csrf_token.php';
require_once 'security_headers.php';
?>
<head><link rel="stylesheet" href="css/styles.css"></head>
<form method="post" action="login_process.php">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

     <!-- reCAPTCHA Widget !-->
     <div class="g-recaptcha" data-sitekey="6Lcyn3sqAAAAAIWyMvKA9Ej6mI4e8EtT8KikzHqk"></div>
     

    <!--<button type="submit">Submit</button>-->

    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>

<?php
    session_start();
    if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
    }
?>

    
    <button type="button" onclick="window.location.href='register.php'">Register</button>
    <button type="button" onclick="window.location.href='password_recovery_request.php'">Forgot Password</button>
</form>


 <!-- reCAPTCHA script  -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

