<?php
SESSION_START();
$pageTitle = 'Moda | المنتجات';
include 'init.php';
//GET user id from session
$userID=$_SESSION['userID'];
/*
** if Request Method is GET =?request for clear try to clear the fav
** $_GET['do'] can be
** 1- 'tryClearFav' -> show confirm message
** 1- 'clearFav' -> then clear the fav
*/
if($_SERVER['REQUEST_METHOD']=='GET' && isset($_GET['do'])){
    //get do 
    if($_GET['do']=='tryClearFav'){
        //try to clear the fav, show confirm message
        //check if the favorite not empty
        if($_GET['favsize']>0){
            //show confirm message
            $tryClearFavMessage= '<div class="message-container message-box error"><i class="fa fa-close"></i>
                                <span>هل أنت متأكد من حذف جميع المنتجات من مفضلتك؟</span>
                                <a class="btn" href="?do=clearFav">نعم</a>
                                </div>';
        }else{
            //show message that the favorite already empty
            $tryClearFavMessage= '<div class="message-container message-box warning "><i class="fa fa-close"></i>
                                <span>المفضلة لا تحوي عناصر لحذفها</span>
                                </div>';
        }
    }elseif($_GET['do']=='clearFav'){
        $stmt=$con->prepare("UPDATE favorite SET isFavorit=0 WHERE userID=?");
        $stmt->execute([$userID]);
        if($stmt->rowCount()>0){
            $clearFavMessage='<div class="message-container message-box success"><i class="fa fa-close"></i>
                              <p>تم حذف جميع المنتجات من المفضلة</p>
                              </div>
                              <div class="back-to-add-prod-btn" onclick=\'location.href="products.php?do=-1";\'>استعراض منتجات جديدة</div>
                              <div class="message-container message-box info">
                                <p>المفضلة فارغة</p>
                              </div>
                              ';
        }//else, idk what could happend
    }
}
//Get the Products from DB
$stmt=$con->prepare("SELECT i.itemID,i.prodName,i.description,i.price,i.priceOnSale,i.image, f.isLiked,f.isFavorit
                     FROM item i,favorite f
                     WHERE i.itemID = f.itemID
                     AND f.userID=?
                     AND f.isFavorit=1
                     ");
$stmt->execute([$userID]);
$products=$stmt->fetchAll();
// echo "<pre>";
// print_r($products);
// echo "</pre>";
?>
<!-- HTML Code -->
<div class="favorit-container">
    <?php
        
    ?>
    <div class="store-head">
        <h2>مفضلتي</h2>
        <a href="?do=tryClearFav&favsize=<?=count($products);?>" >إزالة جميع المنتجات من المفضلة</a>
    </div>
    <?php   
        
        if(isset($tryClearFavMessage)){
            echo $tryClearFavMessage;
        }
        if(isset($clearFavMessage)){
            echo $clearFavMessage;
        }else{
            if(empty($products)){
                echo '<div class="message-container message-box info">
                        <p>المفضلة فارغة، لم تقم بإضافة أيّة منتجات للمفضلة</p>
                    </div>
                    <div class="back-to-add-prod-btn" onclick=\'location.href="products.php?do=-1";\'>استعراض منتجات</div>
                    ';
            }
        }
        ?>
    
    <div class="main-cards-container">
        <div class="prod-cards-container">
            <?php
                foreach($products as $prod){
                    //if there is no product image Use default one
                    // check if the product liked, store the class name or nothing 
                    $liked=($prod['isLiked']==1)?"like-span-clicked":"";
                    $imgPath=imagePath($prod['image'],2);
                    echo<<<"part1"
                        <div class="prod-card" data-prodID='{$prod['itemID']}'>
                        <span class='prod-card-ic love-span love-span-clicked'><i class='fa fa-heart'></i></span>
                            <div class="prod-img-box">
                                <img src="$imgPath">
                            </div>
                            <div class="prod-info">
                                <h4>{$prod['prodName']}</h4>
                                <p>{$prod['description']}</p>
                                <div class="prod-price">
                    part1;
                    if((!$prod['priceOnSale']==null) && (!$prod['priceOnSale']==0)){
                        
                        echo        "<span>" . number_format($prod['priceOnSale'],0,'.',',') . " ل.س" . "</span>";
                        echo        "<span>" . number_format($prod['price'],0,'.',',') . " ل.س" . "</span>";
                    }
                    else{
                        echo        "<span>" . number_format($prod['price'],0,'.',',') . " ل.س" . "</span>";
                    }
                    echo<<<"part2"
                                </div>
                            </div>
                            <span class='prod-card-ic like-span $liked'><i class='fa fa-thumbs-up'></i></span>
                            <a href="prodDetails.php?prodID={$prod['itemID']}" class="prod-card-details-btn" >تفاصيل</a>
                        </div>
                    part2;
                }
            ?>
        </div>
    </div>
</div> <!-- favorit-container close tag -->


<?php include $tpl . 'footer.php';