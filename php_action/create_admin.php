<?php
require_once 'db_connect.php';
require_once 'security_utils.php';

try {
    // First, delete all users and reset auto-increment
    $connect->query("DELETE FROM users");
    $connect->query("ALTER TABLE users AUTO_INCREMENT = 1");

    // Now create admin user - it will get ID 1
    $username = "admin";
    $password = "Admin@123";
    $hashedPassword = SecurityUtils::hashPassword($password); // Using SecurityUtils for consistent hashing
    $email = "admin@example.com";

    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sss", $username, $hashedPassword, $email);

    if($stmt->execute()) {
        echo "Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Password: Admin@123\n";
        echo "User ID: 1 (Admin)\n";
    } else {
        echo "Error creating admin user: " . $stmt->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Admin creation error: " . $e->getMessage());
}

$connect->close();
?>
