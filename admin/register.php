<?php
SESSION_START();
$pageTitle = 'Moda | تسجيل حساب';
include 'init.php';

//check
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //get login information
  $fullname = $_POST['fullname'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $repass = $_POST['repass'];

  //check if Register Information are valid
  $errors = []; //array to add error messages

  //check the Password Srength
  if(empty($pass)){
        $errors[] = ' حقل كلمة المرور لا يجب أن يكون فارغ';
  }elseif(!is_strong($pass)) {
        $errors['strongPass'] =
          '.كلمة المرور يجب أن تحوي حرف كبير, حرف صغير و رقم على الأقل وطولها أكبر من 8 محارف';
      }elseif ($pass != $repass) {
          $errors['pass'] = 'كلمات المرور غير متطابقة';
        }

  //check if fullname, username, are empty and if email is valid
  if (empty($fullname)) {
    $errors['fullname'] = 'حقل الاسم لا يجب أن يكون فارغ';
  }
  if (empty($username)) {
    $errors['username'] = 'حقل اسم المستخدم لا يجب أن يكون فارغ';
  }
  //check if email is valid
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'يرجى ادخال بريد إالكتروني صالح';
  }
  // if th Information valid, insert new user to Database
  if (empty($errors)) {
    $stmt = $con->prepare('SELECT userID FROM user WHERE username=?');
    $stmt->execute([$username]);
    $count = $stmt->rowCount();
    if ($count > 0) {
      $errors[] = 'اسم المستخدم موجود مسبقا, يرجى إدخال اسم مختلف';
    }
    $stmt = $con->prepare('SELECT userID FROM user WHERE email=?');
    $stmt->execute([$email]);
    $count = $stmt->rowCount();
    if ($count > 0) {
      $errors[] = 'البريد الإلكتروني مستخدم من قبل حساب آخر, يرجى إدخال بريد مختلف';
    }
    if (empty($errors)) {
      $stmt = $con->prepare(
        'INSERT INTO user(username,email,fullName,password) VALUES(:username, :email, :fullname, :pass) '
      );
      $stmt->execute([
        'username' => $username,
        'email' => $email,
        'fullname' => $fullname,
        'pass' => sha1($pass),
      ]);
      $count = $stmt->rowCount();
      if ($count > 0) {
        $stmt = $con->prepare('SELECT * FROM user WHERE username=?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header('Location: index.php');
        exit();
      } else {
        $errors[] = 'Something Wrong Happend!'; // in case something wrong happend (idk what could be)
      }
    }
  }
}
?>
<!-- Register  -->

<div class="login-container">
    <div class="form-login">
    <h1>تسجيل حساب</h1>
    <?php if (isset($errors) && !empty($errors)) {
        echo "<div class='message-box error'><i class='fa fa-close'></i>";
        foreach ($errors as $error) {
        echo "<p>$error</p>";
        }
        echo '</div>';
    } ?>
    <form action="?do=register" method="POST">
        <input class="input-form" name="fullname" type="text" placeholder="الاسم كامل" value="<?=(isset($fullname))?$fullname:"";?>"/>
        <input class="input-form" name="username" type="text" placeholder=" اسم المستخدم" value="<?=(isset($username))?$username:"";?>"/>
        <input class="input-form" name="email" type="text" placeholder="البريد الإلكتروني" value="<?=(isset($email))?$email:"";?>"/>
        <input class="input-form" name="pass" type="password" placeholder="كلمة المرور" value="<?=(isset($pass))?$pass:"";?>"/>
        <input class="input-form" name="repass" type="password" placeholder="تأكيد الكلمة" value="<?=(isset($repass))?$repass:"";?>"/>
        <input class="btn-submit" type="submit" value="تسجيل الحساب" />
    </form>
    <div class="redirect-link">
        <small>لدي حساب مسبقاً, <a href="login.php">تسجيل الدخول لحسابي </a></small>
    </div>
    </div>
</div>
<?php include $tpl . 'footer.php';
