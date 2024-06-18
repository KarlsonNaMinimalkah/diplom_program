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
$orderDetails = [];

if (isset($_GET['cartId'])) {
    $cartId = $_GET['cartId'];
    $sql = "SELECT * FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItem = $result->fetch_assoc();
    $stmt->close();
}

if (isset($_POST['completeOrder'])) {
    $cartId = $_POST['cartId'];
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];

    // Вставка данных в таблицу orders
    $insertSql = "INSERT INTO orders (image, name, description, price, quantity, size_id, custom_id, status_id, user_id, full_name, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sssdiiiiiss", $cartItem['image'], $cartItem['name'], $cartItem['description'], $cartItem['price'], $cartItem['quantity'], $cartItem['size_id'], $cartItem['custom_id'], $cartItem['status_id'], $_SESSION['user_id'], $fullName, $email);
    $stmt->execute();
    $stmt->close();

    // Удаление товара из корзины
    $deleteSql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteSql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    $notification = "Ваш заказ успешно оформлен!";
    $orderDetails = [
        'image' => $cartItem['image'],
        'name' => $cartItem['name'],
        'description' => $cartItem['description'],
        'price' => $cartItem['price'],
        'quantity' => $cartItem['quantity'],
        'size_id' => $cartItem['size_id'],
        'custom_id' => $cartItem['custom_id'],
        'status_id' => $cartItem['status_id'],
        'user_id' => $_SESSION['user_id'],
        'full_name' => $fullName,
        'email' => $email
    ];
}

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
    <?php if (isset($cartItem)): ?>
        <div class="product">
            <img src="<?php echo $cartItem['image']; ?>" alt="<?php echo $cartItem['name']; ?>">
            <h2><?php echo $cartItem['name']; ?></h2>
            <p>Цена: <?php echo $cartItem['price']; ?> руб.</p>
            <p>Описание: <?php echo $cartItem['description']; ?></p>
            <p>Количество: <?php echo $cartItem['quantity']; ?></p>
            <p>Кастом: <?php echo $cartItem['custom_id']; ?></p>
            <p>Размер: <?php echo $cartItem['size_id']; ?></p>
            <p>Статус: <?php echo $cartItem['status_id']; ?></p>
        </div>
        <form action="order.php" method="post">
            <input type="hidden" name="cartId" value="<?php echo $cartItem['id']; ?>">
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

<?php if (!empty($orderDetails)): ?>
    <div class="order-summary">
        <h2>Детали заказа:</h2>
        <img src="<?php echo $orderDetails['image']; ?>" alt="<?php echo $orderDetails['name']; ?>">
        <p>Название: <?php echo $orderDetails['name']; ?></p>
        <p>Описание: <?php echo $orderDetails['description']; ?></p>
        <p>Цена: <?php echo $orderDetails['price']; ?> руб.</p>
        <p>Количество: <?php echo $orderDetails['quantity']; ?></p>
        <p>Размер: <?php echo $orderDetails['size_id']; ?></p>
        <p>Кастом: <?php echo $orderDetails['custom_id']; ?></p>
        <p>Статус: <?php echo $orderDetails['status_id']; ?></p>
        <p>ФИО: <?php echo $orderDetails['full_name']; ?></p>
        <p>Почта: <?php echo $orderDetails['email']; ?></p>
        <p>Дата заказа: <?php echo date("Y-m-d H:i:s"); ?></p>
    </div>
<?php endif; ?>
</body>
</html>
