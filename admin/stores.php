<?php
SESSION_START();
$pageTitle = 'Moda | المتاجر';
$showStoreOption = 'Show'; //dummy variable to show store option in nav bar
include 'init.php';
// get the stores from the database
$stmt = $con->prepare('SELECT store.storeID,profileImg,storeName,location,openTime,closeTime,
                        COUNT(itemID) as productsNum, max(addDate) as lastAddDate
                        FROM store LEFT OUTER JOIN item
                        ON store.storeID=item.storeID
                        GROUP BY storeID,profileImg,storeName,location,openTime,closeTime');
$stmt->execute([]);
$stores=$stmt->fetchAll();
?>

    <div class="stores-container">
        <div class="store-cards-container">
            <?php
                foreach($stores as $store){
                    if($store['lastAddDate']!=null)
                        $date=date_format(date_create($store['lastAddDate']),'Y M j');
                    else
                        $date="لا يوجد";
                ?>
                <div class="store-card">
                    <figure class="card-image-box">
                        <img src="<?= imagePath($store['profileImg'],1)?>" />
                    </figure>
                    <div class="card-content">
                        <div class="card-details">
                            <a href="#" class="card-name"><?=$store['storeName']?></a>
                            <h2 class="card-title"><?=$store['location']?></h2>
                            <div class="card-data">
                                <h3><?=$store['productsNum']?><br /><span><?=($store['productsNum']<=10 && $store['productsNum']>=3)? ' منتجات':' منتج';?></span></h3>
                                <h3><?=date_format(date_create($store['openTime']),"A g:i");?><br /><span>بدء العمل</span></h3>
                                <h3><?=date_format(date_create($store['closeTime']),"A g:i");?><br /><span>انتهاء العمل</span></h3>
                                <!-- <h3>#=$date<br /><span>آخر إضافة</span></h3> -->
                            </div>
                            <div class="card-btns">
                                <a href="oneStore.php?do=info&storeID=<?=$store['storeID']?>"> تفاصيل</a>
                                <a href="oneStore.php?do=products&storeID=<?=$store['storeID']?>">منتجات</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php       
            }
            ?>
        </div>
    </div>
<?php include $tpl . 'footer.php';