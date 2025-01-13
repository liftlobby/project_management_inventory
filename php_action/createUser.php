<?php     
require_once 'core.php';
require_once 'security_utils.php';

header('Content-Type: application/json');
$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {    
    $userName = isset($_POST['userName']) ? trim($_POST['userName']) : '';
    $password = isset($_POST['upassword']) ? $_POST['upassword'] : '';
    $email = isset($_POST['uemail']) ? trim($_POST['uemail']) : '';
    
    try {
        // Validate username
        if(empty($userName)) {
            $valid['success'] = false;
            $valid['messages'] = "Username is required";
            echo json_encode($valid);
            exit();
        }

        // First check if username already exists
        $checkSql = "SELECT * FROM users WHERE username = ?";
        $stmt = $connect->prepare($checkSql);
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $valid['success'] = false;
            $valid['messages'] = "Username already exists";
            echo json_encode($valid);
            exit();
        }

        // Validate password
        if(empty($password)) {
            $valid['success'] = false;
            $valid['messages'] = "Password is required";
            echo json_encode($valid);
            exit();
        }

        // Check password complexity
        if (!SecurityUtils::isPasswordComplex($password)) {
            $valid['success'] = false;
            $valid['messages'] = "Password must contain at least:
                - 8 characters
                - One uppercase letter
                - One lowercase letter
                - One number
                - One special character (!@#$%^&*()-_=+{};:,<.>)";
            echo json_encode($valid);
            exit();
        }

        // Hash password using SecurityUtils (includes pepper)
        $hashedPassword = SecurityUtils::hashPassword($password);
        
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("sss", $userName, $hashedPassword, $email);
        
        if($stmt->execute() && $stmt->affected_rows > 0) {
            $valid['success'] = true;
            $valid['messages'] = "User successfully created";    
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error creating user. Please try again.";
        }
        
    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
        error_log("User creation error: " . $e->getMessage());
    }
    
    $connect->close();
    echo json_encode($valid);
} else {
    $valid['success'] = false;
    $valid['messages'] = "Invalid request method";
    echo json_encode($valid);
}
