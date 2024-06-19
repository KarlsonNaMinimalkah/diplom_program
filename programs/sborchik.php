<?php
session_start();

// Проверка, залогинен ли специальный пользователь с id 10
if (!isset($_SESSION['special_user_id']) || $_SESSION['special_user_id'] != 10) {
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

// Функция для отправки писем
function sendEmail($to, $subject, $message) {
    $headers = "From: no-reply@worldofcustomclothing.com";
    mail($to, $subject, $message, $headers);
}

// Обработка удаления заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteOrder'])) {
    $orderId = $_POST['orderId'];
    $orderEmail = $_POST['orderEmail'];

    // Удаление заказа
    $delete_sql = "DELETE FROM orders WHERE id = $orderId";
    if ($conn->query($delete_sql) === TRUE) {
        // Отправка письма о том, что заказ был отменен
        $subject = "Ваш заказ был отменен";
        $message = "Ваш заказ с ID $orderId был отменен.";
        sendEmail($orderEmail, $subject, $message);
    } else {
        echo "Error: " . $conn->error;
    }
}

// Обработка принятия заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acceptOrder'])) {
    $orderId = $_POST['orderId'];
    $orderEmail = $_POST['orderEmail'];

    // Обновление статуса заказа
    $update_sql = "UPDATE orders SET status_id = 4 WHERE id = $orderId";
    if ($conn->query($update_sql) === TRUE) {
        // Отправка письма о том, что заказ был принят
        $subject = "Ваш заказ был принят";
        $message = "Ваш заказ с ID $orderId был принят. Ожидайте готовности.";
        sendEmail($orderEmail, $subject, $message);
    } else {
        echo "Error: " . $conn->error;
    }
}

// Получение всех заказов с информацией из таблицы custom
$orders_sql = "SELECT o.*, c.name AS custom_name, c.image AS custom_image, c.work_time AS custom_work_time 
               FROM orders o
               JOIN custom c ON o.custom_id = c.id 
               WHERE status_id = 1"; // добавили условие WHERE для фильтрации по статусу заказа
$orders_result = $conn->query($orders_sql);

// Массив соответствия числовых значений размеров текстовым меткам
$sizes = [
    1 => 'M',
    2 => 'L',
    3 => 'XL'
    // Добавьте сюда другие размеры по мере необходимости
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница с заказами</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
        }
        .order-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px;
            width: 300px; /* Фиксированная ширина для карточек */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            flex: 0 0 auto; /* Карточки не будут сжиматься */
        }
        .order-card img {
            width: 100%;
            height: 200px; /* Фиксированная высота для изображений */
            object-fit: cover; /* Изображение сохраняет пропорции, но обрезается, чтобы заполнить блок */
            border-radius: 10px;
        }
        .order-card h3 {
            margin: 10px 0;
        }
        .order-card p {
            margin: 5px 0;
        }
        .order-card .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .order-card button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .order-card button.delete {
            background-color: #e74c3c;
            color: white;
        }
        .order-card button.accept {
            background-color: #2ecc71;
            color: white;
        }
    </style>
</head>
<body>
<header>
    <h1>Castom World</h1>
    <nav>
        <a href="sborchik.php">Прием на сборку</a>
        <a href="sborka.php">Сборка</a>
        <a href="vidacha.php">Выдача</a>
    </nav>
    <div class="header-bottom">
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                <span>Привет, <?php echo $_SESSION['special_username']; ?></span>
                    <div class="dropdown-content">
                        <form method="post" action="../login.php"> <!-- Форма для выхода -->
                            <button type="submit" name="logout">Выход</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

    <h2>Список заказов</h2>
    <div class="orders-container">
        <?php if ($orders_result->num_rows > 0): ?>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <div class="order-card">
                    <img src="<?php echo $order['image']; ?>" alt="<?php echo $order['name']; ?>">
                    <h3><?php echo $order['name']; ?></h3>
                    <p><strong>Количество:</strong> <?php echo $order['quantity']; ?></p>
                    <p><strong>Размер:</strong> <?php echo isset($sizes[$order['size_id']]) ? $sizes[$order['size_id']] : 'Неизвестно'; ?></p>
                    <p><strong>Дата оформления:</strong> <?php echo $order['order_date']; ?></p>
                    <img src="<?php echo $order['custom_image']; ?>" alt="<?php echo $order['name']; ?>">
                    <p><strong>Кастомизация:</strong> <?php echo $order['custom_name']; ?></p>
                    <p><strong>Время работы:</strong> <?php echo $order['custom_work_time']; ?> часов</p>
                    <div class="actions">
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="orderId" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="orderEmail" value="<?php echo $order['email']; ?>">
                            <button type="submit" name="deleteOrder" class="delete">Удалить</button>
                        </form>
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="orderId" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="orderEmail" value="<?php echo $order['email']; ?>">
                            <button type="submit" name="acceptOrder" class="accept">Принять</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Нет доступных заказов.</p>
        <?php endif; ?>
    </div>

<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
</body>
</html>

<?php
$conn->close();
?>
