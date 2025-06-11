<?php
require 'csrf_token.php';
require_once 'security_headers.php';
?>
<head><link rel="stylesheet" href="css/styles.css"></head>
<form method="post" action="register_process.php">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <input type="text" name="name" placeholder="Name" required>
    <input type="text" name="phone" placeholder="Phone Number" required>

        <!-- security questions -->
        <label for="security_question_1">Security Question 1</label>
    <select name="security_question_1" required>
        <option value="">Choose a question...</option>
        <option value="What was your first pet's name?">What was your first pet's name?</option>
        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
    
    </select>
    <input type="text" name="security_answer_1" placeholder="Answer" required>

    <label for="security_question_2">Security Question 2</label>
    <select name="security_question_2" required>
        <option value="">Choose a question...</option>
        <option value="What was the name of your elementary school?">What was the name of your elementary school?</option>
        <option value="In what city were you born?">In what city were you born?</option>
        
    </select>
    <input type="text" name="security_answer_2" placeholder="Answer" required>

    <label for="security_question_3">Security Question 3</label>
    <select name="security_question_3" required>
        <option value="">Choose a question...</option>
        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
        <option value="What is your favorite book?">What is your favorite book?</option>
       
    </select>
    <input type="text" name="security_answer_3" placeholder="Answer" required>

    <?php
    //displays session message
    if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
    }
    ?>
    
    <button type="submit">Register</button>
    <button type="button" onclick="window.location.href='login.php'">Already have an account? Login</button>
</form>