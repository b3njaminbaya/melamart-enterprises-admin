<?php 
require_once 'php_action/db_connect.php';

session_start();

if(isset($_SESSION['userId'])) {
    header('location: http://localhost/inventory-management-system/dashboard.php'); 
}

$errors = array();

if($_POST) {        

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
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $connect->query($sql);

        if($result->num_rows == 1) {
            $password = md5($password);
            // exists
            $mainSql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
            $mainResult = $connect->query($mainSql);

            if($mainResult->num_rows == 1) {
                $value = $mainResult->fetch_assoc();
                $user_id = $value['user_id'];

                // set session
                $_SESSION['userId'] = $user_id;

                header('location: http://localhost/inventory-management-system/dashboard.php'); 
            } else{
                
                $errors[] = "Incorrect username/password combination";
            } // /else
        } else {        
            $errors[] = "Username doesnot exists";      
        } // /else
    } // /else not empty username // password
    
} // /if $_POST
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>Login</title>
    <link rel="shortcut icon" href="images/fevicon.jpg">  

    <!-- Font Icon -->
    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="css/style.css">
</head>


        <!-- Sing in  Form -->
        <section class="sign-in">
            <div class="container">
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="images/signin-image.jpg" alt="sing up image"></figure>
                    </div>
                    <div class="messages">
                            <?php if($errors) {
                                foreach ($errors as $key => $value) {
                                    echo '<div class="alert alert-warning" role="alert">
                                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                                    '.$value.'</div>';                                      
                                    }
                                } ?>
                    </div>
                    <div class="signin-form">
                        <h2 class="form-title">Login</h2>
                        <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="loginForm">
                        
                            <div class="form-group">
                                <label for="your_name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                <input type="text" name="username" id="username" required="_required"placeholder="username"/>
                            </div>
                            <div class="form-group">
                                <label for="your_pass"><i class="zmdi zmdi-lock"></i></label>
                                <input type="password" name="password" id="password" required="_required" placeholder="Password"/>
                            </div>
                            
                            <div class="form-group form-button">
                                <input type="submit" name="submit" id="submit" class="form-submit" value="Log in"/>
                            </div>
                        </form>
                        <div class="social-login">
                            <span class="social-label">Contact us with</span>
                            <ul class="socials">
                                <li><a href="https://www.facebook.com/pawarash"><i class="display-flex-center zmdi zmdi-facebook"></i></a></li>
                                <li><a href="https://www.twitter.com/pawarash_"><i class="display-flex-center zmdi zmdi-twitter"></i></a></li>
                                <li><a href="#"><i class="display-flex-center zmdi zmdi-google"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body><!-- This templates was made by Colorlib (https://colorlib.com) -->
</html>