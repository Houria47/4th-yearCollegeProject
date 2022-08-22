<?php
SESSION_START();
$pageTitle = 'Moda | تسجيل الدخول';
include 'init.php';

//check
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //get login information
  $username = $_POST['username'];
  $pass = $_POST['pass'];
  //check if login information are valid
  $errors = []; //array to add error messages
  if (empty($username)) {
    $errors[] = 'حقل اسم المستخدم لا يجب أن يكون فارغ';
  }
  if (empty($pass)) {
    $errors[] = ' حقل كلمة المرور لا يجب أن يكون فارغ';
  }

  if (empty($errors)) {
    $hashedPass = sha1($_POST['pass']);
    $stmt = $con->prepare(
      'SELECT * FROM user WHERE username=? AND password=? LIMIT 1'
    );
    $stmt->execute([$username, $hashedPass]);
    $count = $stmt->rowCount();
    if ($count > 0) {
      $user = $stmt->fetch();
      $_SESSION['userID'] = $user['userID'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['email'] = $user['email'];
      header('Location: index.php');
      exit();
    } else {
      $errors[] = 'كلمة المرور أو اسم المستخدم غير صحيح';
    }
  }
}
?>
<!-- Login  -->
<div class="login-container">
    <div class="form-login">
    <h1>تسجيل الدخول</h1>
    <?php if (isset($errors) && !empty($errors)) {
        echo "<div class='message-box error'><i class='fa fa-close'></i>";
        foreach ($errors as $error) {
        echo "<p>$error</p>";
        }
        echo '</div>';
    } ?>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
        <input class="input-form" name="username" type="text" placeholder=" اسم المستخدم" value="<?=(isset($username))?$username:"";?>"/>
        <div class="pass-input">
            <i class="fa fa-eye eye" title="إظهار كلمة المرور"></i>
            <input class="input-form" name="pass" type="password" placeholder="كلمة المرور" value="<?=(isset($pass))?$pass:"";?>"/>
        </div>
        <input class="btn-submit" type="submit" value="تسجيل الدخول" />
    </form>
    <div class="redirect-link">
        <small>ليس لدي حساب,  <a href="register.php">تسجيل حساب</a></small>
    </div>
    </div>
</div>
<?php include $tpl . 'footer.php';
