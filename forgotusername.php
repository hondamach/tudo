<?php
    session_start();

    // Kiểm tra nếu người dùng đã đăng nhập
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
        header('location: /index.php');
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];

        // Kết nối cơ sở dữ liệu
        include('includes/db_connect.php');

        // Sử dụng prepared statement để tránh SQL Injection
        $stmt = pg_prepare($db, "get_user", "SELECT * FROM users WHERE username = $1");
        $result = pg_execute($db, "get_user", array($username));

        // Kiểm tra nếu người dùng tồn tại và gửi token reset qua email
        if (pg_num_rows($result) === 1) {
            // Tạo một token ngẫu nhiên và lưu trữ vào cơ sở dữ liệu
            $token = bin2hex(random_bytes(16));  // Tạo token ngẫu nhiên dài 32 ký tự

            // Giả sử hàm send_reset_email() sẽ gửi email cho người dùng với liên kết chứa token
            send_reset_email($username, $token);

            // Sau khi gửi email, thông báo cho người dùng một cách an toàn
            $success = true;
        } else {
            // Không tiết lộ thông tin cụ thể về người dùng, chỉ thông báo chung
            $error = true;
        }
    }

    // Hàm gửi email với liên kết chứa token
    function send_reset_email($username, $token) {
        // Giả sử bạn có một hàm gửi email, bạn sẽ tạo liên kết đổi mật khẩu với token
        $reset_link = "https://yourwebsite.com/resetpassword.php?token=" . urlencode($token);
        // Mã gửi email, ví dụ:
        mail($username, "Reset Your Password", "Click here to reset your password: " . $reset_link);
    }
?>

<html>
    <head>
        <title>TUDO/Forgot Username</title>
        <link rel="stylesheet" href="style/style.css">
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <div id="content">
            <form class="center_form" action="forgotusername.php" method="POST">
                <h1>Forgot Username:</h1>
                <p>Forgetting your username can be very frustrating. Unfortunately, we can't just list all the accounts out for everyone 
                to see. What we can do is let you look up your username guesses and we will check if they are in the system. Hopefully it 
                won't take you too long :(</p>
                <input name="username" placeholder="Username"><br><br>
                <input type="submit" value="Send Reset Token"> 

                <!-- Không tiết lộ người dùng có tồn tại hay không, chỉ thông báo chung -->
                <?php if (isset($error) || isset($success)) {
                    echo "<span style='color:blue'>If this username exists, a reset link will be sent to the associated email address.</span>";
                } ?>
                <br><br>
                <?php include('includes/login_footer.php'); ?>
            </form>
        </div>
    </body>
</html>

