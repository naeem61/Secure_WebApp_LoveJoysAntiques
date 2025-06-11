<?php
session_start();
require 'db.php';
require 'csrf_token.php';
require_once 'security_headers.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {

    //validates CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $description = $_POST['description'];
    $contact_preference_type = $_POST['contact_preference'];
    $photo = $_FILES['photo'];

    //gets users email and phone from the database
    $stmt = $pdo->prepare("SELECT email, phone_number FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    //gets the contact information based on selected preference
    $contact_preference = $contact_preference_type === 'email' ? $user['email'] : $user['phone_number'];


    //ONLY alliws allowes these types and extensions
    $allowedTypes = ['image/jpeg', 'image/png'];
    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    //verifies the file extension to make sure its allowed
    $fileExtension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Invalid file extension.");
    }

    //verifies the type (using finfo_file)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $photo['tmp_name']);
    finfo_close($finfo);

    //ALSO, only allows the file to be under 2mb
    if (!in_array($mimeType, $allowedTypes) || $photo['size'] > 2000000) {
        die("Invalid file type or size. Please upload a JPEG or PNG image under 2MB.");
    }

    //sanitises the  filename to allow no weird potential malicious imput by  emoveing characters that 
    //are not alphanumeric, dots, hyphens, or underscores from the filename
    $safeFilename = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($photo["name"]));
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    //creates unique file path based on the current timestamp which ensures no two files have the same name 
    //even if uploaded with the same original name
    $photoPath = $uploadDir . uniqid() . '_' . $safeFilename;

    //moves the file to uploads directory
    if (move_uploaded_file($photo["tmp_name"], $photoPath)) {
        chmod($photoPath, 0644); 

        $stmt = $pdo->prepare("INSERT INTO requests (user_id, description, contact_preference, photo_path) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $description, $contact_preference, $photoPath])) {
            echo "Evaluation request submitted!";
        } else {
            echo "Could not submit the request. Please try again.";
        }
    } else {
        echo "Failed to upload photo.";
    }
}

?>
