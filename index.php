<?php
session_start();
// Если нет сесси, редирект на страницу авторизации, иначе на основную страницу
if (!isset($_SESSION['id'])) {
   header("Location: login.php");
} else {
   header("Location: main.php");
}
exit();
