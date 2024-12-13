<?php 
session_start();

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /login.php');
    exit();
}

// Xử lý form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['description'])) {
        $error = 'Description cannot be empty';  // Thông báo lỗi khi description rỗng
    } else {
        // Làm sạch và mã hóa đầu vào để ngăn chặn XSS
        $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
        
        include('includes/db_connect.php');
        
        // Chuẩn bị và thực thi truy vấn SQL với bảo vệ SQL Injection
        $ret = pg_prepare($db, "updatedescription_query", "UPDATE users SET description = $1 WHERE username = $2");
        
        if ($ret) {
            $result = pg_execute($db, "updatedescription_query", Array($description, $_SESSION['username']));
            if ($result) {
                $success = true;  // Thông báo thành công
            } else {
                $error = 'Failed to update description';  // Thông báo khi không cập nhật được
            }
        } else {
            $error = 'Failed to prepare query';  // Thông báo khi truy vấn không được chuẩn bị
        }
    }
}

?>

<html>
<head>
    <title>TUDO/My Profile</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div id="content">
        <?php
        include('includes/db_connect.php');
        $ret = pg_prepare($db, "selectprofile_query", "SELECT * FROM users WHERE username = $1;");
        $ret = pg_execute($db, "selectprofile_query", Array($_SESSION['username']));
        $row = pg_fetch_row($ret);

        // Mã hóa dữ liệu đầu ra để ngăn chặn XSS khi hiển thị
        $safe_description = htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8');
        ?>
        <h1>My Profile:</h1>
        <form action="profile.php" method="POST">
            <label for="username">Username: </label>
            <input name="username" value="<?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?>" disabled><br><br>
            <label for="password">Password: </label>
            <input name="password" value="<?php echo htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8'); ?>" disabled><br><br>
            <label for="description">Description: </label>
            <input name="description" value="<?php echo $safe_description; ?>"><br><br>
            <input type="submit" value="Update"> 

            <?php 
            // Hiển thị thông báo thành công hoặc lỗi
            if (isset($error)) {
                echo '<span style="color:red">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</span>';
            } elseif (isset($success)) {
                echo '<span style="color:green">Success</span>';
            }
            ?>
        </form>
    </div>
</body>
</html>
