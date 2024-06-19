<?php
session_start();

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Запрос к таблице с кастомами
$sql = "SELECT * FROM custom";
$result = $conn->query($sql);

// Закрытие соединения с базой данных
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
</head>
<body>
    <h1>Выбор кастома</h1>
    <form action="cart.php" method="post">
        <div class="custom-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="custom-card">
                        <div class="card-content">
                            <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            <h2><?php echo $row['name']; ?></h2>
                            <p>Цена: <?php echo $row['price']; ?> руб.</p>
                            <p>Время работы: <?php echo $row['work_time']; ?> ч.</p>
                        </div>
                        <div class="card-radio">
                            <label>
                                <input type="radio" name="custom_id" value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == 1) ? 'checked' : ''; ?>>
                                Выбрать
                            </label>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "Нет записей о кастомах";
            }
            ?>
        </div>
        <button type="submit">Выбрать</button>
    </form>
</body>
</html>
