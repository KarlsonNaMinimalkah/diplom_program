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

// Запрос на получение всех кастомов
$customs_sql = "SELECT * FROM custom";
$customs_result = $conn->query($customs_sql);
$customs = [];
if ($customs_result->num_rows > 0) {
    while ($row = $customs_result->fetch_assoc()) {
        $customs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказы</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .custom-card {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
            width: 30%;
            cursor: pointer; /* Делаем курсор указателем для указания на кликабельность */
        }
        .custom-card img {
            max-width: 100%;
            height: auto;
        }
        .selected {
            border: 2px solid black; /* Стиль для подсветки */
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
    <div class="custom-content">
        <?php foreach ($customs as $custom): ?>
            <div class="custom-card" data-custom-id="<?php echo $custom['id']; ?>">
                <h3><?php echo $custom['name']; ?></h3>
                <img src="<?php echo $custom['image']; ?>" alt="<?php echo $custom['name']; ?>">
                <p>Среднее время выполнения: <?php echo $custom['work_time']; ?> дней</p>
                <p>Цена: <?php echo $custom['price']; ?> руб.</p>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>

<script>
// Ждем загрузки DOM
document.addEventListener("DOMContentLoaded", function() {
    // Находим все карточки с классом "custom-card"
    var cards = document.querySelectorAll('.custom-card');

    // Для каждой карточки навешиваем обработчик клика
    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            // Снимаем подсветку со всех карточек
            cards.forEach(function(c) {
                c.classList.remove('selected');
            });

            // Добавляем подсветку на кликнутую карточку
            this.classList.add('selected');
        });
    });
});
</script>

</body>
</html>

<?php
$conn->close();
?>
