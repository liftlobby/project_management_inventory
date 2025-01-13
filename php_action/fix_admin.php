<?php
require_once 'db_connect.php';
require_once 'security_utils.php';

// First, let's delete any existing admin user
$sql = "DELETE FROM users WHERE username = 'admin'";
$connect->query($sql);

// Now insert the admin user with user_id = 1
$username = "admin";
$password = "Admin@123"; // The actual password
$hashedPassword = SecurityUtils::hashPassword($password);
$email = "admin@example.com";

$sql = "INSERT INTO users (user_id, username, password, email) VALUES (1, ?, ?, ?)";
$stmt = $connect->prepare($sql);
$stmt->bind_param("sss", $username, $hashedPassword, $email);

if($stmt->execute()) {
    echo "Admin user fixed successfully!\n";
    echo "Username: admin\n";
    echo "Password: Admin@123\n";
    echo "User ID: 1 (Admin)\n";
} else {
    echo "Error: " . $connect->error;
}

$connect->close();
?>
