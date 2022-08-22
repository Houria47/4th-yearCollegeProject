<?php
$dsn = 'mysql:host=localhost;dbname=ecommerce';
$user = 'root';
$pass = '';
$option = [
  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
];

try {
  $con = new PDO($dsn, $user, $pass, $option);
  $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo<<<"message"
    <div class="after-nav"></div>
    <div class="after-nav"></div>
    <div class="message-container message-box error">
        <p>فشل الاتصال بقاعدة البيانات, يجب تشغيل السيرفر المحلي XAMPP: MySql -> start</p>
    </div>
    <div class="message-container message-box info">
        <h3>رسالة الخطأ</h3>
        <p>{$e->getMessage()}</p>  
    </div>
    <div class="after-nav"></div>
    <div class="after-nav"></div>
    <div class="after-nav"></div>
  message;
}
