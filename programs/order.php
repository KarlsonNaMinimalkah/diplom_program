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

if (isset($_POST['placeOrder'])) {
    $cartId = $_POST['cartId'];

    // Получаем информацию о товаре из корзины
    $sql = "SELECT * FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Найден товар в корзине, извлекаем данные
        $cartItem = $result->fetch_assoc();

        // Подготовка данных для вставки в таблицу orders
        $insertOrderSql = "INSERT INTO orders (image, name, description, price, quantity, size_id, custom_id, status_id, user_id, full_name, email, order_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertOrderSql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        // Данные для вставки
        $image = $cartItem['image'];
        $name = $cartItem['name'];
        $description = $cartItem['description'];
        $price = $cartItem['price'];
        $quantity = $cartItem['quantity'];
        $size_id = $cartItem['size_id'];
        $custom_id = $cartItem['custom_id'];
        $status_id = 1; // Установка начального статуса заказа
        $user_id = $_SESSION['user_id'];
        $full_name = $_POST['fullName'];
        $email = $_POST['email'];
        $order_date = date('Y-m-d H:i:s'); // Текущая дата и время

        // Привязка параметров и выполнение запроса
        $stmt->bind_param("sssdiiiiisss", $image, $name, $description, $price, $quantity, $size_id, $custom_id, $status_id, $user_id, $full_name, $email, $order_date);
        if ($stmt->execute()) {
            // Обновляем статус товара в корзине на "3" (заказано)
            $updateCartSql = "UPDATE cart SET status_id = 3 WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($updateCartSql);
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // Очистка данных о корзине в сессии, если это необходимо
            unset($_SESSION['cartItem']);

            $notification = "Заказ успешно оформлен!";
        } else {
            $notification = "Ошибка при оформлении заказа. Пожалуйста, попробуйте снова.";
        }
    } else {
        $notification = "Товар не найден в вашей корзине.";
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
    <?php if (isset($_POST['placeOrder']) && $notification === "Товар не найден в вашей корзине."): ?>
        <p><?php echo $notification; ?></p>
    <?php else: ?>
        <div class="product">
            <img src="<?php echo $cartItem['image']; ?>" alt="<?php echo $cartItem['name']; ?>">
            <h2><?php echo $cartItem['name']; ?></h2>
            <p>Цена: <?php echo $cartItem['price']; ?> руб.</p>
            <p>Описание: <?php echo $cartItem['description']; ?></p>
            <p>Количество: <?php echo $cartItem['quantity']; ?></p>
            <p>Кастом: <?php echo $cartItem['custom_id']; ?></p>
            <p>Размер: <?php echo $cartItem['size_id']; ?></p>
        </div>
        <form action="order.php" method="post">
            <input type="hidden" name="cartId" value="<?php echo $cartId; ?>">
            <label for="fullName">ФИО:</label>
            <input type="text" id="fullName" name="fullName" required>
            <label for="email">Почта:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit" name="placeOrder">Завершить</button>
            <button type="button" onclick="window.location.href='cart.php'">Вернуться</button>
        </form>
    <?php endif; ?>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>
