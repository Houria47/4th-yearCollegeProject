<?php
SESSION_START();
$pageTitle = 'Moda | متجري';
$showStoreOption="show"; //dummy variable to show store option in nav bar just in this page
include 'init.php';
/*
 ** First of all we need to get store information from Database
 ** The data for storeId which should be set in the SESSION['storeID']
 ** We're pretty sure that the SESSION['storeID'] is set because there is no
 ** way to access this page if the store not login into the site from the
 ** store button in the site nav bar which appears just after store login
 ** Anyway we will check if the storeID is not set for more secure
 */
if (!isset($_SESSION['storeID'])) {
  // redirect to index.php
  header('Location: index.php');
  exit();
}

// get the information from $_SESSION
$storeID = $_SESSION['storeID'];
$storeName = $_SESSION['storeName'];
$location = $_SESSION['location'];
$email = $_SESSION['email'];
$username = $_SESSION['username'];
$openTime = $_SESSION['openTime'];
$closeTime = $_SESSION['closeTime'];
// create Date Object to format the date with date_format() which accept only date Object created with date_create().
$date = date_create($_SESSION['createDate']);
//if there is no profile image Use default one
$imgPath=imagePath($_SESSION['profileImg'],1);

if (
  isset($_GET['do']) &&
  ($_GET['do'] == 'profileInfo' ||
    $_GET['do'] == 'products'   ||
    $_GET['do'] == 'mostLiked'  ||
    $_GET['do'] == 'addProduct' ||
    $_GET['do']=='editProduct')
) {
  $do = $_GET['do'];
} else {
  //set to default option
  $do = 'profileInfo';
}

//show the main nav bar
//main nav will be visible for all $_GET['do'] options 
?>
  <div class="storeProfile-container">
    <!-- store nav start -->
    <div class="store-profile-nav">
      <a class="store-option <?= $do == 'profileInfo'
        ? 'checked'
        : '' ?>" href="?do=profileInfo">معلومات المتجر</a>
      <a class="store-option <?= $do == 'products'
        ? 'checked'
        : '' ?>" href="?do=products">منتجاتي</a>
      <a class="store-option <?= $do == 'mostLiked'
        ? 'checked'
        : '' ?>" href="?do=mostLiked">الأكثر إعجابا</a>
      <a class="store-option <?= $do == 'addProduct'
        ? 'checked'
        : '' ?>" href="?do=addProduct">إضافة منتج</a>
    </div>
    <div class="after-store-nav"></div>
    <!-- store nav end -->
<?php 
//show content depending on $do variable

