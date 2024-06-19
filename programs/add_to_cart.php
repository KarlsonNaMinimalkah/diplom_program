<?php
session_start();

// Проверка, залогинен ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo '<div class="error-message">Пользователь не залогинен</div>';
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
    echo '<div class="error-message">Ошибка соединения: ' . $conn->connect_error . '</div>';
    exit();
}

// Получение информации о пользователе по user_id из сессии
$user_id = $_SESSION['user_id'];

// Обработка добавления в корзину
if (isset($_GET['productId']) && isset($_GET['selectedSize'])) {
    $productId = $_GET['productId'];
    $selectedSize = $_GET['selectedSize'];
    $quantity = 1;  // Или вы можете использовать $_GET['quantity'] если есть это поле
    $status_id = 1;
    $custom_id = 1;
    
    $productSql = "SELECT * FROM products WHERE ID = $productId";
    $productResult = $conn->query($productSql);
    if ($productResult->num_rows > 0) {
        $productRow = $productResult->fetch_assoc();

        // Добавляем товар в корзину в сессии
        $_SESSION['cart'][] = array(
            'id' => $productId,
            'image' => $productRow['image'],
            'name' => $productRow['name'],
            'description' => $productRow['description'],
            'price' => $productRow['price'],
            'quantity' => $quantity
        );

        // Вставка данных в таблицу cart
        $cartSql = "INSERT INTO cart (image, name, description, price, quantity, size_id, custom_id, status_id, user_id)
                    VALUES ('{$productRow['image']}', '{$productRow['name']}', '{$productRow['description']}', {$productRow['price']}, $quantity, $selectedSize, $custom_id, $status_id, $user_id)";

        if ($conn->query($cartSql) === TRUE) {
            // Получение количества товаров в корзине
            $cartCountSql = "SELECT COUNT(*) AS cartCount FROM cart WHERE user_id = $user_id";
            $cartCountResult = $conn->query($cartCountSql);
            $cartCount = $cartCountResult->fetch_assoc()['cartCount'];

            // Формирование HTML-ответа
            $response = '<div class="success-message">Товар успешно добавлен в корзину</div>';
            $response .= '<div>Количество товаров в корзине: ' . $cartCount . '</div>';
            echo $response;
        } else {
            echo '<div class="error-message">Ошибка: ' . $conn->error . '</div>';
        }
    } else {
        echo '<div class="error-message">Товар не найден</div>';
    }
} else {
    echo '<div class="error-message">Неправильные параметры запроса</div>';
}