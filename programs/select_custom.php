<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$cartId = isset($_GET['cartId']) ? $_GET['cartId'] : 0;
$oldCustomId = 0;

// Получаем текущий кастом товара, если есть
$cartSql = "SELECT custom_id, price FROM cart WHERE id = ?";
$stmt = $conn->prepare($cartSql);
$stmt->bind_param("i", $cartId);
$stmt->execute();
$cartResult = $stmt->get_result();
if ($cartResult->num_rows > 0) {
    $cartRow = $cartResult->fetch_assoc();
    $oldCustomId = $cartRow['custom_id'];
    $cartPrice = $cartRow['price'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_id']) && isset($_POST['cartId'])) {
    $customId = $_POST['custom_id'];
    $cartId = $_POST['cartId'];

    // Получаем цену нового кастома
    $customSql = "SELECT price FROM custom WHERE id = ?";
    $stmt = $conn->prepare($customSql);
    $stmt->bind_param("i", $customId);
    $stmt->execute();
    $customResult = $stmt->get_result();
    $customPrice = $customResult->fetch_assoc()['price'];
    $stmt->close();

    // Получаем цену старого кастома
    if ($oldCustomId != 0) {
        $oldCustomSql = "SELECT price FROM custom WHERE id = ?";
        $stmt = $conn->prepare($oldCustomSql);
        $stmt->bind_param("i", $oldCustomId);
        $stmt->execute();
        $oldCustomResult = $stmt->get_result();
        $oldCustomPrice = $oldCustomResult->fetch_assoc()['price'];
        $stmt->close();

        // Вычитаем цену старого кастома
        $cartPrice -= $oldCustomPrice;
    }

    // Добавляем цену нового кастома
    $newPrice = $cartPrice + $customPrice;

    // Обновляем запись в корзине
    $updateSql = "UPDATE cart SET custom_id = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("idi", $customId, $newPrice, $cartId);
    $stmt->execute();
    $stmt->close();

    header("Location: cart.php");
    exit();
}

$customsSql = "SELECT * FROM custom";
$customsResult = $conn->query($customsSql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выбор кастома</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .custom-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin: 20px auto;
            max-width: 800px;
        }

        .custom-card {
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            width: 300px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .custom-card:hover {
            transform: translateY(-5px);
        }

        .card-content {
            text-align: center;
        }

        .card-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .card-content h2 {
            margin-top: 10px;
            font-size: 1.5rem;
        }

        .card-content p {
            font-size: 1rem;
            color: #666666;
        }

        .card-radio {
            margin-top: 10px;
            text-align: center;
        }

        .card-radio input[type="radio"] {
            display: none; /* Скрываем сам радиокнопку */
        }

        .card-radio label {
            cursor: pointer;
            padding: 8px 16px;
            background-color: #007bff;
            color: #ffffff;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .card-radio label:hover {
            background-color: #0056b3;
        }

        .card-radio input[type="radio"]:checked + label {
            background-color: #0056b3;
        }
    </style>
    <script>
        function selectCustom(element, customId) {
            document.querySelectorAll('.custom-item').forEach(item => {
                item.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('custom_id').value = customId;
        }
    </script>
</head>
<body>
<header>
    <h1>Выбор кастома</h1>
</header>
<main>
    <form method="post">
        <input type="hidden" name="cartId" value="<?php echo $cartId; ?>">
        <input type="hidden" id="custom_id" name="custom_id" value="">
        <div class="custom-container">
            <?php while ($row = $customsResult->fetch_assoc()): ?>
                <div class="custom-card custom-item" onclick="selectCustom(this, <?php echo $row['id']; ?>)">
                    <div class="card-content">
                        <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                        <h2><?php echo $row['name']; ?></h2>
                        <p>Цена: <?php echo $row['price']; ?> руб.</p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button type="submit">Выбрать кастом</button>
        </div>
    </form>
</main>
</body>
</html>
