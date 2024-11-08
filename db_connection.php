<?php
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
   throw new Exception("Ошибка подклчения к БД: " . $conn->connect_error);
}
