<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Castom World</h1>
    <nav>
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="contakts.php">Контакты</a>
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
<div id="notification"></div>

<main>
    <?php
    
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

    // Выполнение запроса к базе данных для выборки товаров
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);

    // Проверка, есть ли результаты запроса
    if ($result->num_rows > 0) {
        // Вывод данных каждой строки
        while($row = $result->fetch_assoc()) {
            // Получаем значение status из таблицы status_quantity по указанному status_id
            $status_sql = "SELECT status FROM status_quantity WHERE ID = " . $row['status_id'];
            $status_result = $conn->query($status_sql);
            // Проверяем, есть ли результат запроса
            if ($status_result->num_rows > 0) {
                // Получаем значение status
                $status_row = $status_result->fetch_assoc();
                $status = $status_row['status'];
            } else {
                $status = "Статус не найден"; // Если статус не найден, выводим соответствующее сообщение
            }
            ?>
            <div class="product">
                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h2><?php echo $row['name']; ?></h2>
                <p><?php echo $row['description']; ?></p>
                <h2><?php echo $row['price']; ?></h2>
                <!-- Вывод кнопок размеров -->
                <p>
                    <!-- Используем кнопки размеров с соответствующими значениями -->
                    <button class="size-btn" data-size="1">M</button>
                    <button class="size-btn" data-size="2">L</button>
                    <button class="size-btn" data-size="3">XL</button>
                </p>

                <!-- Скрытое поле для хранения выбранного размера -->
                <input type="hidden" class="selected-size" name="selected-size" value="">

                <!-- Вывод статуса -->
                <p>Status: <?php echo $status; ?></p>

                <!-- Кнопка добавления в корзину -->
                <form action="index.php" method="post">
                    <input type="hidden" name="productId" value="<?php echo $row['id']; ?>">
                    <input type="hidden" class="selected-size" name="selectedSize" value="">
                    <input type="hidden" name="quantity" id="quantity" value="1"> <!-- Значение quantity -->
                    <button type="submit" name="addToCart">Добавить в корзину</button>
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
    // Находим элемент с классом "cart"
    var cartButton = document.querySelector('.cart img');

    // Обработчик клика по кнопке корзины
    cartButton.addEventListener('click', function() {
        // Переходим на страницу корзины
        window.location.href = "cart.php";
    });

    // Получаем все кнопки размеров
    var sizeButtons = document.querySelectorAll('.size-btn');

    // Обработчик клика по кнопкам размеров
    sizeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Запоминаем выбранный размер в атрибуте data-size
            var selectedSize = this.getAttribute('data-size');

            // Записываем выбранный размер в скрытое поле
            document.querySelector('.selected-size').value = selectedSize;

            // Устанавливаем значение quantity в зависимости от выбранного размера
            var quantityInput = document.getElementById('quantity');
            var quantitySize = 'quantity_' + selectedSize;
            quantityInput.value = "<?php echo $row[$quantitySize]; ?>";

            // Устанавливаем цвет кнопки на черный
            sizeButtons.forEach(function(btn) {
                btn.style.backgroundColor = ""; // Сбрасываем цвет для всех кнопок
            });
            this.style.backgroundColor = "black"; // Устанавливаем цвет текущей кнопки на черный
        });
    });

</script>

<?php
// SQL запрос для вставки данных в таблицу cart
if (isset($_POST['addToCart'])) {
    // Получаем данные из формы
    $productId = $_POST['productId'];
    $selectedSize = $_POST['selectedSize'];
    $quantity = $_POST['quantity'];
    $status_id = 1; // Значение ID статуса (предположим, что 1 соответствует "активно")

    // SQL запрос для вставки данных в таблицу cart
    $insertSql = "INSERT INTO cart (image, name, description, price, quantity, size_id, custom_id, status_id)
                      VALUES ('{$row['image']}', '{$row['name']}', '{$row['description']}', '{$row['price']}', 1, $selectedSize, 1, $status_id)";

    // Выполнение запроса
    if ($conn->query($insertSql) === TRUE) {
        echo "Товар успешно добавлен в корзину";
    } else {
        echo "Ошибка: " . $conn->error;
    }
}

// Закрытие соединения с базой данных
$conn->close();
?>

</body>
</html>
