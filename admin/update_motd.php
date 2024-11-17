<?php
session_start();

// Kiểm tra quyền truy cập của admin
if (!isset($_SESSION['isadmin'])) {
    header('location: /index.php');
    die();
}

// Kiểm tra phương thức POST và xử lý thông điệp MoTD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
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

    // Kiểm tra nếu có ảnh được tải lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];

        // Kiểm tra tệp có phải là ảnh không
        if (in_array($image['type'], $allowed_types)) {
            // Di chuyển tệp ảnh tới thư mục /images
            $upload_dir = '../images/';
            $upload_file = $upload_dir . basename($image['name']);

            // Di chuyển tệp nếu tệp hợp lệ
            if (move_uploaded_file($image['tmp_name'], $upload_file)) {
                // Sau khi tải lên thành công, quay lại trang index.php mà không thông báo
                header('Location: /index.php');
                exit();
            }
        }
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
            <?php 
                if (isset($success)) {
                    echo '<span style="color:green">' . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . '</span>';
                } elseif (isset($error)) {
                    echo '<span style="color:red">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</span>';
                }
            ?>
        </form>
        <br>
        <form class="center_form" action="update_motd.php" method="POST" enctype="multipart/form-data">
            <h1>Upload Images:</h1>
            These images will display under the message of the day. <br><br>
            <input name="title" placeholder="Title" /><br><br>
            <!-- Chỉ cho phép chọn file ảnh -->
            <input type="file" name="image" size="25" accept="image/jpeg, image/png, image/gif, image/jpg" />
            <input type="submit" value="Upload Image">
        </form>
    </div>
</body>
</html>