switch ($do) {
  case 'products':
    // Get store product from DB
    $stmt=$con->prepare("SELECT * FROM item WHERE storeID=? ORDER BY addDate desc");
    $stmt->execute([$storeID]);
    $storeProduct=$stmt->fetchAll()
    ?>
    <!-- Start Product section -->

    <div class="main-cards-container">
        <div class="myProduct-container">
            <h1>المنتجات مرتبة حسب تاريخ الإضافة</h1>
            <div class="prod-cards-container">
            <?php
                foreach($storeProduct as $product){
                    //if there is no product image Use default one
                    $imgPath=imagePath($product['image'],2);
                    echo<<<"part1"
                        <div class="prod-card">
                            <a href="storeManage.php?do=deleteProd&prodID={$product['itemID']}" class="prod-card-ic del-span" title="حذف المنتج"><i class="fa fa-trash"></i></a>
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
                            <a href="StoreProfile.php?do=editProduct&prodID={$product['itemID']}" class="prod-card-ic edit-span"><i class="fa fa-edit"></i></a>
                            <a href="prodDetails.php?prodID={$product['itemID']}" class="prod-card-details-btn" >تفاصيل</a>
                        </div>
                    part2;
                }
            ?>
            </div>
        </div>
    </div>
    <!-- End Product section -->
    <?php
    break;
  case 'mostLiked':
    // Get most Liked product from DB
    $stmt=$con->prepare("SELECT item.itemID,prodName,addDate,price,COUNT(favoritID) AS num 
                         FROM item,favorite
                         WHERE item.itemID=favorite.itemID
                         AND favorite.isLiked=1
                         AND storeID=?
                         GROUP BY item.itemID,prodName,addDate,price
                         ORDER BY COUNT(favoritID) DESC
                         LIMIT 10");
    $stmt->execute([$storeID]);
    $likedProduct=$stmt->fetchAll();
    // Get most Favorite product from DB
    $stmt=$con->prepare("SELECT item.itemID,prodName,addDate,price,COUNT(favoritID) AS num
                         FROM item,favorite
                         WHERE item.itemID=favorite.itemID
                         AND favorite.isFavorit=1
                         AND storeID=? 
                         GROUP BY item.itemID,prodName,addDate,price
                         ORDER BY COUNT(favoritID) DESC
                         LIMIT 10");
    $stmt->execute([$storeID]);
    $favoritProduct=$stmt->fetchAll()
    ?>
    <!-- Start My Products section -->
    <div class="most-liked-container">
        <h1>المنتجات التي نالت إعجاب أكثر من المستخدمين</h1>
        <table>
            <thead>
                <th>اسم المنتج</th>
                <th>عدد الاعجابات</th>
                <th>تاريخ الإضافة</th>
                <th>سعر المنتج</th>
                <th>تعديل</th>
            </thead>
            <tbody>
                <?php
                    foreach($favoritProduct as $prod){
                        if($prod['num']>0){
                            $date=date_format(date_create($prod['addDate']),"A g:i");
                            echo<<<"here"
                                <tr>
                                    <td>{$prod['prodName']}</td>
                                    <td>{$prod['num']} إعجاب</td>
                                    <td>$date</td>
                                    <td>{$prod['price']}</td>
                                    <td><a href="StoreProfile.php?do=editProduct&prodID={$prod['itemID']}"><i class="fa fa-edit"></i></a></td>
                                </tr>
                            here;
                        }
                    }
                ?>
            </tbody>
        </table>
        
        <h1>المنتجات الأكثر إضافة للمفضلة</h1>
        <table>
            <thead>
                <th>اسم المنتج</th>
                <th>عدد الإضافات للمفضلة</th>
                <th>تاريخ الإضافة</th>
                <th>سعر المنتج</th>
                <th>تعديل</th>
            </thead>
            <tbody>
                <?php
                    foreach($likedProduct as $prod){
                        if($prod['num']>0){
                            $date=date_format(date_create($prod['addDate']),"A g:i");
                            echo<<<"here"
                                <tr>
                                    <td>{$prod['prodName']}</td>
                                    <td>{$prod['num']} إضافة</td>
                                    <td>$date</td>
                                    <td>{$prod['price']}</td>
                                    <td><a href="StoreProfile.php?do=editProduct&prodID={$prod['itemID']}"><i class="fa fa-edit"></i></a></td>
                                </tr>
                            here;
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>
    <!-- End My Products section -->
    <?php
    break;
  case 'addProduct':
    // Get data from DB to fill input fields 
    $classes=[];
    $allsizes=[];
    $allcolors=[];
    getFormDataDB($con,$classes,$allsizes,$allcolors);
    ?>
    <!-- Start add product container -->
    <section class="add-prod-container">
        <h2>إضافة منتج</h2>
        <form action="storeManage.php" class="add-prod-form" method="POST" enctype="multipart/form-data">
            <div class="add-prod-flex">
                <div class="add-prod-col">
                    <div class="add-prod-row">
                        <label for="prod_name">اسم المنتج</label>
                        <input type="text" name="prodName" id="prod_name" />
                    </div>
                    <div class="add-prod-row">
                        <label for="prod_desc">الوصف</label>
                        <input type="text" name="prodDesc" id="prod_desc" />
                    </div>
                    <div class="add-prod-row">
                        <label>التصنيف</label>
                        <select name="prodCategory" class="prod-category">
                            <?php
                                foreach($classes as $class){
                                    echo "<option value='{$class['classID']}' data-sizeType='{$class['sizeType']}'>{$class['className']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="add-prod-row price-row">
                        <div class="add-prod-row">
                            <label for="prod_price" >السعر</label>
                            <input
                                type="text"
                                name="prodPrice"
                                id="prod_price"
                                min="0"
                            />
                        </div>
                        <div class="add-prod-row">
                            <label for="prod_priceonsell"
                                >السعر بعد الحسم</label
                            >
                            <input
                                type="text"
                                name="prodPriceonsell"
                                id="prod_priceonsell"
                                min="0"
                            />
                        </div>
                    </div>
                </div>
                <div class="add-prod-col">
                    <div class="add-prod-row">
                        <div class="output-box">
                            <i class="fa fa-upload"></i>
                            <img id="output"/>
                        </div>
                        <label for="add_input_file" class="add-prod-btn-1">حمل صورة المنتج</label>
                        <input
                            class="hide-file-input"
                            id="add_input_file"
                            name="prodPhoto"
                            type="file"
                            accept="image/*"
                            onchange="changeImage(this)"
                        />
                    </div>
                </div>
            </div>
            <!-- add product size and colors -->
            <div id="parent-0" class="add-prod-colorsandsize" >
                <div class="add-detials-section">
                    <div class="add-detials-row">
                        <label >المقاس</label>
                        <select name="prodSize[8]" class="prod-size" data-num="0" >
                            <option value="8" data-sizetype="2">32</option>
                            <option value="9" data-sizetype="2">34</option>
                            <option value="10" data-sizetype="2">36</option>
                            <option value="11" data-sizetype="2">38</option>
                            <option value="12" data-sizetype="2">40</option>
                            <option value="13" data-sizetype="2">42</option>
                            <option value="14" data-sizetype="2">44</option>
                            <option value="15" data-sizetype="2">46</option>
                            <option value="25" data-sizetype="2">standard</option>
                        </select>
                    </div>
                    <div class="add-detials-row">
                        <label>أضف الألوان المتوفرة</label>
                        <select
                            name="prodSize[8][]"
                            class="js-prod-color select-hidden"
                            data-num="0"
                            multiple
                        >
                        <?php
                            foreach($allcolors as $color){
                                echo "<option value='{$color['colorID']}' data-colorValue='{$color['colorValue']}'>{$color['colorName']}</option>";
                            }
                        ?>
                        </select>
                        <select class="js-prod-color" data-num="0">
                        <?php
                            foreach($allcolors as $color){
                                echo "<option value='{$color['colorID']}' data-colorValue='{$color['colorValue']}'>{$color['colorName']}</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="prod-data add-prod-row">
                </div>
            </div>
            <!-- add new size button -->
            <div id="js-add-new-size" class="add-prod-btn-2">
                <span>أضف مقاس جديد</span>
            </div>
            <!-- Submit product data button -->
            <label for="add-prod-submit" class="add-prod-row add-prod-btn-1">
                أضف منتج
                <input id ="add-prod-submit" type="submit" value="أضف المنتج" />
            </label>
            <input name="do" value="addItem" type="hidden"/>

        </form>
        <?php
            // this div is invisible, contains size options to clone from them in 
            // js code to sizeSelect elements in each add-prod-colorsandsize div  
            echo '<div id="basic-size-options">';
            foreach($allsizes as $size){
                echo "<option value='{$size['sizeID']}' data-sizeType='{$size['sizeType']}'>{$size['sizeValue']}</option>";
            }
            echo '</div>';
            ?>
    </section>
    <!-- End add product container -->
    <?php
    break;
  case 'editProduct':
    
    // Get data of the product we want to update
    // Get Product ID from GET request
    $prodID=$_GET['prodID'];
    $stmt=$con->prepare("SELECT * FROM item WHERE itemID=?");
    $stmt->execute([$prodID]);
    if($stmt->rowCount()>0){
        // the product exist,fetch the data
        $prod=$stmt->fetch();
        // Get data from DB to fill input fields 
        $classes=[];
        $allsizes=[];
        $allcolors=[];
        getFormDataDB($con,$classes,$allsizes,$allcolors);
        // Get product details
        $prodSizes=[];
        $prodColors=[];
        getProductDetails($con,$prodID,$prodSizes,$prodColors);
        // <!-- call js function to store these data as defualt value (to make js validate funcs work correctly) -->
        if(!empty($prodSizes)){
            $sizes=json_encode($prodSizes);
            $colors=json_encode($prodColors);

            echo<<<"here"
            <script type="text/javascript">
                    let sizes = $sizes;
                    let colors = $colors;
                    // code must execute After page load so we can use the function in js file 
                    window.addEventListener('DOMContentLoaded', (event) => {
                        setSizesColors(sizes,colors);
                    });
            </script>
            here;
        }
        
    }else{
        // the product ID not valid , Product not exist
        // redirect to store Info section
        header('Location: StoreProfile.php?do=profileInfo');
        exit();
    }
    ?>
    <!-- Start edit product container, same as add product container -->
    <section class="add-prod-container">
        <div class="store-head">
            <h2>تعديل المنتج</h2>
            <a href="storeManage.php?do=deleteProd&prodID=<?=$prodID?>">حذف المنتج</a>
        </div>
        <form action="storeManage.php" class="add-prod-form" method="POST" enctype="multipart/form-data">
            <div class="add-prod-flex">
                <div class="add-prod-col">
                    <div class="add-prod-row">
                        <label for="prod_name">اسم المنتج</label>
                        <input value="<?=$prod['prodName']?>" type="text" name="prodName" id="prod_name" />
                    </div>
                    <div class="add-prod-row">
                        <label for="prod_desc">الوصف</label>
                        <input value="<?=$prod['description']?>" type="text" name="prodDesc" id="prod_desc" />
                    </div>
                    <div class="add-prod-row">
                        <label>التصنيف</label>
                        <select name="prodCategory" class="prod-category">
                            <?php
                                foreach($classes as $class){
                                    if($prod['classID']==$class['classID'])//make this option selected
                                    {
                                        echo "<option selected value='{$class['classID']}'  data-sizeType='{$class['sizeType']}'>{$class['className']}</option>";
                                        $prodSizeType=$class['sizeType'];
                                    }else
                                        echo "<option value='{$class['classID']}' data-sizeType='{$class['sizeType']}'>{$class['className']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="add-prod-row price-row">
                        <div class="add-prod-row">
                            <label for="prod_price" >السعر</label>
                            <input
                                value="<?=$prod['price']?>"
                                type="text"
                                name="prodPrice"
                                id="prod_price" 
                                min="0"
                            />
                        </div>
                        <div class="add-prod-row">
                            <label for="prod_priceonsell"
                                >السعر بعد الحسم</label
                            >
                            <input
                                value="<?=$prod['priceOnSale']?>"
                                type="text"
                                name="prodPriceonsell"
                                id="prod_priceonsell"
                                min="0"
                            />
                        </div>
                    </div>
                </div>
                <div class="add-prod-col">
                    <div class="add-prod-row">
                        <div class="output-box">
                            <i class="fa fa-upload"></i>
                            <img id="output" src="<?=imagePath($prod['image'],2)?>" />
                        </div>
                        <label for="add_input_file" class="add-prod-btn-1">تغيير صورة المنتج</label>
                        <input
                            class="hide-file-input"
                            id="add_input_file"
                            name="prodPhoto"
                            type="file"
                            accept="image/*"
                            onchange="changeImage(this)"
                        />
                    </div>
                </div>
            </div>
            <!-- add product size and colors -->
        <?php
        $count=0;//for #parent id
        if(empty($prodSizes)){
            //there is no detials for the product ,show basic input fields
            ?>
            <div id="parent-0" class="add-prod-colorsandsize" >
                <div class="add-detials-section">
                    <div class="add-detials-row">
                        <label >المقاس</label>
                        <select name="prodSize[8]" class="prod-size" data-num="0" >
                            <?php
                            foreach($allsizes as $idx => $size){
                                if($size['sizeType']==$prodSizeType){
                                    if($idx==0)//make this option selected
                                        echo "<option selected value='$sizeID'  data-sizeType='{$sizeDetails[1]}' data-selected='true'>{$sizeDetails[2]}</option>";
                                    else                                        
                                        echo "<option value='{$size['sizeID']}'  data-sizeType='{$size['sizeType']}'>{$size['sizeValue']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="add-detials-row">
                        <label>أضف الألوان المتوفرة</label>
                        <select
                            name="prodSize[8][]"
                            class="js-prod-color select-hidden"
                            data-num="0"
                            multiple
                        >
                        <?php
                            foreach($allcolors as $color){
                                echo "<option value='{$color['colorID']}' data-colorValue='{$color['colorValue']}'>{$color['colorName']}</option>";
                            }
                        ?>
                        </select>
                        <select class="js-prod-color" data-num="0">
                        <?php
                            foreach($allcolors as $color){
                                echo "<option value='{$color['colorID']}' data-colorValue='{$color['colorValue']}'>{$color['colorName']}</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="prod-data add-prod-row">
                </div>
            </div>
            <?php
        }else{
            // show input fields with product detaisl
            foreach($prodSizes as $sizeID => $sizeDetails){
                ?>
                <div id="parent-<?=$count?>" class="add-prod-colorsandsize">
                    <div class="add-detials-section">
                        <div class="add-detials-row">
                            <label >المقاس</label>
                            <select name="prodSize[<?=$sizeID?>]" class="prod-size" data-num="<?=$count?>" >
                                <?php
                                    foreach($allsizes as $size){
                                        if($size['sizeType']==$prodSizeType){
                                            if($size['sizeID']==$sizeID)//make this option selected
                                                echo "<option selected value='$sizeID'  data-sizeType='{$sizeDetails[1]}' data-selected='true'>{$sizeDetails[2]}</option>";
                                            else                                        
                                                echo "<option value='{$size['sizeID']}'  data-sizeType='{$size['sizeType']}'>{$size['sizeValue']}</option>";

                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="add-detials-row">
                            <label>أضف الألوان المتوفرة</label>
                            <select
                                name="prodSize[<?=$sizeID?>][]"
                                class="js-prod-color select-hidden"
                                data-num="<?=$count?>"
                                multiple
                            >
                            <?php
                                foreach($allcolors as $color){
                                    echo "<option value='{$color['colorID']}' data-colorValue='{$color['colorValue']}'>{$color['colorName']}</option>";
                                }
                            ?>
                            </select>
                            <select class="js-prod-color" data-num="<?=$count?>">
                            <?php
                                foreach($allcolors as $color){
                                    echo "<option value='{$color['colorID']}' data-colorValue='{$color['colorValue']}'>{$color['colorName']}</option>";
                                }
                            ?>
                            </select>
                        </div>
                    </div>
                    <div class="prod-data add-prod-row">
                        <?php
                        foreach($prodColors[$sizeID] as $color )
                            echo "<span style='background-color:{$color[2]}' data-num='$count' data-colorID='{$color[0]}'><i class='fa fa-trash'></i></span>";
                        ?>
                    </div>
                </div>
                <?php
                $count++;
            }
        }?>
            <!-- add new size button -->
            <div id="js-add-new-size" class="add-prod-btn-2">
                <span>أضف مقاس جديد</span>
            </div>
            <!-- Submit product data button -->
            <label for="add-prod-submit" class="add-prod-row add-prod-btn-1">
                تعديل
                <input id ="add-prod-submit" type="submit" value="تعديل" />
            </label>
            <input name="do" value="editProduct" type="hidden"/>
            <input name="prodID" value="<?=$prodID?>" type="hidden"/>

        </form>
        <?php
            // this div is invisible, contains size options to clone from them in 
            // js code to sizeSelect elements in each add-prod-colorsandsize div  
            echo '<div id="basic-size-options">';
            foreach($allsizes as $size){
                echo "<option value='{$size['sizeID']}' data-sizeType='{$size['sizeType']}'>{$size['sizeValue']}</option>";
            }
            echo '</div>';
            ?>
    </section>
    <!-- End update product container -->
    <?php
    break;
  default://$do=profileInfo
     ?>
    <section class="profile-data-section">
        <?php if (isset($_GET['activeEdit']) && $_GET['activeEdit'] == 'active') { ?>
        <!-- show update form -->
        <div class="form-login">
            <h1>تعديل بيانات المتجر</h1>
            <form action="storeManage.php" method="POST" enctype="multipart/form-data">
                <div class="input-flex">
                    <label>اسم المتجر</label>
                    <input class="input-form" name="storeName" type="text" placeholder="اسم المتجر" value="<?= $storeName ?>" />
                </div>
                <div class="input-flex">
                    <label>البريد الإلكتروني</label>
                    <input class="input-form" name="email" type="text" placeholder="البريد الإلكتروني" value="<?= $email ?>"  />
                </div>
                <div class="input-flex">
                <label>العنوان</label>
                <input class="input-form" name="location" type="text" placeholder="العنوان" value="<?= $location ?>"  />
                </div>
                <div class="input-flex">
                        
                    <label>ساعة بدء العمل</label>
                    <input class="input-form" name="openTime" type="time" placeholder="ساعة بدء العمل" value="<?=$openTime ?>"  />
                </div>
                <div class="input-flex">
                    <label>ساعة انتهاء العمل</label>
                    <input class="input-form" name="closeTime" type="time" placeholder="ساعة انتهاء العمل" value="<?=$closeTime ?>"/>
                </div> 
                <div class="output-box">
                    <i class="fa fa-upload"></i>
                    <img id="output" src="<?=$imgPath?>"/>
                </div>
                <label for="file-input" class="file-input-btn"><i class="fa fa-upload"></i> اختر صورة للمتجر</label>
                <input id="file-input" class="hide-file-input" name="profileImg" type="file" placeholder="صورة المتجر" onchange="changeImage(this)"  />
                <input class="btn-submit" type="submit" value="تعديل البيانات" />
                <input name="do" value="updateProfile" type="hidden"/>
            </form>
        <?php } else { ?>
        <!-- show store information -->
        <div class="profile-head">
            <div class="profile-pic-box">
                <img src="<?=$imgPath?>" />
            </div>
            <div class="vertical-separator"></div>
            <h1 class="store-name"><?=$storeName?></h1>
        </div>
        <hr />
        <div class="profile-data">
            <div class="data-row">
                <span>اسم المتجر :</span>
                <p><?=$storeName?></p>
            </div>
            <div class="data-row">
                <span>العنوان :</span>
                <p><?=$location?></p>
            </div>
            <div class="data-row">
                <span>البريد :</span>
                <p><?=$email?></p>
            </div>
            <div class="data-row">
                <span>ساعة بدء العمل :</span>
                <p><?=date_format(date_create($openTime),"A g:i");?></p>
            </div>
            <div class="data-row">
                <span>ساعة انتهاء العمل :</span>
                <p><?=date_format(date_create($closeTime),"A g:i");?></p>
            </div>
            <div class="data-row">
                <span>تاريخ الانشاء :</span>
                <p><?=date_format($date,'Y M j')?></p>
            </div>
        </div>
        <a class="btn" href="?do=profileInfo&activeEdit=active">تعديل بيانات المتجر</a>
        <a class="btn" href="storeManage.php?do=attemptToDeleteStore&storeID=<?=$storeID?>">حذف حساب المتجر</a>
        <?php } ?>
    </section>
     <?php 
     break;
} ?>
  </div> <!-- NOTE: the close tag for the main container :  storeProfile-container -->
<?php include $tpl . 'footer.php';
