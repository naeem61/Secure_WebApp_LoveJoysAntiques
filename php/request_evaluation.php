<?php
require 'csrf_token.php';
require_once 'security_headers.php';
?>
<head><link rel="stylesheet" href="css/styles.css"></head>
<div class="container">
<form method="post" action="evaluation_process.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <textarea name="description" placeholder="Describe the item" required></textarea>
    <select name="contact_preference" required>
        <option value="email">Email</option>
        <option value="phone">Phone</option>
    </select>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png" required>
    <button type="submit">Submit Request</button>
</form>
<button onclick="window.location.href='logout.php'">Sign Out</button>
</div>