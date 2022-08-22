<?php

/*
 ** Title Function v1.0
 ** Title Function return Page Title in case the variable $pageTitle is set
 ** in the Page
 ** Return 'Moda' in case the variable not set
 */
function getTitle()
{
  global $pageTitle;
  if (isset($pageTitle)) {
    echo $pageTitle;
  } else {
    echo 'Moda';
  }
}

/*
 ** Password Validation Function v1.0
 ** Password Validation Function Test the Strength of the Password
 ** Strength Password Must be:
 ** 1) Password should be at least 8 characters in length
 ** 2) Should include at least one upper case letter, one lower case letter, one number
 ** The Function return boolean value:
 ** 0 => the Password Not Strong, 1 => the Password Strong
 */
function is_strong($pass)
{
  $uppercase = preg_match('@[A-Z]@', $pass); //check if the password include upper case letter
  $lowercase = preg_match('@[a-z]@', $pass); //check if the password include lower case letter
  $number = preg_match('@[0-9]@', $pass); //check if the password include number

  if (!$uppercase || !$lowercase || !$number || strlen($pass) < 8) {
    return 0;
  } else {
    return 1;
  }
}
/*
 ** Default Image Function v1.0
 ** Default Image Function return default image path in case the actual path not exist
 ** it takes two Parameters:
 ** param 1) actualPath: String of the actual image path
 ** param 2) pathType: Number of the default image path type 
 ** We discuss two type:
 ** pathType = 1 -> default image path for store profileImg
 ** pathType = 2 -> default image path for product itemImg
 */
function imagePath($actualPath , $pathType){
    if($pathType==1){
        $fullActualPath = 'uploades\store_profile_images\\' . $actualPath;
        if(!empty($actualPath) && file_exists($fullActualPath)){
            return $fullActualPath;
        }else{
            return 'layout\images\defaultStore.png';
        }
    }elseif($pathType==2){
        $fullActualPath = 'uploades\product_images\\' . $actualPath;
        if(!empty($actualPath) && file_exists($fullActualPath)){
            return $fullActualPath;
        }else{
            return 'layout\images\default_product.png';
        }
    }
}

/*
 ** Get Items Data v1.0
 ** This Function will be called from StoreProfile Page
 ** From sections: addProduct && editProduct 
 ** It will contact with DB to get data needed for form feilds
 ** And store the result in the parameters to return
 ** It takes three parameters to return the data and one for DB connection
 ** param1) $con: To connect with DB
 ** param2) $classes: To store categories 
 ** param3) $allsizes: To store sizes
 ** param4) $allcolors: To store colors
 */
function getFormDataDB($con,&$classes,&$allsizes,&$allcolors){
    // Get Product Categories from DB and store them in $classes array
    $stmt=$con->prepare('SELECT * FROM class');
    $stmt->execute();
    $classes=$stmt->fetchAll();

    // Get the sizes from the DB and store them in $allsizes array
    $stmt=$con->prepare('SELECT * FROM size');
    $stmt->execute();
    $allsizes=$stmt->fetchAll();

    // Get the colors from the DB and store them in $allcolors array
    $stmt=$con->prepare('SELECT * FROM color WHERE colorID !=35 ORDER BY colorName');
    $stmt->execute();
    $allcolors=$stmt->fetchAll();
    
}

/*
 ** Get Product Details v1.0
 ** This Function will be called from prodDetails.php & StoreProfile.php Page(editProduct section)
 ** It will contact with DB to get product Details
 ** And store the result in the parameters to return
 ** It takes three parameters to return the data and one for DB connection
 ** param1) $con: To connect with DB
 ** param2) $prodID: ID of the Product that we want to get its details
 ** param3) $prodSizes: To store product sizes, it's an assosiative array 
                        the key referes to sizeID and the value its info(ID,sizeType,sizeValue)
 ** param4) $prodColors: To store product colors, it's an assosiative array 
                        the key referes to sizeID and the value array store its colors
                        (each color array store the color info:colorID,colorName,colorValue)
** param5) $allColors: (optional) To store all colors without mapping them to their size
 */
