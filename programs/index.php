"<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
header("Location: ../login.php");
exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}

// Fetch user information based on session user_id
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Handle logout directly in this script
if (isset($_POST['logout'])) {
session_unset();
session_destroy();
header("Location:../login.php");
exit();
}
?>

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
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <span>Привет, <?php echo $user['name']; ?></span>
                    <div class="dropdown-content">
                        <form method="post">
                            <button type="submit" name="logout">Выход</button>
                        </form>
                        <a href="delete_account.php">Удалить аккаунт</a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="cart">
                <a href="cart.php"><img src="cart-icon.png" alt="Корзина"></a>
                <span>0</span>
            </div>
        </div>
    </div>
</header>
<div id="notification"></div>
<main>
    <?php
    // SQL query to fetch products
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    // Check if there are results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch status from status_quantity table
        $status_sql = "SELECT status FROM status_quantity WHERE ID = " . $row['status_id'];
        $status_result = $conn->query($status_sql);
        if ($status_result->num_rows > 0) {
            $status_row = $status_result->fetch_assoc();
            $status = $status_row['status'];
        } else {
            $status = "Статус не найден";
        }
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
            <input type="hidden" class="selected-size" name="selected-size" value="">
            <p>Status: <?php echo $status; ?></p>
            <form action="index.php" method="post">
                <input type="hidden" name="productId" value="<?php echo $row['id']; ?>">
                <input type="hidden" class="selected-size" name="selectedSize" value="">
                <input type="hidden" name="quantity" id="quantity" value="1">
                <button type="submit" name="addToCart">Добавить в корзину</button>
            </form>
        </div>
        <?php
    }
} else {
    echo "0 результатов";
}

// Handle add to cart functionality
if (isset($_POST['addToCart'])) {
    $productId = $_POST['productId'];
    $selectedSize = $_POST['selectedSize'];
    $quantity = $_POST['quantity'];
    $status_id = 1;

    $productSql = "SELECT * FROM products WHERE id = $productId";
    $productResult = $conn->query($productSql);
    if ($productResult->num_rows > 0) {
        $productRow = $productResult->fetch_assoc();

        $stmt = $conn->prepare("INSERT INTO cart (user_id, image, name, description, price, quantity, size_id, custom_id, status_id)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdiiii", $user_id, $productRow['image'], $productRow['name'], $productRow['description'], $productRow['price'], $quantity, $selectedSize, $custom_id, $status_id);

        $custom_id = 1;

        if ($stmt->execute()) {
            echo "<script>document.getElementById('notification').innerText = 'Товар успешно добавлен в корзину';</script>";
        } else {
            echo "<script>document.getElementById('notification').innerText = 'Ошибка: " . $stmt->error . "';</script>";
        }

        $stmt->close();
    }
}

// Close connection
$conn->close();
?>
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
<script>
    var cartButton = document.querySelector('.cart img');
    cartButton.addEventListener('click', function() {
        window.location.href = "cart.php";
    });
    <?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

// Создаем соединение с базой данных
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получаем информацию о пользователе на основе user_id из сессии
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Обработка выхода пользователя
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Закрываем соединение с базой данных
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            padding: 10px;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            color: black;
            text-decoration: none;
            display: block;
            padding: 8px 16px;
            transition: background-color 0.3s;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
    </style>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <span class="dropbtn">Привет, <?php echo $user['name']; ?> &#9660;</span>
                    <div class="dropdown-content">
                        <form method="post">
                            <button type="submit" name="logout">Выход</button>
                        </form>
                        <a href="delete_account.php">Удалить аккаунт</a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="cart">
                <a href="cart.php"><img src="cart-icon.png" alt="Корзина"></a>
                <span>0</span>
            </div>
        </div>
    </div>
</header>
<div id="notification"></div>
<main>
    <!-- Остальной HTML контент страницы -->
</main>
<footer>
    <p>Контакты: email@example.com | Телефон: +1234567890</p>
</footer>
<script>
    var cartButton = document.querySelector('.cart img');
    cartButton.addEventListener('click', function() {
        window.location.href = "cart.php";
    });

    var sizeButtons = document.querySelectorAll('.size-btn');
    sizeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var selectedSize = this.getAttribute('data-size');
            document.querySelector('.selected-size').value = selectedSize;
            var quantityInput = document.getElementById('quantity');
            var quantitySize = 'quantity_' + selectedSize;
            quantityInput.value = "<?php echo $row[$quantitySize]; ?>";
            sizeButtons.forEach(function(btn) {
                btn.style.backgroundColor = "";
            });
            this.style.backgroundColor = "black";
        });
    });
</script>
</body>
</html>
