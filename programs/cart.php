<?php
session_start(); // Включение сессий

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
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// Выборка товаров из корзины
$sql = "SELECT cart.id AS cart_id, cart.image, cart.name, cart.price, custom.name AS custom_name, size.size AS size_name 
        FROM cart 
        JOIN custom ON cart.custom_id = custom.id 
        JOIN size ON cart.size_id = size.id 
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Проверка, есть ли результаты запроса
if ($result->num_rows > 0) {
    // Вывод данных каждой строки
    while($row = $result->fetch_assoc()) {
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
                <form action="order.php" method="post" style="display: inline;">
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

// Закрытие соединения с базой данных
$conn->close();
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
    <h1>Castom World</h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="contacts.php">Контакты</a>
    </nav>
    <div class="header-bottom">
        <div class="search-bar">
            <input type="text" placeholder="Поиск">
            <button>Поиск</button>
        </div>
        <div class="user-actions">
            <a href="../login.php">Вход</a>
            <div class="cart">
                <!-- Кнопка корзины в виде изображения -->
                <a href="cart.php"><img src="cart-icon.png" alt="Корзина"></a>
                <span>0</span>
            </div>
        </div>
    </div>
</header>
<main>
    <!-- Ваш основной контент здесь -->
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>
