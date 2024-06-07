<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // После успешной авторизации
if (password_verify($password, $user["password"])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username']; // Сохраняем имя пользователя в сессии
    $user_id = $user['id'];
    header("Location: programs/index.php");
    exit();
}
else {
            $error = "Неверное имя пользователя или пароль";
        }
    } else {
        $error = "Неверное имя пользователя или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="black">
    <div class="container">
        <h2>Login</h2>
        <form action="programs/index.php" method="post" class="vhod">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" class="log">Login</button>
        </form>
        <p>Don't have an account? <a href="registration.php">Register</a></p>
    </div>
    
</body>
</html>
