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
            display: inline-block;
            vertical-align: top;
            width: 30%;
        }
        .product img {
            max-width: 100%;
            height: auto;
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
    <div id="products-container"></div>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
<script>
    $(document).ready(function() {
        // Загружаем все продукты при первой загрузке страницы
        loadProducts('');

        // Обработчик клика по кнопкам категорий
        $('.category-btn').click(function() {
            var categoryId = $(this).data('category');
            loadProducts(categoryId);
        });

        // Функция для загрузки продуктов по категории
        function loadProducts(categoryId) {
            var search = '<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>';
            var url = 'get_products.php?category=' + categoryId + '&search=' + search;

            $.ajax({
                type: 'GET',
                url: url,
                success: function(response) {
                    $('#products-container').html(response);
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