function getProductDetails($con,$prodID,&$prodSizes,&$prodColors,&$allColors=null,&$allColorSizes=null){
    $myAllColors=[];
    $myAllColorSizes=[];
    // Get product details
    $stmt=$con->prepare("SELECT s.sizeID,s.sizeType,s.sizeValue,c.colorID,c.colorName,c.colorValue
    FROM details d, size s, color c
    WHERE s.sizeID=d.sizeID
    AND   c.colorID=d.colorID
    AND   itemID=?");
    $stmt->execute([$prodID]);
    $prodDetails=$stmt->fetchAll();
    // store the colors and sizes in the parameters (an assosiative array)
    foreach($prodDetails as $key=>$row){
        $prodSizes[$row['sizeID']][0]=$row['sizeID'];
        $prodSizes[$row['sizeID']][1]=$row['sizeType'];
        $prodSizes[$row['sizeID']][2]=$row['sizeValue'];
        $prodColors[$row['sizeID']][]=[$row['colorID'], $row['colorName'],$row['colorValue']];
        if($row['colorID']===35){
            $prodColors[$row['sizeID']]=[];
        }
        if($allColors !== null && $row['colorID']!==35){
            $myAllColors[$row['colorID']][0]=$row['colorID'];
            $myAllColors[$row['colorID']][1]=$row['colorName'];
            $myAllColors[$row['colorID']][2]=$row['colorValue'];
            $myAllColorSizes[$row['colorID']][]=[$row['sizeID'], $row['sizeType'],$row['sizeValue']];
    }
    }
    $allColors=$myAllColors;
    $allColorSizes=$myAllColorSizes;
}
/*
 ** Get Product Cards v1.0
 ** This Function will be called from fetchProduct.php & products.php Page
 ** It will contact with DB to get products depending on param: kind
 ** It takes three parameters:
 ** param1) $con: To connect with DB
 ** param2) $kind: may have one of these values:
            1- newProds -> Get Newer Products
            2- trending -> Get the most liked Product
            3- categoryID -> Get the producs with this category
 ** param3) $userID: if is set then get the products with likes and favs information
 ** It return Product Cards filled with data
 */
function getProductCards($con,$kind ,$limit=15){
    // variable to store the final Product Cards
    $productCards="";
    // variable to store info message if there is no products
    $noProductsMessage="لا يوجد منتجات في الموقع للأسف"; // this is the default message
    // variable to store likes and favs information for the user (with userID)
    $userLikesFavs=[];
    // initialize the query statement according to the needed kind of product 
    $prodDetailFeilds="i.itemID,i.prodName,i.description,i.price,i.priceOnSale,i.image";
    $q="";
    $params=[];
    switch ($kind) {
        case 'newProds':
            # Get Newer Products
            $q="SELECT " . $prodDetailFeilds . " FROM item i ORDER BY addDate DESC LIMIT ". $limit;
            break;
        case 'trending':
            # Get the most liked Product
            $q="SELECT " . $prodDetailFeilds . " FROM item i
                LEFT OUTER JOIN favorite f 
                ON i.itemID=f.itemID 
                GROUP BY $prodDetailFeilds
                ORDER BY SUM(f.isLiked)
                DESC
                LIMIT ". $limit;

            break;
        default:
            # the default is the third value which is categoryID 
            # or if none of the expected value then get all data

            // Check if $kind contains a valid category ID
            $stmt=$con->prepare("SELECT classID , className FROM class 
                                 WHERE classID=?");
            $stmt->execute([$kind]);
            if($stmt->rowCount()>0){
                // Get the producs with this category
                $q="SELECT " . $prodDetailFeilds . " FROM item i WHERE i.classID=?";
                $params=[$kind];
                $row=$stmt->fetch();
                $noProductsMessage="إلى الآن لم تتم إضافة منتجات من هذا التصنيف: {$row['className']}";
            }else{
                // The default Query is for all Products
                $q="SELECT " . $prodDetailFeilds . " FROM item i";
            }

            break;
    }
    // prepare & execute the query
    $stmt=$con->prepare($q);
    $stmt->execute($params);
    if($stmt->rowCount()<=0){
        //there is no products,
        $productCards="<div class='message-container message-box warning'>
                            <p>$noProductsMessage</p>
                       </div>";
    }else{
        //there is  products, fetch them and get products likes and favs information
        $products=$stmt->fetchAll();
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
        // fill the $productCards string with html code and information for each product from $products
        foreach($products as $prod){
            // check if the product liked or favored
            // store the class name or nothing 
            $liked=(!empty($userLikesFavs) && $userLikesFavs[$prod['itemID']]['isLiked']==1)?"like-span-clicked":"";
            if(!empty($userLikesFavs) && $userLikesFavs[$prod['itemID']]['isFavorit']==1){
                $favored="love-span-clicked";
                $iconType="fa";
            }else{
                $favored="";
                $iconType="far";
            }
            $imgPath=imagePath($prod['image'],2);
            $productCards .="
                <div class='prod-card' data-prodID='{$prod['itemID']}'>
                    <span class='prod-card-ic love-span $favored'><i class='$iconType fa-heart'></i></span>
                    <div class='prod-img-box'>
                        <img src='$imgPath'>
                    </div>
                    <div class='prod-info'>
                        <h4>{$prod['prodName']}</h4>
                        <p>{$prod['description']}</p>
                        <div class='prod-price'>";
            if((!$prod['priceOnSale']==null) && (!$prod['priceOnSale']==0)){
                
                $productCards .="<span>" . number_format($prod['priceOnSale'],0,'.',',') . " ل.س" . "</span>";
                $productCards .="<span>" . number_format($prod['price'],0,'.',',') . " ل.س" . "</span>";
            }
            else{
                $productCards .="<span>" . number_format($prod['price'],0,'.',',') . " ل.س" . "</span>";
            }
            $productCards .="
                        </div>
                    </div>
                    <span class='prod-card-ic like-span $liked'><i class='fa fa-thumbs-up'></i></span>
                    <a href='prodDetails.php?prodID={$prod['itemID']}' class='prod-card-details-btn'>تفاصيل</a>
                </div>";
        }
    }
    
    // return the product cards
    return $productCards;
}