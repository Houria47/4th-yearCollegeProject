<?php
SESSION_START();
$pageTitle = 'Moda | تسجيل الدخول لمتجر';
include 'init.php';
//check
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //get login information
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  //check if login information are valid
  $errors = []; //array to add error messages
  if (empty($email)) {
    $errors[] = 'حقل البريد الإلكتروني لا يجب أن يكون فارغ';
  } else {
    //check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'يرجى إدخال بريد إلكتروني صالح';
    }
  }
  if (empty($pass)) {
    $errors[] = ' حقل كلمة المرور لا يجب أن يكون فارغ';
  }

  if (empty($errors)) {
    $hashedPass = sha1($_POST['pass']);
    $stmt = $con->prepare(
      'SELECT * FROM store WHERE email=? AND password=? LIMIT 1'
    );
    $stmt->execute([$email, $hashedPass]);
    $count = $stmt->rowCount();
    if ($count > 0) {
        $store = $stmt->fetch();
        $_SESSION['storeID'] = $store['storeID'];
        $_SESSION['storeName'] = $store['storeName'];
        $_SESSION['email'] = $store['email'];
        $_SESSION['username'] = $store['username'];
        $_SESSION['location'] = $store['location'];
        $_SESSION['openTime'] = $store['openTime'];
        $_SESSION['closeTime'] = $store['closeTime'];
        $_SESSION['profileImg'] = $store['profileImg'];
        $_SESSION['createDate'] = $store['createDate'];
        header('Location: storeProfile.php');
        exit();
    } else {
      $errors[] = 'البريد الإلكتروني أو كلمة المرور غير صحيح';
    }
  }
}
?>
<!-- Login to store form -->
<div class="login-container">
    <div class="form-login">
    <h1>تسجيل الدخول للمتجر</h1>
    <?php if (isset($errors) && !empty($errors)) {
        echo "<div class='message-box error'><i class='fa fa-close'></i>";
        foreach ($errors as $error) {
        echo "<p>$error</p>";
        }
        echo '</div>';
    } ?>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
        <input class="input-form" name="email" type="text" placeholder="البريد الإلكتروني" value="<?=(isset($email))?$email:"";?>"/>
        <div class="pass-input">
            <i class="fa fa-eye eye" title="إظهار كلمة المرور"></i>
            <input class="input-form" name="pass" type="password" placeholder="كلمة المرور" value="<?=(isset($pass))?$pass:"";?>"/>
        </div>
        <input class="btn-submit" type="submit" value="تسجيل الدخول" />
    </form>
    <div class="redirect-link">
        <small>ليس لدي متجر,  <a href="storeRegister.php">إنشاء متجر</a></small>
    </div>
    </div>
</div>

<?php include $tpl . 'footer.php';
