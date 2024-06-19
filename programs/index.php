<?php
include('config.php');
?>
<?php

session_start();

// Проверка, залогинен ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

// Создание подключения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение информации о пользователе по user_id из сессии
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .product {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }
        .product img {
            max-width: 700px;
            max-height: 700px;
            margin-right: 10px;
        }
        .search-bar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<header>
    <h1>Castom World</h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="user_order.php">Заказы</a>
    </nav>
    <div class="header-bottom">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Поиск" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit">Найти</button>
            </form>
        </div>
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <span>Привет, <?php echo $_SESSION['username']; ?></span>
                    <div class="dropdown-content">
                        <form method="post" action="../login.php"> <!-- Форма для выхода -->
                            <button type="submit" name="logout">Выход</button>
                        </form>
                        <a href="delete_account.php">Удалить аккаунт</a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="cart">
                <a href="cart.php"><img src="cart-icon.png" alt="Корзина"></a>
                <span id="cart-count">0</span>
            </div>
        </div>
    </div>
</header>
<div id="notification"></div>
<main>
    <?php
    // Используемые переменные для поиска
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // SQL запрос для получения продуктов
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
    $result = $conn->query($sql);
    // Проверка наличия результатов
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Получение статуса из таблицы status_quantity
            $status_sql = "SELECT status FROM status_quantity WHERE id = " . $row['status_id'];
            $status_result = $conn->query($status_sql);
            $status = ($status_result->num_rows > 0) ? $status_result->fetch_assoc()['status'] : "Статус не найден";
            ?>
            <div class="product">
                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h2><?php echo $row['name']; ?></h2>
                <p><?php echo $row['description']; ?></p>
                <h2><?php echo $row['price']; ?></h2>
                <p>
                    <button class="size-btn" data-size="1">M</button>
                    <button class="size-btn" data-size="2">L</button>
                    <button class="size-btn" data-size="3">XL</button>
                </p>
                <input type="hidden" class="selected-size" name="selected-size" value="1"> <!-- По умолчанию размер M -->
                <p>Status: <?php echo $status; ?></p>
                <p>ID товара: <?php echo $row['ID']; ?></p> <!-- Выводим ID товара -->
                <form class="add-to-cart-form">
                    <input type="hidden" name="productId" value="<?php echo $row['ID']; ?>">
                    <input type="hidden" class="selected-size" name="selectedSize" value="1"> <!-- По умолчанию размер M -->
                    <input type="hidden" name="quantity" value="1">
                    <button type="button" class="add-to-cart-button">Добавить в корзину</button>
                </form>
            </div>
            <?php
        }
    } else {
        echo "0 результатов";
    }
    ?>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
<script>
    $(document).ready(function() {
        $('.add-to-cart-button').click(function() {
            var productId = $(this).closest('form').find('input[name="productId"]').val();
            var selectedSize = $(this).closest('form').find('input[name="selectedSize"]').val();
            
            // Формируем URL для GET-запроса
            var url = 'add_to_cart.php?productId=' + productId + '&selectedSize=' + selectedSize;

            $.ajax({
                type: 'GET',  // Используем метод GET
                url: url,
                success: function(response) {
                    $('#notification').html('<div class="success-message">Товар успешно добавлен в корзину</div>');
                },
                error: function() {
                    $('#notification').html('<div class="error-message">Ошибка при отправке запроса</div>');
                }
            });
        });

        var sizeButtons = document.querySelectorAll('.size-btn');
        sizeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var selectedSize = this.getAttribute('data-size');
                document.querySelectorAll('.selected-size').forEach(function(input) {
                    input.value = selectedSize;
                });
                sizeButtons.forEach(function(btn) {
                    btn.style.backgroundColor = "";
                });
                this.style.backgroundColor = "black";
            });
        });
    });
</script>

</body>
</html>
