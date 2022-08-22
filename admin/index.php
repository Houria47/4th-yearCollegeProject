<?php
SESSION_START();
$pageTitle = 'Moda | الصفحة الرئيسية';
include 'init.php';
// Get Newest Product from DB
$newproducts=getProductCards($con,'newProds',4);
$trendproducts=getProductCards($con,'trending',4);
$stmt=$con->prepare("SELECT * FROM store LIMIT 6");
$stmt->execute([]);
$stores=$stmt->fetchAll();
?>
<!-- <a href='products.php?do=newProds' class="btn">newProds</a>
<a href='products.php?do=trending' class="btn">trending</a> -->
<div class="home-page-container">
    <header>
        <div class="web-expline">
            <img src="layout/images/shop666.jpg" class="photo-expline1" alt="">
            <img src="layout/images/shop888.jpg" class="photo-expline2">
            <div class="sent-web-expline">
            </div>
        </div>
        <div class="slider">
            <button type="button" class="btn-carousel left"><i class="fa fa-chevron-left"></i></button>
            <div class="space-slider">
                <div class="space-carousel">
                    <img src="layout/images/slider4.jpg">
                    <img src="layout/images/6.png">
                    <img src="layout/images/12.jpg">
                    <img src="layout/images/slidershow.jpg">
                    <img src="layout/images/b795a7cf1a418e612c4ff5c316596e92.png">
                    <img src="layout/images/slider.jpg">
                </div>
            </div>
            <button type="button" class="btn-carousel right"><i class="fa fa-chevron-right"></i></button>
        </div>
    </header>
    <!-- newproducts Cards Section  -->
    <p class="trend">مضافة حديثاً</p>
    <div class="main-cards-container">
        <div class="prod-cards-container">
            <?php
                echo $newproducts;
            ?>
        </div>
    </div>
    <div  <?='onclick=\'location.href="products.php?do=newProds";\''?> class="seeMore-btn">عرض المزيد<i class="fa fa-angle-double-left"></i></div>
    <!-- newproducts Cards Section  -->
    <p class="trend">الأكثر رواجاً</p>
    <div class="main-cards-container">
        <div class="prod-cards-container">
            <?php
                echo $trendproducts;
            ?>
        </div>
    </div>
    <div <?='onclick=\'location.href="products.php?do=trending";\''?> class="seeMore-btn">عرض المزيد<i class="fa fa-angle-double-left"></i></div>
    <!-- stores section -->
    
    <p class="trend">المتاجر</p>
    <section class="store-home-cards">
        <?php
            foreach($stores as $store){
                $imgPath=imagePath($store['profileImg'],1);
                $link="onclick='location.href=\"oneStore.php?do=info&storeID={$store['storeID']}\";'";
                echo<<<"here"
                <div class="home-store-card" title="عرض المتجر" >
                    <div class="store-img-box" $link>
                        <img src="$imgPath">
                        <div class="overlay">
                            <span>{$store['storeName']}</span>
                        </div>
                    </div>
                </div>
                here;
            }
        ?>
    </section>
    <div <?='onclick=\'location.href="stores.php";\''?> class="seeMore-btn">عرض كل المتاجر<i class="fa fa-angle-double-left"></i></div>
</div>
<?php
include $tpl . 'footer.php';