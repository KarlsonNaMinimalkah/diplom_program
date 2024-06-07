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