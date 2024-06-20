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
    <title>Заказы</title>
    <link rel="stylesheet" href="style.css">
    <style>
    img {
        width: 100%;
        display: block;
        margin: 0 auto; /* Центрируем изображения внутри их контейнеров */
    }

    .order-card {
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 20px;
        display: inline-block;
        vertical-align: top;
        width: 100%;
    }

    .ready {
        background-color: lightgreen;
    }

    .issued {
        background-color: lightgray;
    }

    .not-ready {
        background-color: lightgoldenrodyellow;
    }
</style>

</head>
<body>
<header>
    <h1>Castom World</h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="user_order.php">Заказы</a>
    </nav>
    <div class="header-bottom">
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
        </div>
    </div>
</header>
<main>
    <?php
    // SQL запрос для получения всех заказов пользователя с информацией
    $orders_sql = "SELECT o.*, c.name AS custom_name, c.image AS custom_image, c.work_time AS custom_work_time 
                   FROM orders o
                   JOIN custom c ON o.custom_id = c.id
                   WHERE o.user_id = $user_id";
    $orders_result = $conn->query($orders_sql);

    $ready_orders = [];
    $issued_orders = [];
    $other_orders = [];

    if ($orders_result->num_rows > 0) {
        while ($order = $orders_result->fetch_assoc()) {
            if ($order['status_id'] == 5) {
                $ready_orders[] = $order;
            } else if ($order['status_id'] == 6) {
                $issued_orders[] = $order;
            } else {
                $other_orders[] = $order;
            }
        }
    } else {
        echo "Нет заказов.";
    }
    ?>

    <div class="order-section">
        <div class="order-header">Готовые к выдаче</div>
        <?php if (!empty($ready_orders)): ?>
            <?php foreach ($ready_orders as $order): ?>
                <div class="order-card ready" style="margin-right: 10px;">
                    <h2>Заказ ID: <?php echo $order['id']; ?></h2>
                    <img src="<?php echo $order['image']; ?>" alt="<?php echo $order['name']; ?>">
                    <p> <?php echo $order['name']; ?></p>
                    <img src="<?php echo $order['custom_image']; ?>" alt="<?php echo $order['custom_name']; ?>">
                    <p><strong>Кастомизация:</strong> <?php echo $order['custom_name']; ?></p>
                    <p><strong>Цена:</strong> <?php echo $order['price']; ?> руб.</p>
                    <p><strong>ФИО:</strong> <?php echo $order['full_name']; ?></p>
                    <p><strong>Количество:</strong> <?php echo $order['quantity']; ?></p>
                    <p><strong>Размер:</strong> <?php echo isset($sizes[$order['size_id']]) ? $sizes[$order['size_id']] : 'Неизвестно'; ?></p>
                    <p><strong>Почта:</strong> <?php echo $order['email']; ?></p>
                    <p><strong>Дата оформления:</strong> <?php echo $order['order_date']; ?></p>
                    <br>
                    <h1><strong>Пинкод для получения:</strong> <?php echo $order['password_cod']; ?></h1>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет готовых к выдаче заказов.</p>
        <?php endif; ?>
    </div>

    <div class="order-section">
        <div class="order-header">Выданные заказы</div>
        <?php if (!empty($issued_orders)): ?>
            <?php foreach ($issued_orders as $order): ?>
                <div class="order-card issued">
                    <h2>Заказ ID: <?php echo $order['id']; ?></h2>
                    <img src="<?php echo $order['image']; ?>" alt="<?php echo $order['name']; ?>">
                    <p> <?php echo $order['name']; ?></p>
                    <img src="<?php echo $order['custom_image']; ?>" alt="<?php echo $order['custom_name']; ?>">
                    <p><strong>Кастомизация:</strong> <?php echo $order['custom_name']; ?></p>
                    <p><strong>Цена:</strong> <?php echo $order['price']; ?> руб.</p>
                    <p><strong>ФИО:</strong> <?php echo $order['full_name']; ?></p>
                    <p><strong>Количество:</strong> <?php echo $order['quantity']; ?></p>
                    <p><strong>Размер:</strong> <?php echo isset($sizes[$order['size_id']]) ? $sizes[$order['size_id']] : 'Неизвестно'; ?></p>
                    <p><strong>Почта:</strong> <?php echo $order['email']; ?></p>
                    <p><strong>Дата оформления:</strong> <?php echo $order['order_date']; ?></p>
                    <p><strong>Статус:</strong> Выдан</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет выданных заказов.</p>
        <?php endif; ?>
    </div>

    <div class="order-section">
        <div class="order-header">Остальные заказы</div>
        <?php if (!empty($other_orders)): ?>
            <?php foreach ($other_orders as $order): ?>
                <div class="order-card not-ready">
                    <h2>Заказ ID: <?php echo $order['id']; ?></h2>
                    <img src="<?php echo $order['image']; ?>" alt="<?php echo $order['name']; ?>">
                    <p> <?php echo $order['name']; ?></p>
                    <img src="<?php echo $order['custom_image']; ?>" alt="<?php echo $order['custom_name']; ?>">
                    <p><strong>Кастомизация:</strong> <?php echo $order['custom_name']; ?></p>
                    <p><strong>Цена:</strong> <?php echo $order['price']; ?> руб.</p>
                    <p><strong>ФИО:</strong> <?php echo $order['full_name']; ?></p>
                    <p><strong>Количество:</strong> <?php echo $order['quantity']; ?></p>
                    <p><strong>Размер:</strong> <?php echo isset($sizes[$order['size_id']]) ? $sizes[$order['size_id']] : 'Неизвестно'; ?></p>
                    <p><strong>Почта:</strong> <?php echo $order['email']; ?></p>
                    <p><strong>Дата оформления:</strong> <?php echo $order['order_date']; ?></p>
                    <p><strong>Статус:</strong> <?php echo $order['status_id']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет остальных заказов.</p>
        <?php endif; ?>
    </div>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>

<?php
$conn->close();
?>
