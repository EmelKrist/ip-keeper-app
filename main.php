<?php
session_start();
require_once 'config.php';

// Редирект на страницу авторизации, если нет сессии
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$reboot_error_message = "";
$with_error = "";

try {
    require_once 'db_connection.php';
    // Получение id пользователя из сессии и ip из БД
    $id = htmlspecialchars($_SESSION['id']);
    $query = $conn->prepare("SELECT ip, username FROM user WHERE id =?");
    $query->bind_param("s", $id);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        $query->bind_result($encrypted_ip, $username);
        $query->fetch();
        $query->close();
        // Отправка запроса к api для получения информации об ip 
        $ip = base64_decode($encrypted_ip);
        $url = IP_INFO_API . $ip . "/geo";
        $request = curl_init($url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        // Получение ответа в json формате 
        $response = curl_exec($request);
        if (curl_errno($request)) {
            throw new Exception("Сервис получения информации об IP адресе недоступен.");
        }
        curl_close($request);
        // Парсинг json'а
        $json_data = json_decode($response, true);
        if ($json_data &&  !isset($json_data['bogon'])) {
            $city = $json_data['city'];
            $region = $json_data['region'];
            $country = $json_data['country'];
            $loc = $json_data['loc'];
            $postal = $json_data['postal'];
            $timezone = $json_data['timezone'];
        } else {
            throw new Exception("Не удалось получить информацию об IP адресе.");
        }
    } else {
        throw new Exception("Данный аккаунт больше не существует, пройдите повторную регистрацию.");
    }
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $error_message = "Сервис хранения IP адресов временно недоступен, попробуйте позже.";
    $with_error = !empty($error_message);
} catch (Exception $e) {
    $error_message = $e->getMessage();
    $with_error = !empty($error_message);
}

// Обработка POST запроса на перегенрацию IP 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'reboot_ip.php';
    if (isset($_POST['reboot_ip'])) {
        try {
            reboot_ip($id, $encrypted_ip);
            header("Location: logout.php");
            exit();
        } catch (Exception $e) {
            $reboot_error_message = $e->getMessage();
        }
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
    <link rel="stylesheet" href="css/style.css">
    <style>
        .card {
            max-width: 800px;
        }

        .card-body ul.list-group {
            display: grid;
            gap: 15px;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .card-body .list-group-item {
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
        }

        @media (max-width: 576px) {
            .control-buttons {
                flex-direction: column;
                align-items: center;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Заголовок с кнопками выхода и перегенерации IP -->
    <header class="shadow-sm py-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto d-flex justify-content-start">
                    <form action="" method="post">
                        <button <?php if ($with_error): ?>disabled<?php endif; ?> type="submit" name="reboot_ip" class="btn btn-dark" style="min-width: 120px;">Новый IP</button>
                    </form>
                </div>
                <div class="col text-center">
                    <h1 class="mb-0 fw-bold">IP keeper</h1>
                </div>
                <div class="col-auto d-flex justify-content-end">
                    <a href="logout.php" class="btn btn-danger" style="min-width: 120px;">Выход</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Вывод ошибки перегенерации IP -->
        <?php if ($reboot_error_message): ?>
            <div class="toast fade show mb-3" role="alert" aria-live="polite" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000" style="background-color: #dc3545;">
                <div class="toast-header">
                    <strong class="me-auto">Ошибка</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
                </div>
                <div class="toast-body">
                    <?php echo $reboot_error_message; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card rounded">
            <div class="card-header text-center">
                <h2>О пользователе</h2>
            </div>

            <!-- Вывод ошибки или данных об IP пользователя -->
            <div class="card-body">
                <?php if ($error_message): ?>
                    <p style="color: #ffc600; text-align: justify;"><?php echo $error_message ?></p>
                <?php endif; ?>

                <?php if (!$error_message): ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Имя:</strong> <?php echo $username ?></li>
                        <li class="list-group-item"><strong>IP:</strong> <?php echo $ip ?></li>
                        <li class="list-group-item"><strong>Страна:</strong> <?php echo $country ?></li>
                        <li class="list-group-item"><strong>Регион:</strong> <?php echo $region ?></li>
                        <li class="list-group-item"><strong>Населенный пункт:</strong> <?php echo $city ?></li>
                        <li class="list-group-item"><strong>Почтовый индекс:</strong> <?php echo $postal ?></li>
                        <li class="list-group-item"><strong>Координаты:</strong> <?php echo $loc ?></li>
                        <li class="list-group-item"><strong>Временная зона:</strong> <?php echo $timezone ?></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>

</html>