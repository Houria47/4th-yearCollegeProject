<?php
SESSION_START();
$isProdsPage=true;
$pageTitle = 'Moda | المنتجات';
include 'init.php';
/*// NOTE: Requests for this page are from main nav tabs where
** $_GET['do'] contains category ID
** OR from home page for New added && Trending Products
** New added => $_GET['do'] = newProds
** Trending => $_GET['do'] = trending
*/
$do= ( isset($_GET['do']) ) ? $_GET['do'] : -1 ;
$productsCards=getProductCards($con,$do);
?>
    <!-- HTML Code -->
    <div class="prod-container">
        <div class="prodFilter btn" data-kind="-1" >عرض جميع المنتجات</div>
        <div class="main-cards-container">
            <div class="prod-cards-container">
            <?php
                echo $productsCards;
            ?>
            </div>
        </div>
    </div>

<?php include $tpl . 'footer.php';