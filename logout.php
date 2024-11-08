<?php
// Закрытие сессии пользователя и редирект на страницу авторизации
session_start();
unset($_SESSION['id']);
session_destroy();
header('Location: login.php');
exit();
