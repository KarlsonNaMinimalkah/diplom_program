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

if (isset($_POST['removeFromCart'])) {
    $cartId = $_POST['cartId'];
    $deleteSql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteSql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Получение всех товаров из корзины с учетом статуса 1
$sql = "SELECT cart.id AS id, cart.image, cart.name, cart.price, custom.name AS custom_name, size.size AS size_name, 
        cart.custom_id, cart.size_id 
        FROM cart 
        JOIN custom ON cart.custom_id = custom.id 
        JOIN size ON cart.size_id = size.id 
        WHERE cart.user_id = ? AND cart.status_id = 1"; // Добавлено условие на status_id
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = []; // Массив для хранения всех товаров в корзине

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = [
            'id' => $row['id'],
            'image' => $row['image'],
            'name' => $row['name'],
            'price' => $row['price'],
            'custom_name' => $row['custom_name'],
            'size_name' => $row['size_name'],
            'custom_id' => $row['custom_id'],
            'size_id' => $row['size_id']
        ];
    }
} else {
    $notification = "Корзина пуста или нет товаров с доступным статусом.";
}

// Запрос на количество товаров в корзине с учетом статуса 1
$cartCountSql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ? AND status_id = 1";
$stmt = $conn->prepare($cartCountSql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$countResult = $stmt->get_result();
$cartCount = $countResult->fetch_assoc()['count'];
$stmt->close();

// Вычисление общей суммы всех товаров в корзине с учетом статуса 1
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['price'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .total-info {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
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
    <?php if (!empty($cartItems)): ?>
        <?php foreach ($cartItems as $item): ?>
            <div class="product">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                <h2><?php echo $item['name']; ?></h2>
                <p>ID товара: <?php echo $item['id']; ?></p> <!-- Вывод ID товара -->
                <p>Цена: <?php echo $item['price']; ?> руб.</p>
                <p>Кастом: <?php echo $item['custom_name']; ?></p>
                <p>Размер: <?php echo $item['size_name']; ?></p>
                <div class="actions">
                    <form action="cart.php" method="post" style="display: inline;">
                        <input type="hidden" name="cartId" value="<?php echo $item['id']; ?>">
                        <button type="submit" name="removeFromCart">Удалить</button>
                    </form>
                    <form action="order.php" method="post" style="display: inline;">
                        <input type="hidden" name="cartId" value="<?php echo $item['id']; ?>">
                        <button type="submit" name="placeOrder">Оформить заказ</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Корзина пуста или нет товаров с доступным статусом.</p>
    <?php endif; ?>
    <div class="total-info">
        <p>Общее количество товаров в корзине: <?php echo $cartCount; ?></p>
        <p>Общая сумма всех товаров: <?php echo $totalPrice; ?> руб.</p>
    </div>
</main>
<footer>
    
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>
