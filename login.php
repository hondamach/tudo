<?php
    session_start();
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
        header('location: /index.php');
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Thêm salt vào mật khẩu
        $salt = "supersalt!!!"; // Chuỗi salt mà bạn muốn sử dụng
        $password = $salt . $_POST['password'];  // Kết hợp salt và mật khẩu người dùng nhập vào

        // Băm mật khẩu đã kết hợp salt
        $hashed_password = hash('sha256', $password);

        include('includes/db_connect.php');

        // Truy vấn để kiểm tra thông tin người dùng trong cơ sở dữ liệu
        $ret = pg_prepare($db, "login_query", "SELECT * FROM users WHERE username = $1 AND password = $2");
        $ret = pg_execute($db, "login_query", array($_POST['username'], $hashed_password));

        if (pg_num_rows($ret) === 1) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $_POST['username'];

            if ($_SESSION['username'] === 'admin') {
                $_SESSION['isadmin'] = true;
            }

            header('location: /index.php');
            die();
        } else {
            $error = true;
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
                <input name="username" placeholder="Username"><br><br>
                <input type="password" name="password" placeholder="Password"><br><br>
                <input type="submit" value="Log In"> 
                <?php if (isset($error)){echo "<span style='color:red'>Login Failed</span>";} ?>
                <br><br>
                <?php include('includes/login_footer.php'); ?>
            </form>
        </div>
    </body>
</html>

