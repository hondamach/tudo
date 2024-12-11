<?php
session_start();

// Tạo CSRF token khi tải trang
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Nếu đã đăng nhập, chuyển hướng về trang chính
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: /index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    include('includes/db_connect.php');

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra nếu đã vượt quá số lần thử đăng nhập
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
        if (isset($_SESSION['lock_time']) && (time() - $_SESSION['lock_time']) < 60) {
            $error = 'Too many failed attempts. Please try again in ' . (60 - (time() - $_SESSION['lock_time'])) . ' seconds.';
        } else {
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lock_time']);
        }
    }

    if (!isset($error)) {
        // Truy vấn để lấy mật khẩu đã băm từ cơ sở dữ liệu
        $ret = pg_prepare($db, "login_query", "SELECT password FROM users WHERE username = $1");
        $ret = pg_execute($db, "login_query", array($username));

        if (pg_num_rows($ret) === 1) {
            $hashed_password = pg_fetch_result($ret, 0, 'password');

            if (password_verify($password, $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;

                if ($username === 'admin') {
                    $_SESSION['isadmin'] = true;
                }

                $_SESSION['login_attempts'] = 0;
                unset($_SESSION['csrf_token']); // Reset CSRF token sau khi đăng nhập thành công
                header('Location: /index.php');
                exit();
            }
        }

        $error = 'Invalid username or password.';
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lock_time'] = time();
        }
    }
}
?>

<html>
<head>
    <title>TUDO/Log In</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="content">
        <form class="center_form" action="login.php" method="POST">
            <h1>Log In:</h1>
            <p>Currently we are in the Alpha testing phase, thus you may log in if you received credentials from
                the admin. Otherwise you can admin the few pages linked at the bottom :)
            </p>
            <input name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <!-- Thêm CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="submit" value="Log In">
            <?php if (isset($error)) { echo "<span style='color:red'>{$error}</span>"; } ?>
            <br><br>
            <?php include('includes/login_footer.php'); ?>
        </form>
    </div>
</body>
</html>
