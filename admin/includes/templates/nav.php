<?php
    $stmt=$con->prepare('SELECT * FROM class');
    $stmt->execute([]);
    $classes=$stmt->fetchAll();
?>

<div class="fixed">
  <!-- Top bar Start -->
  <div class="top-bar">
    <a href="index.php" class="head-title">mira</a>
    <div class="search-input">
      <input type="text" placeholder="بحث.." />
      <i class="fa fa-search"></i>
    </div>
    <a class="stores-button" href="stores.php"
      ><i class="fa fa-store"></i>المتاجر</a
    >
    <?php if (isset($showStoreOption)) { ?>
    <div class="store-dropdown">
      <span>حساب المتجر</span>
      <div class="dropdown-content">
        <?php if (isset($_SESSION['storeID'])) { ?>
        <a href="StoreProfile.php?do=profileInfo" class="dropdown-item">متجري</a>
        <a href="logout.php?do=fromStore" class="dropdown-item"
          >خروج من المتجر</a
        >
        <?php } else { ?>
        <a href="storeRegister.php" class="dropdown-item">إنشاء متجر</a>
        <a href="storeLogin.php" class="dropdown-item">دخول لمتجري</a>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  </div>
  <!-- Top bar End -->

  <!-- Nav bar Start -->
  <nav>
    <div class="nav-list">
      <a class="home-link" href="index.php">الصفحة الرئيسية</a>
      <ul>
        <?php
        if(isset($isProdsPage))
            foreach($classes as $class)
                echo "<li><a class='prodFilter' data-kind='{$class['classID']}'>{$class['className']}</a></li>"; 
        else
            foreach($classes as $class)
                echo "<li><a href='Products.php?do={$class['classID']}' class='prodFilter' data-kind='{$class['classID']}'>{$class['className']}</a></li>"; 
        
        ?>
      </ul>
    </div>
    <div class="dropdown">
      <span>حساب المستخدم</span>
      <div class="dropdown-content">
        <?php if (isset($_SESSION['userID'])) { ?>
        <a href="favorite.php" class="dropdown-item">مفضلتي</a>
        <a href="logout.php?do=fromUser" class="dropdown-item">تسجيل الخروج</a>
        <?php } else { ?>
        <a href="login.php" class="dropdown-item">تسجيل دخول</a>
        <a href="register.php" class="dropdown-item">تسجيل حساب</a>
        <?php } ?>
      </div>
    </div>
  </nav>
  <!-- Nav bar End -->

</div>
<div class="after-nav"></div>
