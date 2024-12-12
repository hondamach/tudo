<?php
session_start();

// Kiểm tra quyền truy cập của admin
if (!isset($_SESSION['isadmin'])) {
    header('location: /index.php');
    die();
}

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';

    // Kiểm tra dữ liệu nhập
    if (!empty($message)) {
        // Mã hóa ký tự đặc biệt cơ bản
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        // Mã hóa thêm các ký tự đặc biệt /, {, và }
        $message = str_replace(['/', '{', '}'], ['&#47;', '&#123;', '&#125;'], $message);

        // Mở file và ghi thông điệp
        $t_file = fopen("../templates/motd.tpl", "w");
        if ($t_file) {
            fwrite($t_file, $message);
            fclose($t_file);
            $success = "Message set!";
        } else {
            $error = "Failed to write message.";
        }
    } else {
        $error = "Empty message";
    }
}
?>
<html>
<head>
    <title>TUDO/Update MoTD</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <?php 
        include('../includes/header.php'); 
        include('../includes/db_connect.php');
        
        // Đọc nội dung file template an toàn
        $template = "";
        if (file_exists("../templates/motd.tpl")) {
            $t_file = fopen("../templates/motd.tpl", "r");
            if ($t_file) {
                $template = fread($t_file, filesize("../templates/motd.tpl"));
                fclose($t_file);
            }
        }
    ?>
    <div id="content">
        <form class="center_form" action="update_motd.php" method="POST">
            <h1>Update MoTD:</h1>
            Set a message that will be visible for all users when they log in.<br><br>
            <!-- Giải mã trước khi hiển thị thông điệp -->
            <textarea name="message"><?php echo htmlspecialchars_decode($template, ENT_QUOTES); ?></textarea><br><br>
            <input type="submit" value="Update Message">
        </form>
        <br>
        <form class="center_form" action="upload_image.php" method="POST" enctype="multipart/form-data">
            <h1>Upload Images:</h1>
            These images will display under the message of the day. <br><br>
            <input name="title" placeholder="Title" /><br><br>
            <input type="file" name="image" size="25" />
            <input type="submit" value="Upload Image">
            <?php 
        if (isset($_SESSION['success'])) {
            echo '<span style="color:green">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</span>';
            unset($_SESSION['success']); // Xóa thông báo sau khi đã hiển thị
        } elseif (isset($_SESSION['error'])) {
            echo '<span style="color:red">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</span>';
            unset($_SESSION['error']); // Xóa thông báo sau khi đã hiển thị
        }
            ?>
        </form>
    </div>
</body>
</html>

