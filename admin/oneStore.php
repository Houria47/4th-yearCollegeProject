<?php
SESSION_START();
$pageTitle = 'Moda | المتاجر';
$showStoreOption = 'Show'; //dummy variable to show store option in nav bar
include 'init.php';

/*
** Show store details to user depending on $_GET['do'] value
** do = info -> show store information
** do = products -> show store products
*/
$do=($_GET['do']=='info')?'info':'products';
//
$storeID=(isset($_GET['storeID']) && is_numeric($_GET['storeID']))?intval($_GET['storeID']): 0 ;
$stmt=$con->prepare('SELECT * FROM store WHERE storeID=?');
$stmt->execute([$storeID]);
if($stmt->rowCount()>0){
    $store=$stmt->fetch();
}else{
    header('Location:index.php');
    exit();
}
//if there is no profile image Use default one
$imgPath=imagePath($store['profileImg'],1);
if( $do== 'info' ){
    // show store info

    // get # of added elements in this store &
    // get date of last added item in this store
    $stmt=$con->prepare("SELECT COUNT(itemID), max(addDate) FROM item WHERE storeID=?");
    $stmt->execute([$storeID]);
    $res=$stmt->fetch();
    $numOfProd=$res[0];
    // create Date Object to format the date with date_format() which accept only date Object created with date_create().
    $dateOfLastAdd = date_create($res[1]);
    ?>

    <!-- HTML code -->
    <div class="storeInfo-container">
        <!-- Show Store Name at the Top -->
        <div class="store-title">
            <h1>متجر <?=$store['storeName']?></h1>
        </div>
        <div class="store-head">
            <h2>معلومات المتجر</h2>
            <a href="?do=products&storeID=<?=$storeID?>">عرض منتجات المتجر</a>
        </div>
        <div class="store-info">
            <div class="store-info-column">
                <div class="store-info-row">
                    <h3>اسم المتجر</h3>
                    <span><?=$store['storeName']?></span>
                </div>
                <div class="store-info-row">
                    <h3>الموقع</h3>
                    <span><?=$store['location']?></span>
                </div>
                <div class="store-contact-info">
                    <div class="store-info-row">
                        <h3>البريد</h3>
                        <span><?=$store['email']?></span>
                    </div>
                    <div class="store-info-row">
                        <h3>أوقات العمل</h3>
                        <span>من الساعة <?=date_format(date_create($store['openTime']),"A g:i");?> إلى <?=date_format(date_create($store['closeTime']),"A g:i");?></span>
                    </div>
                </div>
            </div>
            <div class="vertical-separator"></div>
            <div class="store-info-column">
                <div class="store-img">
                    <img src="<?=$imgPath?>">
                </div>
                <div class="store-info-row">
                    <h3>عدد المنتجات المضافة</h3>
                    <span><?php echo $numOfProd; echo ($numOfProd<=10 && $numOfProd>=3)? ' منتجات':' منتج';?></span>
                </div>
                <div class="store-info-row">
                    <h3>تاريخ آخر إضافة</h3>
                    <span><?=date_format($dateOfLastAdd,'Y M j')?></span>
                </div>
            </div>
        </div>
    </div>

    <?php

}elseif($do=='products'){

    // Get Store Product From DB
    $stmt=$con->prepare("SELECT * FROM item WHERE storeID=? ORDER BY addDate");
    $stmt->execute([$storeID]);
    $products=$stmt->fetchAll();
    // variable to store likes and favs information for the user (with userID)
    $userLikesFavs=[];
    // check if userID is set in the session to Get products likes and favs information
    if(isset($_SESSION['userID'])){
        //userID is set in the session
        $userID=$_SESSION['userID'];
        //check if this userID exist in DB
        $stmt=$con->prepare("SELECT userID FROM user WHERE userID=?");
        $stmt->execute([$userID]);
        if($stmt->rowCount()>0){
            // Get products likes and favs information for this user
            $q="SELECT itemID , isLiked, isFavorit FROM favorite 
                WHERE itemID=?
                AND userID=?";
            foreach($products as $prod){
                $stmt=$con->prepare($q);
                $stmt->execute([$prod['itemID'],$userID]);
                if($stmt->rowCount()>0){
                    $res=$stmt->fetch();
                    $userLikesFavs[$prod['itemID']]=
                        [
                            "isLiked"  => $res['isLiked'],
                            "isFavorit"=> $res['isFavorit']
                        ];
                }else{
                    $userLikesFavs[$prod['itemID']]=
                        [
                            "isLiked"  => 0,
                            "isFavorit"=> 0
                        ];
                }
            }
        }// else there is no user with this ID, do nothing the $userLikesFavs will be empty
    }//userID is not set in the session, do nothing the $userLikesFavs will be empty
    ?>
    <div class="storeInfo-container">
        <!-- Show Store Name at the Top -->
        <div class="store-title">
            <h1>متجر <?=$store['storeName']?></h1>
        </div>
        <div class="store-head">
            <h2>منتجات المتجر</h2>
            <a href="?do=info&storeID=<?=$storeID?>">تفاصيل المتجر</a>
        </div>
        <!-- Start Product Cards -->
        <div class="main-cards-container">
            <div class="prod-cards-container">
                <?php
                    foreach($products as $product){
                        // check if the product liked or favored
                        // store the class name or nothing 
                        $liked=(!empty($userLikesFavs) && $userLikesFavs[$product['itemID']]['isLiked']==1)?"like-span-clicked":"";
                        if(!empty($userLikesFavs) && $userLikesFavs[$product['itemID']]['isFavorit']==1){
                            $favored="love-span-clicked";
                            $iconType="fa";
                        }else{
                            $favored="";
                            $iconType="far";
                        }
                        //if there is no product image Use default one
                        $imgPath=imagePath($product['image'],2);
                        echo<<<"part1"
                            <div class="prod-card" data-prodID='{$product['itemID']}'>
                                <span class="prod-card-ic love-span $favored"><i class="$iconType fa-heart"></i></span>
                                <div class="prod-img-box">
                                    <img src="$imgPath">
                                </div>
                                <div class="prod-info">
                                    <h4>{$product['prodName']}</h4>
                                    <p>{$product['description']}</p>
                                    <div class="prod-price">
                        part1;
                        if((!$product['priceOnSale']==null) && (!$product['priceOnSale']==0)){
                            
                            echo        "<span>" . number_format($product['priceOnSale'],0,'.',',') . " ل.س" . "</span>";
                            echo        "<span>" . number_format($product['price'],0,'.',',') . " ل.س" . "</span>";
                        }
                        else{
                            echo        "<span>" . number_format($product['price'],0,'.',',') . " ل.س" . "</span>";
                        }
                        echo<<<"part2"
                                    </div>
                                </div>
                                <span class="prod-card-ic like-span $liked"><i class="fa fa-thumbs-up"></i></span>
                                <a href="prodDetails.php?prodID={$product['itemID']}" class="prod-card-details-btn">تفاصيل</a>
                            </div>
                        part2;
                    }
                ?>
            </div> 
        </div>
    </div>
    <?php
}
include $tpl . 'footer.php';
