<?php
// Khởi động session an toàn
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: /index.php');
    exit();
}

// Xử lý yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if (!empty($username)) {
        // Kết nối đến cơ sở dữ liệu
        include('includes/db_connect.php');

        // Sử dụng prepared statement để tránh SQL Injection
        $stmt = pg_prepare($db, "get_user", "SELECT email FROM users WHERE username = $1");
        $result = pg_execute($db, "get_user", [$username]);

        if ($result && pg_num_rows($result) === 1) {
            // Tạo token ngẫu nhiên
            $token = bin2hex(random_bytes(16)); // Token dài 32 ký tự
            $row = pg_fetch_assoc($result);
            $email = $row['email'];

            // Lưu token vào cơ sở dữ liệu
            $stmt_token = pg_prepare($db, "save_token", "UPDATE users SET reset_token = $1, reset_expiry = $2 WHERE username = $3");
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token hết hạn sau 1 giờ
            pg_execute($db, "save_token", [$token, $expiry, $username]);

            // Gửi email chứa token
            send_reset_email($email, $token);

            $success = true;
        } else {
            // Không tiết lộ thông tin nếu username không tồn tại
            $error = true;
        }
    } else {
        $error = true;
    }
}

// Hàm gửi email chứa token
function send_reset_email($email, $token) {
    $reset_link = "https://a10bmwp11.up.railway.app/resetpassword.php?token=" . urlencode($token);
    $subject = "Reset Your Password";
    $message = "Click the link below to reset your password:\n$reset_link\n\nThis link will expire in 1 hour.";
    $headers = "From: no-reply@yourdomain.com\r\n" .
               "Reply-To: no-reply@yourdomain.com\r\n" .
               "X-Mailer: PHP/" . phpversion();

    mail($email, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <input name="username" placeholder="Username" required><br><br>
            <input type="submit" value="Send Reset Token"> 

            <!-- Không tiết lộ thông tin chi tiết về lỗi -->
            <?php if (isset($error) || isset($success)) {
                echo "<span style='color:blue'>If this username exists, a reset link will be sent to the associated email address.</span>";
            } ?>
            <br><br>
            <?php include('includes/login_footer.php'); ?>
        </form>
    </div>
</body>
</html>
