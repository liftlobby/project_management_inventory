<?php
require_once 'php_action/db_connect.php';
require_once 'php_action/security_utils.php';

session_start();

// Initialize SecurityUtils
SecurityUtils::init();

// Redirect if no pending MFA verification
if (!isset($_SESSION['mfa_pending']) || !isset($_SESSION['temp_user_id'])) {
    header('location: index.php');
    exit();
}

$errors = array();

if ($_POST) {
    $mfaCode = isset($_POST['mfa_code']) ? trim($_POST['mfa_code']) : '';
    
    if (empty($mfaCode)) {
        $errors[] = "Please enter the verification code";
    } else {
        try {
            if (SecurityUtils::verifyMFACode($_SESSION['temp_user_id'], $mfaCode)) {
                // MFA successful - complete login
                $_SESSION['userId'] = $_SESSION['temp_user_id'];
                $_SESSION['username'] = $_SESSION['username'];
                unset($_SESSION['mfa_pending']);
                unset($_SESSION['temp_user_id']);
                
                header('location: dashboard.php');
                exit();
            } else {
                $errors[] = "Invalid or expired verification code";
            }
        } catch (Exception $e) {
            error_log("MFA verification error: " . $e->getMessage());
            $errors[] = "An error occurred during verification. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MFA Verification</title>
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="custom/css/custom.css">
</head>
<body>
    <div class="container">
        <div class="row vertical">
            <div class="col-md-5 col-md-offset-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Two-Factor Authentication</h3>
                    </div>
                    <div class="panel-body">
                        <?php if($errors) {
                            foreach ($errors as $error) {
                                echo '<div class="alert alert-danger">' . $error . '</div>';
                            }
                        } ?>
                        
                        <p>Please enter the verification code sent to your email.</p>
                        
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="form-group">
                                <input type="text" name="mfa_code" class="form-control" placeholder="Enter verification code" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
