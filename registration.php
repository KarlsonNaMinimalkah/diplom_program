<?php
// Подключение к базе данных (предполагается, что у тебя уже есть соединение)
// Замени параметры соединения на свои
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка данных из формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Готовим и выполняем запрос для вставки данных в базу данных
    $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";

    if ($conn->query($sql) === TRUE) {
        // Перенаправление на страницу авторизации
        header("Location: login.php");
        exit(); // Завершаем выполнение скрипта, чтобы убедиться, что дальнейшего вывода нет
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Закрытие соединения с базой данных
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="orange">
    <div class="container">
        <h2>Registration</h2>
        <form action="registration.php" method="post" class="vhod">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <button type="submit" class="reg">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
