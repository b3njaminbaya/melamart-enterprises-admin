<?php
require_once 'php_action/db_connect.php';

session_start();

// Already logged in → go to dashboard
if(isset($_SESSION['userId'])) {
    header('Location: dashboard.php');
    exit();
}

$errors = array();

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if($username === '') { $errors[] = "Username is required."; }
    if($password === '') { $errors[] = "Password is required."; }

    if(empty($errors)) {
        /*
         * Prepared statement — eliminates SQL injection.
         * We select the stored hash and verify it with password_verify()
         * (bcrypt). The auth_helper.php fallback handles legacy MD5 accounts
         * transparently and upgrades them on first successful login.
         */
        $stmt = $connect->prepare(
            "SELECT user_id, password FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stored = $user['password'];

            $verified = false;

            // 1) Try modern bcrypt hash first
            if(password_verify($password, $stored)) {
                $verified = true;
            }
            // 2) Fallback: legacy MD5 — verify, then transparently upgrade to bcrypt
            elseif($stored === md5($password)) {
                $verified = true;
                // Upgrade the hash in-place
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $upStmt  = $connect->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $upStmt->bind_param("si", $newHash, $user['user_id']);
                $upStmt->execute();
                $upStmt->close();
            }

            if($verified) {
                // Prevent session fixation
                session_regenerate_id(true);
                $_SESSION['userId'] = $user['user_id'];
                $stmt->close();
                header('Location: dashboard.php');
                exit();
            }
        }

        // Generic message — prevents username enumeration
        $errors[] = "Incorrect username or password.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Melamart Admin — Login</title>
  <link rel="shortcut icon" href="images/logo.jpeg">

  <!-- Melamart brand fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Open Sans', 'Helvetica Neue', Arial, sans-serif;
      background: #f7f9fb;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    /* ── Card ── */
    .login-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 40px rgba(11,61,94,.14);
      width: 100%;
      max-width: 420px;
      padding: 40px 36px 0;
      overflow: hidden;
    }

    /* ── Brand ── */
    .login-brand {
      text-align: center;
      margin-bottom: 24px;
    }
    .login-brand img {
      height: 70px;
      width: auto;
      display: block;
      margin: 0 auto 12px;
      border-radius: 6px;
    }
    .login-brand h1 {
      font-family: 'Montserrat', sans-serif;
      font-size: 19px;
      font-weight: 800;
      color: #0b3d5e;
      line-height: 1.2;
    }
    .login-brand p {
      font-size: 12px;
      color: #697587;
      margin-top: 3px;
      letter-spacing: .3px;
    }

    hr.divider {
      border: none;
      border-top: 1px solid #dde3ea;
      margin: 0 0 22px;
    }

    .login-heading {
      font-family: 'Montserrat', sans-serif;
      font-size: 15px;
      font-weight: 700;
      color: #0b3d5e;
      text-align: center;
      margin-bottom: 20px;
    }

    /* ── Errors ── */
    .error-box {
      background: #fce8e6;
      color: #8b1a14;
      border-radius: 6px;
      padding: 10px 14px;
      font-size: 13.5px;
      margin-bottom: 16px;
    }
    .error-box + .error-box { margin-top: -10px; }

    /* ── Form ── */
    .form-group { margin-bottom: 18px; }
    label {
      display: block;
      font-size: 12.5px;
      font-weight: 600;
      color: #1a2634;
      margin-bottom: 6px;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #dde3ea;
      border-radius: 6px;
      font-family: 'Open Sans', sans-serif;
      font-size: 14px;
      color: #1a2634;
      background: #fff;
      transition: border-color .2s, box-shadow .2s;
    }
    input[type="text"]:focus,
    input[type="password"]:focus {
      outline: none;
      border-color: #0b3d5e;
      box-shadow: 0 0 0 3px rgba(11,61,94,.1);
    }

    /* ── Submit ── */
    .btn-login {
      display: block;
      width: 100%;
      padding: 12px;
      background-color: #0b3d5e;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-family: 'Montserrat', sans-serif;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 6px;
      transition: background-color .2s, box-shadow .2s, transform .15s;
    }
    .btn-login:hover {
      background-color: #1a5f8a;
      box-shadow: 0 4px 16px rgba(11,61,94,.22);
      transform: translateY(-1px);
    }
    .btn-login:active { transform: translateY(0); }

    /* ── Yellow accent strip ── */
    .login-accent {
      height: 5px;
      background: linear-gradient(90deg, #f5a800 0%, #ffc130 100%);
      margin: 28px -36px 0;
    }

    /* ── Footer note ── */
    .login-footer {
      text-align: center;
      font-size: 11.5px;
      color: #b0bbc9;
      margin-top: 18px;
    }
  </style>
</head>
<body>

  <div class="login-card">

    <div class="login-brand">
      <img src="images/logo.jpeg" alt="Melamart Enterprises Limited">
      <h1>Melamart Enterprises</h1>
      <p>Admin Portal</p>
    </div>

    <hr class="divider">
    <h2 class="login-heading">Sign in to continue</h2>

    <?php foreach($errors as $err): ?>
      <div class="error-box"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endforeach; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST" autocomplete="off">

      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          placeholder="Enter your username"
          value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
          required
          autocomplete="username"
        >
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Enter your password"
          required
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn-login">Sign In</button>

    </form>

    <div class="login-accent"></div>
  </div>

  <p class="login-footer">
    &copy; <?php echo date('Y'); ?> Melamart Enterprises Limited. All rights reserved.
  </p>

</body>
</html>
