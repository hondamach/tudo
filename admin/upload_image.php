<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $validfile = true;

        // Kiểm tra kích thước file (giới hạn 2MB)
        $max_file_size = 2 * 1024 * 1024; // 2MB
        if ($_FILES['image']['size'] > $max_file_size) {
            $validfile = false;
            echo 'File size exceeds 2MB<br>';
        }

        // Kiểm tra nội dung file bằng getimagesize()
        $image_info = getimagesize($_FILES['image']['tmp_name']);
        if ($image_info === false) {
            $validfile = false;
            echo 'File is not a valid image<br>';
        }

        // Kiểm tra extension và mime type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_mime = $image_info['mime']; // Dùng mime từ getimagesize để tăng tính chính xác

        if (!in_array($file_extension, $allowed_extensions)) {
            $validfile = false;
            echo 'Invalid file extension<br>';
        }
        if (!in_array($file_mime, $allowed_mime_types)) {
            $validfile = false;
            echo 'Invalid mime type<br>';
        }

        if ($validfile) {
            // Tạo tên file an toàn
            $unique_name = uniqid('img_', true) . '.' . $file_extension;
            $upload_path = '../images/' . $unique_name;

            // Escape title trước khi lưu vào database
            $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');

            // Di chuyển file upload
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Lưu vào database
                include('../includes/db_connect.php');

                $query = "INSERT INTO motd_images (path, title) VALUES ($1, $2)";
                $ret = pg_prepare($db, "createimage_query", $query);
                $ret = pg_execute($db, "createimage_query", [$unique_name, $title]);

                if ($ret) {
                    echo 'Image uploaded successfully<br>';
                } else {
                    echo 'Failed to save image info to the database<br>';
                }
            } else {
                echo 'Failed to move uploaded file<br>';
            }
        }
    } else {
        echo 'No file uploaded or upload error occurred<br>';
    }

    // Chuyển hướng về update_motd.php
    header('Location: /admin/update_motd.php');
    exit();
}
?>

