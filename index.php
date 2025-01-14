<?php 
require_once 'php_action/db_connect.php';
require_once 'php_action/security_utils.php';
require_once 'php_action/EmailService.php';
require_once 'php_action/csrf_utils.php';
require_once 'php_action/recaptcha_utils.php';
require_once 'php_action/AuditLogger.php';

session_start();

$errors = array();

// Check for session timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $errors[] = "Your session has expired. Please log in again.";
}

if($_POST) {
    // Initialize audit logger
    $auditLogger = AuditLogger::getInstance();
    
    // Debug logging
    error_log("Form submitted. POST data: " . print_r($_POST, true));
    
    // Check CSRF token
    if (!CSRFProtection::validateToken()) {
        error_log("CSRF validation failed");
        $errors[] = "Invalid request - CSRF token missing or invalid";
        $auditLogger->log('login_failed', 'user', null, null, ['reason' => 'CSRF validation failed']);
    } 
    // Verify reCAPTCHA
    else {
        $recaptchaResult = ReCaptchaV3::verify($_POST['g-recaptcha-response'] ?? '', 'login');
        if (!$recaptchaResult['success']) {
            error_log("reCAPTCHA validation failed: " . $recaptchaResult['error']);
            $errors[] = "Security verification failed. Please try again.";
            $auditLogger->log('login_failed', 'user', null, null, ['reason' => 'reCAPTCHA validation failed']);
        }
        else {
            // Process login
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if(empty($username) || empty($password)) {
                if($username == "") {
                    $errors[] = "Username is required";
                } 
                if($password == "") {
                    $errors[] = "Password is required";
                }
                $auditLogger->log('login_failed', 'user', null, null, ['reason' => 'Missing credentials']);
            } else {
                try {
                    $sql = "SELECT * FROM users WHERE username = ?";
                    $stmt = SecurityUtils::prepareAndExecute($sql, "s", [$username]);
                    $result = $stmt->get_result();

                    if($result->num_rows == 1) {
                        $user = $result->fetch_assoc();
                        error_log("User found: " . print_r($user, true));
                        
                        if(SecurityUtils::verifyPassword($password, $user['password'])) {
                            // Check if user has email
                            if (empty($user['email'])) {
                                error_log("User has no email address");
                                $errors[] = "Your account needs an email address for MFA. Please contact admin.";
                                $auditLogger->log('login_failed', 'user', $user['user_id'], null, ['reason' => 'Missing email for MFA']);
                            } else {
                                // Generate and send MFA code
                                $mfaCode = SecurityUtils::generateMFACode();
                                error_log("Generated MFA code: " . $mfaCode);
                                
                                if (SecurityUtils::storeMFACode($user['user_id'], $mfaCode)) {
                                    error_log("MFA code stored successfully");
                                    
                                    if (EmailService::sendMFACode($user['email'], $mfaCode)) {
                                        error_log("MFA code email sent successfully");
                                        // Store temporary session data for MFA
                                        $_SESSION['mfa_pending'] = true;
                                        $_SESSION['temp_user_id'] = $user['user_id'];
                                        $_SESSION['username'] = $user['username'];
                                        
                                        $auditLogger->log('mfa_initiated', 'user', $user['user_id']);
                                        
                                        header('location: verify-mfa.php');
                                        exit();
                                    } else {
                                        error_log("Failed to send MFA email");
                                        $errors[] = "Failed to send verification code. Please try again.";
                                        $auditLogger->log('login_failed', 'user', $user['user_id'], null, ['reason' => 'MFA email sending failed']);
                                    }
                                } else {
                                    error_log("Failed to store MFA code");
                                    $errors[] = "System error. Please try again.";
                                    $auditLogger->log('login_failed', 'user', $user['user_id'], null, ['reason' => 'MFA code storage failed']);
                                }
                            }
                        } else {
                            SecurityUtils::recordLoginAttempt($username);
                            $errors[] = "Incorrect username/password combination";
                            $auditLogger->log('login_failed', 'user', $user['user_id'], null, ['reason' => 'Invalid password']);
                        }
                    } else {
                        $errors[] = "Username does not exist";
                        $auditLogger->log('login_failed', 'user', null, null, ['reason' => 'Invalid username', 'attempted_username' => $username]);
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $errors[] = "An error occurred. Please try again.";
                    $auditLogger->log('login_error', 'user', null, null, ['error' => $e->getMessage()]);
                }
            }
        }
    }
    
    if($errors) {
        // Store errors in session
        $_SESSION['errors'] = $errors;
        header('location: '.$_SERVER['PHP_SELF']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Management System</title>

    <!-- bootstrap -->
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
    <!-- bootstrap theme-->
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
    <!-- font awesome -->
    <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

    <!-- custom css -->
    <link rel="stylesheet" href="custom/css/custom.css">

    <!-- jquery -->
    <script src="assests/jquery/jquery.min.js"></script>
    <!-- jquery ui -->  
    <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
    <script src="assests/jquery-ui/jquery-ui.min.js"></script>

    <!-- bootstrap js -->
    <script src="assests/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="row vertical">
            <div class="col-md-5 col-md-offset-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign in</h3>
                    </div>
                    <div class="panel-body">
                        <div class="messages">
                            <?php if($errors) {
                                foreach ($errors as $key => $value) {
                                    echo '<div class="alert alert-warning" role="alert">'.htmlspecialchars($value).'</div>';
                                }
                            } ?>
                        </div>

                        <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="loginForm">
                            <?php 
                            echo CSRFProtection::getTokenField(); 
                            // Add reCAPTCHA scripts
                            echo ReCaptchaV3::getScript();
                            // Add form protection
                            echo ReCaptchaV3::getFormProtection('login', 'loginForm');
                            ?>
                            <fieldset>
                                <div class="form-group">
                                    <label for="username" class="col-sm-3 control-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" autocomplete="off" required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="col-sm-3 control-label">Password</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off" required />
                                            <span class="input-group-addon" style="cursor: pointer;" id="togglePassword">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9">
                                        <button type="submit" class="btn btn-primary">Sign in</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password toggle script -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>