<?php
session_start();

$error_message = "";
$toast_color = "";
$email = "";
$password = "";

// Обработка POST запроса на вход
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        require_once 'db_connection.php';
        // Валидация входных данных
        $query = $conn->prepare("SELECT id, ip, password FROM user WHERE email =?");
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();
        // Проверка пользователя на существование 
        if ($query->num_rows > 0) {
            $query->bind_result($id, $ip, $db_password);
            $query->fetch();
            $query->close();
            // Проверка пароля на корректность
            if (password_verify($password, $db_password)) {
                $_SESSION['id'] = $id;
                $conn->close();
                header("Location: main.php");
                exit();
            } else {
                throw new InvalidArgumentException("Неверный пароль.");
            }
        } else {
            throw new InvalidArgumentException("Пользователя не существует.");
        }
    } catch (InvalidArgumentException $e) {
        $error_message = $e->getMessage();
        $toast_color = "#dc3545";
    } catch (Exception  $e) {
        error_log($e->getMessage());
        $error_message = "Сервис хранения IP адресов временно недоступен, попробуйте позже.";
        $toast_color = "#dc3545";
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP keeper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <header class="text-center py-3">
        <h1 class="fw-bold">IP keeper</h1>
    </header>

    <div class="container">
        <div class="card rounded">
            <div class="card-header text-center">
                <h2>Авторизация</h2>
            </div>
            <div class="card-body">
                <!-- Форма авторизации -->
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Эл. почта</label>
                        <input type="text" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label">Пароль</label>
                        <div class="d-flex align-items-center">
                            <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="toggle_password">
                                <i class="bi bi-eye-slash" id="toggle_icon"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 mb-3">Войти</button>
                </form>

                <!-- Вывод сообщения об ошибке -->
                <?php if ($error_message): ?>
                    <div class="toast fade show mt-3 mb-3" role="alert" aria-live="polite" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000" style="background-color: <?php echo $toast_color; ?>;">
                        <div class="toast-header">
                            <strong class="me-auto">Ошибка</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
                        </div>
                        <div class="toast-body">
                            <?php echo $error_message; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="text-center">
                    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                </div>
            </div>
        </div>
        <script type="module" src="js/toggle_password_visibility.js"></script>
</body>

</html>