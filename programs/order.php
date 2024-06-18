<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$notification = "";

if (isset($_POST['completeOrder'])) {
    $cartItem = $_SESSION['cartItem'];
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $orderDate = date('Y-m-d H:i:s'); // Получение текущей даты и времени

    // Вставка данных о заказе в таблицу orders
    $insertOrderSql = "INSERT INTO orders (image, name, description, price, quantity, size_id, custom_id, status_id, user_id, full_name, email, order_date) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertOrderSql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param(
        "sssdiisiisss",
        $cartItem['image'], 
        $cartItem['name'], 
        $cartItem['description'], 
        $cartItem['price'], 
        $cartItem['quantity'], 
        $cartItem['size_id'], 
        $cartItem['custom_id'], 
        $cartItem['status_id'], 
        $_SESSION['user_id'], 
        $fullName, 
        $email, 
        $orderDate
    );

    if ($stmt->execute()) {
        // Обновление статуса товара в корзине на "3"
        $updateCartSql = "UPDATE cart SET status_id = 3 WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($updateCartSql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ii", $cartItem['id'], $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // Очистка сессии
        unset($_SESSION['cartItem']);

        $notification = "Заказ успешно завершен!";
    } else {
        $notification = "Ошибка при оформлении заказа. Пожалуйста, попробуйте снова.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="style3.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Castom World</h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="contacts.php">Контакты</a>
    </nav>
</header>
<main>
    <?php if ($notification): ?>
        <div class="notification"><?php echo $notification; ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['cartItem'])): ?>
        <div class="product">
            <img src="<?php echo $_SESSION['cartItem']['image']; ?>" alt="<?php echo $_SESSION['cartItem']['name']; ?>">
            <h2><?php echo $_SESSION['cartItem']['name']; ?></h2>
            <p>Цена: <?php echo $_SESSION['cartItem']['price']; ?> руб.</p>
            <p>Описание: <?php echo $_SESSION['cartItem']['description']; ?></p>
            <p>Количество: <?php echo $_SESSION['cartItem']['quantity']; ?></p>
            <p>Кастом: <?php echo $_SESSION['cartItem']['custom_id']; ?></p>
            <p>Размер: <?php echo $_SESSION['cartItem']['size_id']; ?></p>
            <p>Статус: <?php echo $_SESSION['cartItem']['status_id']; ?></p>
        </div>
        <form action="order.php" method="post">
            <input type="hidden" name="cartId" value="<?php echo $_SESSION['cartItem']['id']; ?>">
            <label for="fullName">ФИО:</label>
            <input type="text" id="fullName" name="fullName" required>
            <label for="email">Почта:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit" name="completeOrder">Завершить</button>
            <button type="button" onclick="window.location.href='cart.php'">Вернуться</button>
        </form>
    <?php else: ?>
        <p>Товар не найден.</p>
    <?php endif; ?>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>
