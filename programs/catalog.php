<?php
session_start();
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

// Получение категории и поискового запроса
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .product {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
            width: 30%;
        }
        .product img {
            width: 250px;
            height: 200px;
            margin-bottom: 10px;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .category-buttons {
            margin-bottom: 20px;
        }
        .category-buttons button {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
            padding: 8px 16px;
            cursor: pointer;
            border: 1px solid #ccc;
            background-color: #f0f0f0;
            transition: background-color 0.3s ease;
        }
        .category-buttons button:hover {
            background-color: #ddd;
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
        <div class="search-bar">
            <form method="GET" action="catalog.php">
                <input type="text" name="search" placeholder="Поиск" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Найти</button>
            </form>
        </div>
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <span>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="dropdown-content">
                        <form method="post" action="login.php">
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
    <div class="category-buttons" style="display:flex">
        <button class="category-btn" data-category="1">Джинсы</button>
        <button class="category-btn" data-category="2">Кепки</button>
        <button class="category-btn" data-category="3">Понамы</button>
        <button class="category-btn" data-category="4">Джинсовки</button>
        <button class="category-btn" data-category="5">Штаны карго</button>
        <button class="category-btn" data-category="6">Спортивные штаны</button>
        <button class="category-btn" data-category="7">Футболки темные</button>
        <button class="category-btn" data-category="8">Футболки светлые</button>
        <button class="category-btn" data-category="9">Лонгсливы темные</button>
        <button class="category-btn" data-category="10">Лонгсливы светлые</button>
        <button class="category-btn" data-category="11">Худи темные</button>
        <button class="category-btn" data-category="12">Худи светлые</button>
    </div>
    <div id="products-container">
        <?php
        // SQL-запрос для получения продуктов по категории и поисковому запросу
        $sql = "SELECT * FROM products WHERE 1=1";
        if (!empty($category)) {
            $sql .= " AND category_id = " . intval($category);
        }
        if (!empty($search)) {
            $sql .= " AND (name LIKE '%" . $conn->real_escape_string($search) . "%' OR description LIKE '%" . $conn->real_escape_string($search) . "%')";
        }

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Получаем статус из таблицы status_quantity
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
                    <input type="hidden" class="selected-size" name="selected-size" value="1"> <!-- Размер по умолчанию M -->
                    <p>Статус: <?php echo $status; ?></p>
                    <button class="add-to-cart-button">Добавить в корзину</button>
                    <input type="hidden" name="productId" value="<?php echo $row['id']; ?>">
                </div>
                <?php
            }
        } else {
            echo "<p>Нет продуктов в этой категории.</p>";
        }
        ?>
    </div>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
<script>
    $(document).ready(function() {
        // Обработчик клика по кнопкам категорий
        $('.category-btn').click(function() {
            var categoryId = $(this).data('category');
            loadProducts(categoryId);
        });

        // Функция для загрузки продуктов по категории
        function loadProducts(categoryId) {
            var search = '<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>';
            var url = 'catalog.php?category=' + categoryId + '&search=' + search;

            $.ajax({
                type: 'GET',
                url: url,
                success: function(response) {
                    var newContent = $(response).find('#products-container').html();
                    $('#products-container').html(newContent);
                },
                error: function() {
                    $('#products-container').html('<div class="error-message">Ошибка при загрузке продуктов</div>');
                }
            });
        }

        // Обработчик клика по кнопке добавления в корзину
        $(document).on('click', '.add-to-cart-button', function() {
            var productId = $(this).closest('.product').find('input[name="productId"]').val();
            var selectedSize = $(this).closest('.product').find('.selected-size').val();

            var url = 'add_to_cart.php?productId=' + productId + '&selectedSize=' + selectedSize;

            $.ajax({
                type: 'GET',
                url: url,
                success: function(response) {
                    $('#notification').html('<div class="success-message">Товар успешно добавлен в корзину</div>');
                },
                error: function() {
                    $('#notification').html('<div class="error-message">Ошибка при добавлении товара в корзину</div>');
                }
            });
        });

        // Обработчик выбора размера
        $(document).on('click', '.size-btn', function() {
            var selectedSize = $(this).data('size');
            $(this).closest('.product').find('.selected-size').val(selectedSize);
            $('.size-btn').css('background-color', '');
            $(this).css('background-color', 'black');
        });
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
