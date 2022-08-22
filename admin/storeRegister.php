<?php
SESSION_START();
$pageTitle = 'Moda | تسجيل حساب لمتجر';
include 'init.php';

//check
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //get login information
  $storeName = $_POST['storeName'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $repass = $_POST['repass'];

  //check if Register Information are valid
  $errors = []; //array to add error messages
 
  //check if name, email, are empty and if email is valid
  if (empty($storeName)) {
    $errors['name'] = 'حقل الاسم لا يجب أن يكون فارغ';
  }
  if (empty($email)) {
    $errors[] = 'حقل البريد الإكتروني لا يجب أن يكون فارغ';
  } else {
    //check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'يرجى ادخال بريد إلكتروني صالح';
    }
  } 
  //check the Password Srength
  if(empty($pass)){
        $errors[] = ' حقل كلمة المرور لا يجب أن يكون فارغ';
        if(empty($repass))
            $errors[] = ' حقل تأكيد كلمة المرور لا يجب أن يكون فارغ';
    }elseif(!is_strong($pass)) {
        $errors['strongPass'] =
            '.كلمة المرور يجب أن تحوي حرف كبير, حرف صغير و رقم على الأقل وطولها أكبر من 8 محارف';
    }elseif ($pass != $repass) {
        $errors['pass'] = 'كلمات المرور غير متطابقة';
    }

  // if the Information valid, insert new store to Database
  $stmt = $con->prepare('SELECT storeID FROM store WHERE email=?');
  $stmt->execute([$email]);
  $count = $stmt->rowCount();
  if ($count > 0) {
    $errors[] = 'البريد الإلكتروني مستخدم من قبل حساب آخر, يرجى إدخال بريد مختلف';
  }
  if (empty($errors)) {
    $stmt = $con->prepare(
      'INSERT INTO store(email,storeName,password,createDate) VALUES(:email, :storeName, :pass ,now())'
    );
    $stmt->execute([
      'email' => $email,
      'storeName' => $storeName,
      'pass' => sha1($pass),
    ]);
    $count = $stmt->rowCount();
    if ($count > 0) {
      $stmt = $con->prepare('SELECT * FROM store WHERE email=?');
      $stmt->execute([$email]);
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
    }
     else {
      $errors[] = 'Something Wrong Happend!'; // in case something wrong happend (idk what could be)
    }
  }
}
?>
  <!-- Register  -->
    <div class="login-container">
        <div class="form-login">
            <h1>تسجيل حساب متجر</h1>
            <?php if (isset($errors) && !empty($errors)) {
            echo "<div class='message-box error'><i class='fa fa-close'></i>";
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo '</div>';
            } ?>
            <form action="?do=register" method="POST">
                <input class="input-form" name="storeName" type="text" placeholder="اسم المتجر" value="<?=(isset($storeName))?$storeName:"";?>" />
                <input class="input-form" name="email" type="text" placeholder="البريد الإلكتروني" value="<?=(isset($email))?$email:"";?>"/>
                <input class="input-form" name="pass" type="password" placeholder="كلمة المرور" value="<?=(isset($pass))?$pass:"";?>"/>
                <input class="input-form" name="repass" type="password" placeholder="تأكيد كلمة المرور" value="<?=(isset($repass))?$repass:"";?>"/>
                <input class="btn-submit" type="submit" value="تسجيل الحساب" />
            </form>
            <div class="redirect-link">
                <small>لدي متجر مسبقاً, <a href="storeLogin.php">تسجيل الدخول لمتجري </a></small>
            </div>
        </div>
    </div>
  <?php include $tpl . 'footer.php';
