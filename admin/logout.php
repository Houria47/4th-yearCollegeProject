<?php
SESSION_START();
$do = $_GET['do'] == 'fromUser' ? 'fromUser' : 'fromStore';
if ($do == 'fromUser') {
  unset($_SESSION['userID']);
  unset($_SESSION['username']);
  unset($_SESSION['email']);
} else {
  unset($_SESSION['storeID']);
  unset($_SESSION['storeName']);
  unset($_SESSION['email']);
  unset($_SESSION['username']);
  unset($_SESSION['openTime']);
  unset($_SESSION['closeTime']);
  unset($_SESSION['profileImg']);
  unset($_SESSION['createDate']);
}
header('Location: index.php');
exit();
