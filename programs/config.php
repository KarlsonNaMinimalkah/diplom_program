<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 11;
}

// Проверка запроса на выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
