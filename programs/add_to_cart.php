<?php
// Проверяем, получены ли данные из формы
if (isset($_POST['productId']) && isset($_POST['selectedSize'])) {
    // Получаем данные из формы
    $productId = $_POST['productId'];
    $selectedSize = $_POST['selectedSize'];

    // Подключение к базе данных
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

    // SQL запрос для выборки данных о продукте
    $sql = "SELECT * FROM products WHERE ID = $productId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Получение данных о продукте
        $row = $result->fetch_assoc();

        // Определение status_id
        $status_id = ($row['quantity_' . $selectedSize] > 0) ? 1 : 2;

        // SQL запрос для вставки данных в таблицу cart
        $insertSql = "INSERT INTO cart (image, name, description, price, quantity, size_id, custom_id, status_id)
                      VALUES ('{$row['image']}', '{$row['name']}', '{$row['description']}', '{$row['price']}', 1, $selectedSize, 1, $status_id)";

        if ($conn->query($insertSql) === TRUE) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "Продукт с ID $productId не найден";
    }

    // Закрытие соединения с базой данных
    $conn->close();
} else {
    // Если данные не получены, выводим сообщение об ошибке
    echo "Ошибка: Данные о товаре не получены.";
}
?>
