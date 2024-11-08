<?php
require_once 'get_user_ip.php';

$error_message = "";
$toast_color = "";
$username = "";
$email = "";
$password = "";

// Обработка POST запроса на регистрацию
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        require_once 'db_connection.php';
        // Валиадция полей
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Электронная почта введена некорректно.");
        } else if (strlen($password) < 8) {
            throw new InvalidArgumentException("Пароль должен быть не менее 8 символов длиной.");
        } else if (!preg_match("/^[a-zA-Z0-9]+$/", $password)) {
            throw new InvalidArgumentException("Пароль должен содержать только латинские символы и цифры.");
        } else {
            // Проверка существования email в БД
            $id_by_email_query = $conn->prepare("SELECT id FROM user WHERE email = ?");
            $id_by_email_query->bind_param("s", $email);
            $id_by_email_query->execute();
            $id_by_email_query->store_result();

            if ($id_by_email_query->num_rows > 0) {
                throw new InvalidArgumentException("Указанная электронная почта уже используется.");
            } else {
                // Обеспечение безопасности ip пользователя (шифрование)
                $encrypted_ip = base64_encode(get_user_ip());
                // Обеспчеение безопасности пароля пользователя (хэширование)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Проверка наличия никнейма
                if (empty(trim($username))) {
                    $username = "Anonymous";
                }
                // Регистрация нового пользователя
                $query = $conn->prepare("INSERT INTO user (username, ip, email, password) VALUES (?, ?, ?, ?)");
                $query->bind_param("ssss", $username, $encrypted_ip, $email, $hashed_password);

                if ($query->execute()) {
                    $conn->close();
                    header("Location: login.php");
                    exit();
                } else {
                    throw new Exception("Ошибка сервиса при регистрации, попробуйте позже.");
                }
            }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <h2>Регистрация</h2>
            </div>
            <div class="card-body">
                <!-- Форма регистрации -->
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Эл. почта*</label>
                        <input type="text" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label">Пароль*</label>
                        <div class="d-flex align-items-center">
                            <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="toggle_password">
                                <i class="bi bi-eye-slash" id="toggle_icon"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 mb-3">Зарегистрироваться</button>
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

                <div class="text-center mb-3">
                    <p>Уже зарегистрированы? <a href="login.php">Войдите</a></p>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="js/toggle_password_visibility.js"></script>
</body>

</html>