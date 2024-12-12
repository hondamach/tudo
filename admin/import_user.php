<?php
    include('../includes/utils.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userObj = $_POST['userobj'];
        if ($userObj !== "") {
            // Kiểm tra nếu userObj là đối tượng của lớp User hợp lệ
            $user = unserialize($userObj, ["allowed_classes" => ["User"]]); // Chỉ cho phép lớp User được unserialize
            if ($user !== false && $user instanceof User) {
                include('../includes/db_connect.php');
                $ret = pg_prepare($db,
                    "importuser_query", "insert into users (username, password, description) values ($1, $2, $3)");
                $ret = pg_execute($db, "importuser_query", array($user->username, $user->password, $user->description));
            }
        }
    }
    header('location:/index.php');
    die();
?>
