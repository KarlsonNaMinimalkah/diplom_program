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

// Обработка ввода пинкода и выдачи заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitPincode'])) {
    $orderId = $_POST['orderId'];
    $orderEmail = $_POST['orderEmail'];
    $pincode = $_POST['pincode'];

    // Проверка пинкода
    $check_sql = "SELECT password_cod FROM orders WHERE id = $orderId";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        $correctPincode = $row['password_cod'];

        if ($pincode == $correctPincode) {
            // Выполняем действие "Выдать заказ"
            $update_sql = "UPDATE orders SET status_id = 6 WHERE id = $orderId";
            if ($conn->query($update_sql) === TRUE) {
                // Отправка письма о том, что заказ был принят
                $subject = "Ваш заказ был выдан";
                $message = "Ваш заказ с ID $orderId был выдан.";
                sendEmail($orderEmail, $subject, $message);
                echo '<script>alert("Заказ выдан успешно.");</script>';
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } else {
            $pincodeError = "Неверный пинкод";
        }
    } else {
        $pincodeError = "Заказ не найден или неверный пинкод";
    }
}

// Фильтрация данных по введённым критериям
$search = isset($_GET['search']) ? $_GET['search'] : '';
$orders_sql = "SELECT o.*, c.name AS custom_name, c.image AS custom_image, c.work_time AS custom_work_time 
               FROM orders o
               JOIN custom c ON o.custom_id = c.id 
               WHERE status_id = 5
               AND (o.full_name LIKE '%$search%' OR o.email LIKE '%$search%' OR o.id LIKE '%$search%')"; 
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
    <title>Выдача заказа</title>
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
            height: 800px; /* Фиксированная высота для карточек */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            flex: 0 0 auto; /* Карточки не будут сжиматься */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .order-card img {
            width: 100%;
            height: 150px; /* Фиксированная высота для изображений */
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
            flex-direction: column;
            margin-top: 10px;
        }
        .order-card button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 5px;
        }
        .order-card button.delete {
            background-color: #e74c3c;
            color: white;
        }
        .order-card button.accept {
            background-color: #2ecc71;
            color: white;
        }
        .error-message {
            color: red;
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


    <h2>Выдача заказа</h2>

    <!-- Форма поиска -->
    <form method="get" action="">
        <label for="search">Поиск:</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Найти</button>
    </form>

    <div class="orders-container">
        <?php if ($orders_result->num_rows > 0): ?>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <div class="order-card">
                    <h3><?php echo $order['name']; ?></h3>
                    <img src="<?php echo $order['image']; ?>" alt="<?php echo $order['name']; ?>">
                    <p><strong>Кастомизация:</strong> <?php echo $order['custom_name']; ?></p>
                    <img src="<?php echo $order['custom_image']; ?>" alt="<?php echo $order['custom_name']; ?>">
                    <p><strong>Цена:</strong> <?php echo $order['price']; ?> руб.</p>
                    <p><strong>ФИО:</strong> <?php echo $order['full_name']; ?></p>
                    <p><strong>Количество:</strong> <?php echo $order['quantity']; ?></p>
                    <p><strong>Размер:</strong> <?php echo isset($sizes[$order['size_id']]) ? $sizes[$order['size_id']] : 'Неизвестно'; ?></p>
                    <p><strong>Почта:</strong> <?php echo $order['email']; ?></p>
                    <p><strong>Дата оформления:</strong> <?php echo $order['order_date']; ?></p>
                    
                    <form action="" method="post">
                        <input type="hidden" name="orderId" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="orderEmail" value="<?php echo $order['email']; ?>">
                        <label for="pincode">Введите пинкод:</label>
                        <input type="text" id="pincode" name="pincode" required>
                        <button type="submit" name="submitPincode">Подтвердить</button>
                    </form>

                    <?php if (isset($pincodeError)): ?>
                        <p class="error-message"><?php echo $pincodeError; ?></p>
                    <?php endif; ?>
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
