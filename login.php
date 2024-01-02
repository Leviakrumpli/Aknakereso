<?php

$is_invalid = false;

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $mysqli = require __DIR__ . "/database.php";

    $sql = sprintf("SELECT * FROM user WHERE email = '%s'", $mysqli->real_escape_string($_POST["email"]));

    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();

    if($user)
    {
        if(password_verify($_POST["password"], $user["password_hash"]))
        {
            session_start();

            session_regenerate_id();

            $_SESSION["user_id"] = $user["id"];

            header("Location: index.php");
            exit;
        }
    }

    $is_invalid = true;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login_css.css">
    <title>Login</title>
</head>
<body>
    <div id="csomag">
    <h1>Bejelentkezés</h1>

    <?php if($is_invalid): ?>
        <em>A megadott E-mail cím vagy jelszó helytelen!</em>
    <?php endif; ?>

    <form method="post">
        <label for="email">E-mail</label><br>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"><br>

        <label for="password">Jelszó</label><br>
        <input type="password" name="password" id="password"><br>

        <button>Bejelentkezés</button>
        <a href="signup.html" id="link">Ugrás a regisztrációhoz</a>
    </form>
    </div>
</body>
</html>