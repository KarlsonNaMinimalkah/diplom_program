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

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $query = "SELECT id, username, password FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($password == $user["password"]) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if ($_SESSION['user_id'] == 10) {
                header("Location: /диплом/programs/sborchik.php");
                exit();
            } else {
                header("Location: /диплом/programs/index.php");
                exit();
            }
        } else {
            $error = "Неверный email или пароль";
        }
    } else {
        $error = "Неверный email или пароль";
    }

    $stmt->close();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="black">
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="post" class="vhod">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" class="log">Login</button>
        </form>
        <p><?php echo $error; ?></p>
        <p>Don't have an account? <a href="registration.php">Register</a></p>
    </div>
</body>
</html>
