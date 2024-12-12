<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_FILES['image']) {
        $validfile = true;

        // Kiểm tra kích thước của file
        $max_file_size = 5 * 1024 * 1024; // Giới hạn 5MB
        if ($_FILES['image']['size'] > $max_file_size) {
            $validfile = false;
            $_SESSION['error'] = 'File quá lớn';
        }

        // Kiểm tra MIME type của file
        $allowed_mime = array("image/gif", "image/png", "image/jpeg");
        $file_mime = $_FILES['image']['type'];
        if (!in_array($file_mime, $allowed_mime)) {
            $validfile = false;
            $_SESSION['error'] = 'Không phải loại hình ảnh hợp lệ (GIF, PNG, JPEG)';
        }

        // Kiểm tra phần mở rộng của file
        $illegal_ext = array("php", "pht", "phtm", "phtml", "phpt", "pgif", "phps", "php2", "php3", "php4", "php5", "php6", "php7", "php16", "inc", "phar");
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (in_array($file_ext, $illegal_ext)) {
            $validfile = false;
            $_SESSION['error'] = 'Extension file không hợp lệ';
        }

        // Kiểm tra file thực sự là một hình ảnh
        $is_check = getimagesize($_FILES['image']['tmp_name']);
        if ($is_check === false) {
            $validfile = false;
            $_SESSION['error'] = 'Không thể kiểm tra hình ảnh';
        }

        if ($validfile) {
            // Lưu file vào thư mục images
            $path = basename($_FILES['image']['name']);
            $title = htmlentities($_POST['title']);

            if (move_uploaded_file($_FILES['image']['tmp_name'], '../images/'.$path)) {
                // Lưu vào cơ sở dữ liệu
                include('../includes/db_connect.php');
                $ret = pg_prepare($db,
                    "createimage_query", "insert into motd_images (path, title) values ($1, $2)");
                $ret = pg_execute($db, "createimage_query", array($path, $title));

                $_SESSION['success'] = 'Tải ảnh lên thành công: ' . $path;
            } else {
                $_SESSION['error'] = 'Lỗi khi tải ảnh lên';
            }
        } else {
            $_SESSION['error'] = 'File không hợp lệ';
        }
    }
}
header('location:/admin/update_motd.php');
die();
?>

