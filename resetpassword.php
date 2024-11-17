<?php
    session_start();

    // Kiểm tra xem người dùng đã đăng nhập hay chưa
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
        header('location: /index.php');
        die();
    }

    // Giới hạn số lần thử token
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
    }

    if ($_SESSION['attempts'] >= 5) {
        echo 'Too many attempts. Please try again later.';
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Đảm bảo không tiết lộ thông tin qua URL
        if (!isset($_GET['token'])) {
            echo 'Invalid request';
            die();
        }
        $token = $_GET['token'];
        
        include('includes/db_connect.php');
        $ret = pg_prepare($db, "checktoken_query", "select * from tokens where token = $1");
        $ret = pg_execute($db, "checktoken_query", array($token));

        if (pg_num_rows($ret) === 0) {
            $invalid_token = true;
        }
    }
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['token']) || !isset($_POST['password1']) || !isset($_POST['password2'])) {
            echo 'Invalid request';
            die();
        }

        $token = $_POST['token'];
        $password1 = $_POST['password1'];
        $password2 = $_POST['password2'];

        if ($password1 !== $password2) {
            $pass_error = true;
        }
        else {
            include('includes/db_connect.php');
            $ret = pg_prepare($db, "checktoken_query", "select * from tokens where token = $1");
            $ret = pg_execute($db, "checktoken_query", array($token));

            if (pg_num_rows($ret) === 0) {
                $invalid_token = true;
            } else {
                // Cập nhật mật khẩu
                $uid = pg_fetch_row($ret)[1];
                $newpass = hash('sha256', $password1);  // Đảm bảo mật khẩu được mã hóa an toàn

                $ret = pg_prepare($db, "changepassword_query", "update users set password = $1 where uid = $2");
                $ret = pg_execute($db, "changepassword_query", array($newpass, $uid));

                // Xóa token sau khi sử dụng
                $ret = pg_prepare($db, "deletetoken_query", "delete from tokens where token = $1");
                $ret = pg_execute($db, "deletetoken_query", array($token));

                // Đánh dấu thành công
                $success = true;
            }
        }
    }
?>

<html>
    <head>
        <title>TUDO/Reset Password</title>
        <link rel="stylesheet" href="style/style.css">
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <div id="content">
            <?php
                if (isset($invalid_token)) {
                    echo '<h1 style="color:red">The reset token is invalid or expired.</h1>';
                    echo '<a href="#" onclick="history.back();return false">Go back</a>';
                    $_SESSION['attempts']++;  // Tăng số lần thử
                    die();
                }
                
                if (isset($pass_error)) {
                    echo '<h1 style="color:red">Passwords don\'t match.</h1><br>';
                    echo '<a href="#" onclick="history.back();return false">Go back</a>';
                    $_SESSION['attempts']++;  // Tăng số lần thử
                    die();
                }
            ?>
            <div id="content">
                <form class="center_form" action="resetpassword.php" method="POST">
                    <h1>Reset Password:</h1>
                    <!-- Đổi từ GET sang POST cho token -->
                    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                    <input type="password" name="password1" placeholder="New password"><br><br>
                    <input type="password" name="password2" placeholder="Confirm password"><br><br>
                    <input type="submit" value="Change password"> 
                    <?php if (isset($success)) { echo "<span style='color:green'>Password changed!</span>"; } ?>
                    <br><br>
                    <?php include('includes/login_footer.php'); ?>
                </form>
            </div>
        </div>
    </body>
</html>

