<?php 
require_once 'php_action/db_connect.php';
require_once 'php_action/security_utils.php';

session_start();

if(isset($_SESSION['userId'])) {
    header('location:'.$store_url.'dashboard.php');        
}

$errors = array();

if($_POST) {        
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !SecurityUtils::verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid request";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if(empty($username) || empty($password)) {
            if($username == "") {
                $errors[] = "Username is required";
            } 

            if($password == "") {
                $errors[] = "Password is required";
            }
        } else {
            // Check for brute force attempts
            if (SecurityUtils::checkLoginAttempts($username)) {
                $errors[] = "Too many login attempts. Please try again after 15 minutes.";
            } else {
                try {
                    $sql = "SELECT * FROM users WHERE username = ?";
                    $stmt = SecurityUtils::prepareAndExecute($sql, "s", [$username]);
                    $result = $stmt->get_result();

                    if($result->num_rows == 1) {
                        $user = $result->fetch_assoc();
                        
                        if(SecurityUtils::verifyPassword($password, $user['password'])) {
                            // Set session
                            $_SESSION['userId'] = $user['user_id'];
                            $_SESSION['last_activity'] = time();
                            
                            header('location:'.$store_url.'dashboard.php');
                        } else {
                            SecurityUtils::recordLoginAttempt($username);
                            $errors[] = "Incorrect username/password combination";
                        }
                    } else {
                        SecurityUtils::recordLoginAttempt($username);
                        $errors[] = "Username does not exist";
                    }
                } catch (Exception $e) {
                    $errors[] = "An error occurred. Please try again later.";
                    error_log("Login error: " . $e->getMessage());
                }
            }
        }
    }
}

// Generate new CSRF token
$csrf_token = SecurityUtils::generateCSRFToken();
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
                                    echo '<div class="alert alert-warning" role="alert">
                                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                                    '.$value.'</div>';                                        
                                    }
                                } ?>
                        </div>

                        <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <fieldset>
                                <div class="form-group">
                                    <label for="username" class="col-sm-3 control-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="col-sm-3 control-label">Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off" />
                                    </div>
                                </div>                                
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9">
                                        <button type="submit" class="btn btn-default">Login</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>