<?php
session_start();
include('includes/db_connect.php');

// Constants
define('MAX_ATTEMPTS', 5);
define('TOKEN_EXPIRY_TIME', 3600); // Token hết hạn sau 1 giờ

// Giới hạn số lần thử
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
    echo 'Too many attempts. Please try again later.';
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ POST
    $token = $_POST['token'] ?? null;
    $password1 = $_POST['password1'] ?? null;
    $password2 = $_POST['password2'] ?? null;

    if (!$token || !$password1 || !$password2) {
        echo 'Invalid request';
        die();
    }

    if ($password1 !== $password2) {
        echo '<h1 style="color:red">Passwords do not match.</h1>';
        $_SESSION['attempts']++;
        die();
    }

    // Kiểm tra token
    $stmt = pg_prepare($db, "checktoken_query", "SELECT * FROM tokens WHERE token = $1");
    $result = pg_execute($db, "checktoken_query", array($token));
    if (pg_num_rows($result) === 0) {
        echo '<h1 style="color:red">The reset token is invalid or expired.</h1>';
        $_SESSION['attempts']++;
        die();
    }

    // Kiểm tra hạn sử dụng token
    $row = pg_fetch_assoc($result);
    $created_at = strtotime($row['created_at']);
    if (time() - $created_at > TOKEN_EXPIRY_TIME) {
        echo '<h1 style="color:red">The reset token has expired.</h1>';
        $_SESSION['attempts']++;
        die();
    }

    // Cập nhật mật khẩu
    $uid = $row['user_id'];
    $hashed_password = password_hash($password1, PASSWORD_BCRYPT);

    $stmt = pg_prepare($db, "changepassword_query", "UPDATE users SET password = $1 WHERE id = $2");
    pg_execute($db, "changepassword_query", array($hashed_password, $uid));

    // Xóa token sau khi sử dụng
    $stmt = pg_prepare($db, "deletetoken_query", "DELETE FROM tokens WHERE token = $1");
    pg_execute($db, "deletetoken_query", array($token));

    echo "<h1 style='color:green'>Password changed successfully!</h1>";
    die();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="content">
        <form class="center_form" action="resetpassword.php" method="POST">
            <h1>Reset Password</h1>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
            <input type="password" name="password1" placeholder="New password" required><br><br>
            <input type="password" name="password2" placeholder="Confirm password" required><br><br>
            <input type="submit" value="Change password">
        </form>
    </div>
    <?php include('includes/login_footer.php'); ?>
</body>
</html>

