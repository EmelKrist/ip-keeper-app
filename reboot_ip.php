<?php
require_once 'get_user_ip.php';

/**
 * Функция для перезагрузки IP пользователя (клиента).
 * 
 * Получает нынешний IP пользователя и, если он идентичен старому, 
 * прекращает работу, иначе сохраняет в БД новый IP 
 * в зашифрованном виде.
 */
function reboot_ip($id, $encrypted_ip)
{
   try {
      require_once 'db_connection.php';
   } catch (Exception $e) {
      error_log($e->getMessage());
      throw new Exception("Сервис хранения IP адресов временно недоступен, попробуйте позже.");
   }

   $new_ip = get_user_ip();
   $old_ip = base64_decode($encrypted_ip);

   if ($new_ip === $old_ip) {
      throw new Exception("Ваш IP адрес тот же, что и раньше.");
   }
   // Сохранение нового IP в БД
   $query = $conn->prepare("UPDATE user SET ip =? WHERE id =?");
   $query->bind_param("si", base64_encode($new_ip), $id);
   if ($query->execute()) {
      $query->close();
      return true;
   } else {
      throw new Exception("Не удалось перегенереровать ваш IP адрес.");
   }
}
