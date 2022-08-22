<?php
SESSION_START();
$pageTitle = 'Moda | الصفحة الرئيسية';
include 'init.php';
// Get product ID from get request 
$prodID=$_GET['prodID'];
//check if the ID for an existed product in DB
$stmt=$con->prepare("SELECT * FROM item WHERE itemID=?");
$stmt->execute([$prodID]);
if($stmt->rowCount()>0){
    // The product existed, fetch the data
    $product=$stmt->fetch();
    // get path of product image
    $productImg=imagePath($product['image'],2);
    //get it's details from DB
    $prodSizes=[];
    $prodColors=[];
    $allColors=[];
    $allColorSizes=[];
    getProductDetails($con,$prodID,$prodSizes,$prodColors,$allColors,$allColorSizes);
    //get product store info
    $stmt=$con->prepare("SELECT s.storeID,s.storeName,s.profileImg,COUNT(i.itemID) as prodsNum
                         FROM store s , item i
                         WHERE s.storeID=i.storeID 
                         AND s.storeID=?
                         GROUP BY s.storeID,s.storeName,s.profileImg
                         ");
    $stmt->execute([$product['storeID']]);
    $store=$stmt->fetch();
    // get path of store profile image
    $storeImg=imagePath($store['profileImg'],1);
    //link to the store information page
    $storeLink="onclick='location.href=\"oneStore.php?do=info&storeID={$store['storeID']}\";'";
    //show the product details
    ?>
    <!-- HTML Code Start -->
    <div class="storeInfo-container">
            <div class="details-container">
                <div class="det-col img-store">
                    <div class="details-imgBox">
                        <img src="<?=$productImg?>"/>
                    </div>
                    <div class="store-ad">
                        <div class="store-ad-info">
                            <span>متجر:</span>
                            <a href="oneStore.php?do=info&storeID=<?=$store['storeID']?>" title="عرض المتجر"><?=$store['storeName']?></a>
                            <span><?=$store['prodsNum']?></span>
                            <span><?=($store['prodsNum']<=10 && $store['prodsNum']>=3)? ' منتجات':' منتج';?></span>
                        </div>
                        <div class="home-store-card" title="عرض المتجر" <?=$storeLink?>>
                            <div class="store-img-box" $link>
                                <img src="<?=$storeImg?>"/>
                                <div class="overlay">
                                    <span><?=$store['storeName']?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="det-col det-prod-info">
                    <h2><?=$product['prodName']?></h2>
                    <?php
                    $price=number_format($product['price'],0,'.',',');
                    $priceOnSale=number_format($product['priceOnSale'],0,'.',',');
                    if($product['priceOnSale']==0 || empty($product['priceOnSale'])){
                        echo "<span>$price ل.س</span>";
                    }else{
                        echo "<span>$priceOnSale ل.س</span>";
                        echo "<span>$price ل.س</span>";
                    }
                    ?>
                    <p><?=$product['description']?></p>
                </div>
                <div class="det-col prod-colors">
                    <h3>الألوان والمقاسات المتوفرة</h3>
                    <div class="det-sizes-colors">
                        <h4>المقاسات المتوفرة</h4>
                        <div class="all-size-colors">
                            <?php
                                if(!empty($prodSizes)){
                                    foreach($prodSizes as $size){
                                        echo<<<"here1"
                                            <div class="det-card">
                                                <span>{$size[2]}</span>
                                                <div class="colors-of-size">
                                                    <div class="all-size-colors">
                                                    
                                        here1;
                                            if(empty($prodColors[$size[0]])){
                                                //show message that there is no colors for this size
                                                echo '<div class="color-message">
                                                        لم تتم إضافة الوان لهذا المقاس
                                                    </div>';
                                            }else{
                                                foreach($prodColors[$size[0]] as $color){
                                                    
                                                    echo<<<"here2"
                                                    <span
                                                        class="det-card det-card-color"
                                                        data-before-text="{$color[1]}"
                                                        style="background-color: {$color[2]}">
                                                    </span>
                                                    here2;
                                                }
                                            }
                                        echo<<<"here3"
                                                    </div>
                                                </div>
                                            </div>
                                        here3;
                                    }
                                }else{//show message that there is no sizes for this product
                                    echo '<div class="message-container message-box shadow ">
                                            <span>لم تتم إضافة المقاسات</span>
                                        </div>';
                                }
                            ?>
                        </div>
                    </div>
                    <div class="det-sizes-colors">
                        <h4>الألوان المتوفرة</h4>
                        <div class="all-size-colors">
                            <?php
                                    if(!empty($allColors)){
                                        foreach($allColors as $color){
                                            echo<<<"there1"
                                            <div class="det-card det-card-color"
                                                data-before-text="{$color[1]}"
                                                style="background-color: {$color[2]}">
                                                <div class="colors-of-size">
                                                    <span class="all-size-colors">
                                            there1;
                                                        if(empty($allColorSizes[$color[0]])){
                                                            //show message that there is no sizes for this color
                                                            echo '<div class="color-message">
                                                                    لم تتم إضافة مقاسات لهذا اللون
                                                                </div>';
                                                        }else{
                                                            foreach($allColorSizes[$color[0]] as $size){
                                                                echo<<<"there2"
                                                                <div class="det-card">
                                                                    <span class="">{$size[2]}</span>
                                                                </div>
                                                                there2;
                                                            }
                                                        }
                                                    echo<<<"there3"
                                                            </span>
                                                        </div>
                                                    </div>
                                                    there3;
                                        }   
                                    }else{//show message that there is no color for this product
                                        echo '<div class="message-container message-box shadow ">
                                                <span>لم تتم إضافة الألوان</span>
                                            </div>';

                                    }?>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <!-- HTML Code End -->
    <?php
}else{
    echo '<div class="message-container message-box warning ">
            <span>المنتج المطلوب غير موجود</span>
          </div>';
}
?>
    
<?php
include $tpl . 'footer.php';