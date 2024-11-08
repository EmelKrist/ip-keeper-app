<?php
require_once 'config.php';

/**
 * Функция для получения IP адреса пользователя (клиента). 
 * 
 * Если запрос приходит с другого хоста, то IP получается с помощью инструментов языка, 
 * причем с учетом различных обстоятельств (если запрос напрямую от браузера, либо проходит через прокси).
 * Если же запрос приходит с локального хоста, то в таком случае используется API для получения IP, 
 * чтобы избежать появления локального IP 127.0.0.1.
 * 
 * Функция возвращает IP пользователя.
 */
function get_user_ip()
{
   // Получение ip клиента, если запрос с другого хоста (несколько проверок)
   $user_ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
      $_SERVER['REMOTE_ADDR']);
   // Получение ip клиента через API, если запрос локальный
   if ($user_ip === '127.0.0.1' || empty($user_ip)) {
      $request = curl_init(IP_FY_API);
      curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($request);
      if (curl_errno($request)) {
         throw new Exception("Сервис получения IP адреса недоступен.");
      }
      curl_close($request);
      // Парсинг json'а
      $json_data = json_decode($response, true);
      if ($json_data && isset($json_data['ip'])) {
         return $json_data['ip'];
      } else {
         throw new Exception("Произошла ошибка получения IP адреса.");
      }
   } else {
      return $user_ip;
   }
}
