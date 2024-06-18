<?php
session_start();

// Предположим, что $cartItem содержит данные о товаре из базы данных или другого источника
$cartItem = [
    'id' => 1,
    'image' => 'path/to/image.jpg',
    'name' => 'Название товара',
    'description' => 'Описание товара',
    'price' => 1000.00,
    'quantity' => 1,
    'size_id' => 2,
    'custom_id' => 3,
    'status_id' => 1, // или другие данные, необходимые для оформления заказа
];

$_SESSION['cartItem'] = $cartItem;

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

// Обработка удаления товара из корзины
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

// Получение количества товаров в корзине
$cartCountSql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cartCountSql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$countResult = $stmt->get_result();
$cartCount = $countResult->fetch_assoc()['count'];
$stmt->close();

// Получение информации о пользователе
$userSql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($userSql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

// Обработка выхода пользователя
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Выборка товаров из корзины
$sql = "SELECT cart.id AS cart_id, cart.image, cart.name, cart.price, custom.name AS custom_name, size.size AS size_name 
        FROM cart 
        JOIN custom ON cart.custom_id = custom.id 
        JOIN size ON cart.size_id = size.id 
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <!-- ... (ваш код для заголовка) -->
</header>
<main>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="product">
                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h2><?php echo $row['name']; ?></h2>
                <p>Цена: <?php echo $row['price']; ?> руб.</p>
                <p>Кастом: <?php echo $row['custom_name']; ?></p>
                <p>Размер: <?php echo $row['size_name']; ?></p>

                <div class="actions">
                    <!-- Кнопка удаления из корзины -->
                    <form action="cart.php" method="post" style="display: inline;">
                        <input type="hidden" name="cartId" value="<?php echo $row['cart_id']; ?>">
                        <button type="submit" name="removeFromCart">Удалить</button>
                    </form>

                    <!-- Кнопка оформления заказа -->
                    <form action="order.php" method="get" style="display: inline;">
                        <input type="hidden" name="cartId" value="<?php echo $row['cart_id']; ?>">
                        <button type="submit" name="placeOrder">Оформить заказ</button>
                    </form>
                </div>
            </div>
            <?php
        }
    } else {
        echo "Корзина пуста.";
    }
    $conn->close();
    ?>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>
