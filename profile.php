<?php 
    session_start();
    if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] == true) {
        header('location: /login.php');
        die();
    } 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['description'])) {
            $error = true;
        }
        else {
            // Làm sạch đầu vào từ người dùng
            $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
            
            include('includes/db_connect.php');
            $ret = pg_prepare($db, "updatedescription_query", "update users set description = $1 where username = $2");
            $ret = pg_execute($db, "updatedescription_query", Array($description, $_SESSION['username']));
            $success = true;
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
                $ret = pg_prepare($db, "selectprofile_query", "select * from users where username = $1;");
                $ret = pg_execute($db, "selectprofile_query", Array($_SESSION['username']));
                $row = pg_fetch_row($ret);

                // Mã hóa đầu ra của dữ liệu người dùng để tránh XSS
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
                    if (isset($error)) {
                        echo '<span style="color:red">Error</span>';
                    } else if (isset($success)) {
                        echo '<span style="color:green">Success</span>';
                    }
                ?>
            </form>
        </div>
    </body>
</html>

