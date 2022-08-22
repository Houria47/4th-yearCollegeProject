<?php
SESSION_START();
$pageTitle = 'Moda | إعدادات المتجر';
include 'init.php';
/*
 ** This page for store profile update & store add items  validation
 ** and takes GET rquest only for delete product or delete store
 ** 
 */

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get do to insure that the action is to delete
    $do=$_GET['do'];
    if($do=='deleteProd'){
        //Request to Detele a product
        //OK, Delete the product and show Deletion message
        // Get Product ID
        $prodID=$_GET['prodID']; 
        // delete its deltails from details table
        $stmt=$con->prepare('DELETE FROM details WHERE itemID=?');
        $stmt->execute([$prodID]);
        // delete its records from favorite
        $stmt=$con->prepare('DELETE FROM favorite WHERE itemID=?');
        $stmt->execute([$prodID]);
        // delete the product
        $stmt=$con->prepare('DELETE FROM item WHERE itemID=?');
        $stmt->execute([$prodID]);
        if($stmt->rowCount()>0){
            //show success message that the delete done
            echo '<div class="message-container message-box success">';
            echo '<p>تم الحذف بنجاح</p>';
            echo '</div>';
            echo '<div class="message-container">';
            echo '<div class=" message-box info" > سيتم تحويلك لصفحة المنتجات خلال 3 ثوانٍ</div>';
            echo '</div>';
            header('refresh:3 url=StoreProfile.php?do=products');
            exit();
        }else{
            // deletion did not done
            // redirect to StoreProfilepage products section
            header('Location:StoreProfile.php?do=products');
            exit();
        }

    }elseif($do="attemptToDeleteStore"){
        //Request to Detele a store
        // show form to confirm deletion
        //show success message that the delete done
        echo<<<"here"
            <div class="login-container">
                <div class="message-container message-box error">
                    <p>هل أنت متأكد من حذف الحساب؟</p>
                    <p>سيتم فقدان كافة بياناتك ومنتجاتك من الموقع في حال تم تأكيد الحذف</p>
                </div>
                <form class="form-login" action="{$_SERVER['PHP_SELF']}" method="POST">
                    <input name="storeID" value="{$_GET['storeID']}" type="hidden" />
                    <input name="do" value="deleteStore" type="hidden" />
                    <input class="btn-submit" type="submit" value="حذف الحساب " />
                    <a class="btn-submit" href="StoreProfile.php?do=profileInfo">إالغاء</a>
                </form>
            </div>
        here;
    }
}else
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /* 
        ** /do/ in POST Request will have valid value only From
        ** StoreProfile.php update section, add product section and editProduct section
        ** It can be a request to confirm store account deletion
        ** Other Request not vaild => redireact to index.php
        */
        if (
            isset($_POST['do']) &&
            ($_POST['do'] == 'updateProfile' ||
            $_POST['do'] == 'addItem'   ||
            $_POST['do'] == 'deleteStore'   ||
            $_POST['do'] == 'editProduct')
        ) {
            $do = $_POST['do'];
        } else {
            //set to default option
            $do = 'updateProfile';
        }
        switch ($do) {
            case 'updateProfile':
                //Request to update

                //get update information

                $storeName = $_POST['storeName'];
                $email = $_POST['email'];
                $location = $_POST['location'];
                $openTime = $_POST['openTime'];
                $closeTime = $_POST['closeTime'];

                //check if the img changed
                $updateImgFlag=(empty($_FILES['profileImg']['name']))? 0 : 1;
                
                if($updateImgFlag){
                    //get uploaded Profile Image Info
                    $imgName = $_FILES['profileImg']['name'];
                    $imgSize = $_FILES['profileImg']['size'];
                    $imgTmp = $_FILES['profileImg']['tmp_name'];
                    $imgType = $_FILES['profileImg']['type'];
                }

                //check if update Information are valid

                $errors = []; //array to add error messages

                //check if open time after closeTime
                if(strtotime($openTime) >= strtotime($closeTime)){  $errors[] = 'وقت انتهاء الدوام يجب ان يكون بعد وقت بدء الدوام';}
                //check if name, email and location are empty and if email is valid

                if (empty($storeName)) {    $errors[] = 'حقل الاسم لا يجب أن يكون فارغ';}

                if (empty($email))     {    $errors[] = 'حقل الايميل لا يجب أن يكون فارغ';
                } else {
                    //check if email is valid
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {   $errors[] = 'يرجى ادخال ايميل صحيح'; }
                }

                if($updateImgFlag){
                    //Image validation
                    $imgAllowedExtention=['jpeg','jpg','png','gif'];

                    //get image extention

                    // $imgExtention = strtolower(end(array(explode('.',$imgName)))); // this is the old way to get file extention, yield an error
                    $imgExtention=pathinfo($imgName,PATHINFO_EXTENSION); // this is the new way much better to get file extention
                    if ( !empty($imgName) && !in_array($imgExtention,$imgAllowedExtention) ) {    
                        $errors['name'] ='نوع الصورة غير مقبول';
                    }
                }

                // if the Information valid, insert new user to Database
                if (empty($errors)) {

                    //get storeID from the SESSTION 
                    $storeID=$_SESSION['storeID'];

                    if($updateImgFlag){
                        // Query with image update
                        //change uploaded image name & move it from temp path to uploads folder

                        $imgNameFinal= rand(1,1000000) . '_' . $imgName;

                        //NOTE: IMPROVE THE CODE: check if image name already exist in uploades folder -> change the name
                        //NOTE: IMPROVE THE CODE: check if uploades folder not exist or have no permissions -> create the folder & change the mode
                        
                        move_uploaded_file($imgTmp,"uploades\store_profile_images\\" . $imgNameFinal);
                        // Prepare the Query
                        $stmt = $con->prepare('UPDATE store SET 
                                                            storeName=?,
                                                            location=?,
                                                            email=?,
                                                            profileImg=?,
                                                            openTime=?,
                                                            closeTime=?
                                                            WHERE storeID = ? ');
                        $status=$stmt->execute([$storeName,$location,$email,$imgNameFinal,$openTime,$closeTime,$storeID]);
                    }else{
                        // Query without image update
                        // Prepare the Query
                        $stmt = $con->prepare('UPDATE store SET 
                                                            storeName=?,
                                                            location=?,
                                                            email=?,
                                                            openTime=?,
                                                            closeTime=?
                                                            WHERE storeID = ? ');
                        $status=$stmt->execute([$storeName,$location,$email,$openTime,$closeTime,$storeID]);
                    }
                    // Check if the profile information updated successfully
                    if ($status) {
                        $_SESSION['storeName'] = $storeName;
                        $_SESSION['email'] = $email;
                        $_SESSION['location'] = $location;
                        $_SESSION['openTime'] = $openTime;
                        $_SESSION['closeTime'] = $closeTime;
                        if($updateImgFlag){
                             $_SESSION['profileImg'] = $imgNameFinal;
                        }
                        //show success message that the update done
                        echo '<div class="message-container message-box success">';
                        echo '<p>تم التعديل بنجاح</p>';
                        echo '</div>';
                        echo '<div class="message-container">';
                        echo '<div class=" message-box info" > سيتم تحويلك لصفحة المعلومات خلال 3 ثوانٍ</div>';
                        echo '</div>';
                        header('refresh:3 url=StoreProfile.php?do=profileInfo');
                        exit();
                    }else{
                        //show error message that the update did not done
                        echo '<div class="message-container message-box error">';
                        echo '<p>حدث خطأ ما, لم يتم التعديل</p>';
                        echo '</div>';
                        echo '<div class="message-container">';
                        echo '<div class=" message-box info" > سيتم تحويلك لصفحة التعديل خلال 4 ثوانٍ</div>';
                        echo '</div>';
                        header('refresh:4 url=StoreProfile.php?do=profileInfo&activeEdit=active');
                        exit();
                    }
                } else {
                    //Show Errors Messages And redirect to update section
                    echo "<div class='message-container message-box error'>";
                    foreach ($errors as $error) {
                    echo "<p>$error</p>";
                    }
                    echo '</div>';
                    echo '<div class="message-container">';
                    echo '<div class=" message-box info" > سيتم تحويلك لصفحة التعديل خلال 4 ثوانٍ</div>';
                    echo '</div>';
                    header('refresh:4 url=StoreProfile.php?do=profileInfo&activeEdit=active');
                    exit();

                }
                break;
            
            case 'addItem':
                // Request to insert a product
                // First get the Product information from the POST request
                $prodName=$_POST['prodName'];
                $prodDesc=$_POST['prodDesc'];
                $prodCategory=$_POST['prodCategory'];
                $prodPrice=$_POST['prodPrice'];
                $prodPriceonsell=$_POST['prodPriceonsell'];
                $prodSize=$_POST['prodSize'];
                
                // get uploaded Product Image Info
                $imgName = $_FILES['prodPhoto']['name'];
                $imgSize = $_FILES['prodPhoto']['size'];
                $imgTmp = $_FILES['prodPhoto']['tmp_name'];
                $imgType = $_FILES['prodPhoto']['type'];
                // check if insert Information are valid

                $errors = []; //array to add error messages

                // Check if product name, price are empty
                // We know that Product categorya, description and sizes can't be empty cuz this solved in client side (frontend) 
                // Product colors and price on sell can be empty
                if (empty($prodName)) {    $errors[] = 'حقل الاسم لا يجب أن يكون فارغ';}
                if (empty($prodPrice))     
                {    
                    $errors[] = 'حقل السعر لا يجب أن يكون فارغ';
                }else{
                    if($prodPrice <= 0)  {   $errors[] = 'حقل السعر يجب أن يكون أكبر من الصفر'; }
                }
                
                // check if price > prodPriceonsell, if not add error message
                if($prodPriceonsell!=''){
                    if($prodPriceonsell <= 0){
                        $errors[] = 'حقل السعر بعد الحسم يجب أن يكون أكبر من الصفر';
                    }
                    if($prodPrice <= $prodPriceonsell)  {   $errors[] = 'السعر بعد الحسم يجب أن يكون أقل من السعر الأساسي'; }
                }
                
                //Image validation
                if(empty($_FILES['prodPhoto']['name'])){
                    $errors[] = 'حقل الصورة لا يجب أن يكون فارغ';
                }else{
                    $imgAllowedExtention=['jpeg','jpg','png','gif'];

                    //get image extention

                    // $imgExtention = strtolower(end(array(explode('.',$imgName)))); // this is the old way to get file extention, yield an error
                    $imgExtention=pathinfo($imgName,PATHINFO_EXTENSION); // this is the new way much better to get file extention
                    if ( !empty($imgName) && !in_array($imgExtention,$imgAllowedExtention) ) {    
                        $errors[] ='نوع الصورة غير مقبول';
                    }
                }

                // if the Information valid, insert product to Database
                if (empty($errors)) {
                    // change uploaded image name & move it from temp path to uploads folder
                    // NOTE: IMPROVE THE CODE: check if image name already exist in uploades folder -> change the name
                    // NOTE: IMPROVE THE CODE: check if uploades folder not exist or have no permissions -> create the folder & change the mode
                    $imgNameFinal= rand(1,1000000) . '_' . $imgName;
                    move_uploaded_file($imgTmp,"uploades\product_images\\" . $imgNameFinal);

                    //get storeID from the SESSTION 
                    $storeID=$_SESSION['storeID'];

                    // Now Query code to insert the product
                    // First, insert the basic data into item table
                    $stmt = $con->prepare('INSERT INTO item(prodName,description,addDate,price,priceOnSale,image,storeID,classID) 
                                                VALUES(:pname,:pdesc,now(),:pprice,:onsale,:img,:storeId,:classId)
                                        ');
                    $stmt->execute([
                        'pname' => $prodName,
                        'pdesc' => $prodDesc,
                        'pprice' => $prodPrice,
                        'onsale' => $prodPriceonsell,
                        'img' => $imgNameFinal,
                        'storeId' => $storeID,
                        'classId' => $prodCategory,
                    ]);
                    // Get ID of inserted item
                    $itemID=$con->lastInsertId();
                    // Second, insert Product details into details table 
                    // details:the sizes and the colors of each size
                    // The array $prodSize is accociatve  array contains sizes and colors
                    // The key indicate the sizes, the value is an array indicate the colors of this size
                    $q='INSERT INTO details(itemID,sizeID,colorID) Values(:itemID,:sizeID,:colorID)';
                    foreach($prodSize as $size => $colors){
                       if((gettype($colors)!=='string')){
                            foreach($colors as $color){
                                $stmt=$con->prepare($q);
                                $stmt->execute([
                                    'itemID' => $itemID,
                                    'sizeID' => $size,
                                    'colorID' => $color
                                ]);
                            }
                       }else{
                            $stmt=$con->prepare($q);
                            $stmt->execute([
                                'itemID' => $itemID,
                                'sizeID' => $colors,
                                'colorID' => 35
                            ]);
                       }
                    }
                    // Check if the data inserted
                    if($stmt->rowCount() > 0){
                        // Get the inserted data to show
                        $stmt=$con->prepare('SELECT prodName,description,price,priceOnSale,sizeValue,colorName 
                                            FROM item ,size,color,details
                                            WHERE   item.itemID=?
                                            and     details.itemID=item.itemID
                                            and     details.sizeID=size.sizeID
                                            and     details.colorID=color.colorID
                                            ');
                        $stmt->execute([$itemID]);
                        $rows=$stmt->fetchAll();
                        // Show message that the product added successfully
                        $price=number_format($rows[0]['price'],0,'.',',');
                        $priceOnSale=number_format($rows[0]['priceOnSale'],0,'.',',');
                        echo<<<"message1"
                            <div class="addProd-insertInfo-container ">
                                <div class="message-container message-box success">
                                    <p>تمت إضافة المنتج بنجاح</p>
                                </div>
                                <div class="message-container message-box info">
                                    <p>البيانات التي تم ادخالها:</p>
                                    <h2>{$rows[0]['prodName']}</h2>
                                    <p>{$rows[0]['description']}</p>
                                    <p>السعر قبل الحسم $price ل.س</p>
                                    <p>السعر بعد الحسم $priceOnSale ل.س</p>
                                    <table class="styled-table">
                                        <thead>
                                            <tr>
                                                <th>المقاس</th>
                                                <th>اللون</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        message1;
                                    foreach($rows as $row){
                                        echo<<<"row"
                                            <tr>
                                                <td>{$row['sizeValue']}</td>
                                                <td>{$row['colorName']}</td>
                                            <tr>
                                        row;
                                    }
                        echo<<<"message2"
                                        </tbody>
                                    </table>
                                </div>
                                <div class="back-to-add-prod-btn" onclick='location.href="StoreProfile.php?do=addProduct";'>
                                    عودة لصفحة إضافة منتج
                                </div>
                            </div>
                        message2;
                    }
                }else{
                    // Show Errors Messages And redirect to AddProduct section
                    echo "<div class='message-container message-box error'>";
                    foreach ($errors as $error) {
                    echo "<p>$error</p>";
                    }
                    echo '</div>';
                    echo '<div class="message-container">';
                    echo '<div class=" message-box info" > سيتم تحويلك لصفحة التعديل خلال 4 ثوانٍ</div>';
                    echo '</div>';
                    header('refresh:4 url=StoreProfile.php?do=addProduct');
                    exit();

                }
                break;
            case 'editProduct':
                // same as addItem code, but before inserting the details(colors&sizes) 
                // delete all records from details table for this product
                // Request to update a product
                // First get the Product information from the POST request
                $prodName=$_POST['prodName'];
                $prodDesc=$_POST['prodDesc'];
                $prodCategory=$_POST['prodCategory'];
                $prodPrice=$_POST['prodPrice'];
                $prodPriceonsell=$_POST['prodPriceonsell'];
                $prodSize=$_POST['prodSize'];
                // get product ID
                $prodID=$_POST['prodID'];
                //check if the img changed
                $updateImgFlag=(empty($_FILES['prodPhoto']['name']))? 0 : 1;
                if($updateImgFlag){
                    
                    // get uploaded Product Image Info
                    $imgName = $_FILES['prodPhoto']['name'];
                    $imgSize = $_FILES['prodPhoto']['size'];
                    $imgTmp = $_FILES['prodPhoto']['tmp_name'];
                    $imgType = $_FILES['prodPhoto']['type'];
                }
                // check if insert Information are valid

                $errors = []; //array to add error messages

                // Check if product name, description, price are empty and if email is valid
                // We know that Product categorya and sizes can't be empty cuz this solved in client side (frontend) 
                // Product colors and price on sell can be empty
                if (empty($prodName)) {    $errors[] = 'حقل الاسم لا يجب أن يكون فارغ';}
                if (empty($prodPrice))     
                {    
                    $errors[] = 'حقل السعر لا يجب أن يكون فارغ';
                }else{
                    if($prodPrice <= 0)  {   $errors[] = 'حقل السعر يجب أن يكون أكبر من الصفر'; }
                }
                
                // check if price > prodPriceonsell, if not add error message
                if($prodPriceonsell!=''){
                    if($prodPriceonsell <= 0){
                        $errors[] = 'حقل السعر بعد الحسم يجب أن يكون أكبر من الصفر';
                    }
                    if($prodPrice <= $prodPriceonsell)  {   $errors[] = 'السعر بعد الحسم يجب أن يكون أقل من السعر الأساسي'; }
                }
                
                if($updateImgFlag){
                    //Image validation
                    $imgAllowedExtention=['jpeg','jpg','png','gif'];

                    //get image extention

                    // $imgExtention = strtolower(end(array(explode('.',$imgName)))); // this is the old way to get file extention, yield an error
                    $imgExtention=pathinfo($imgName,PATHINFO_EXTENSION); // this is the new way much better to get file extention
                    if ( !empty($imgName) && !in_array($imgExtention,$imgAllowedExtention) ) {    
                        $errors[] ='نوع الصورة غير مقبول';
                    }

                }
                // if the Information valid, update the product
                if (empty($errors)) {
                    //flag to check if update done
                    $updateDone=false;
                    if($updateImgFlag){
                        // change uploaded image name & move it from temp path to uploads folder
                        // NOTE: IMPROVE THE CODE: check if image name already exist in uploades folder -> change the name
                        // NOTE: IMPROVE THE CODE: check if uploades folder not exist or have no permissions -> create the folder & change the mode
                        $imgNameFinal= rand(1,1000000) . '_' . $imgName;
                        move_uploaded_file($imgTmp,"uploades\product_images\\" . $imgNameFinal);
                    }
                    //get storeID from the SESSTION 
                    $storeID=$_SESSION['storeID'];
                    // Now Query code to update the product
                    // First, update the basic data into item table item
                    if(!$updateImgFlag){
                        $stmt = $con->prepare('UPDATE item SET
                                                            prodName=?,
                                                            description=?,
                                                            price=?,
                                                            priceOnSale=?,
                                                            storeID=?,
                                                            classID=?
                                                WHERE itemID=?
                                            ');
                        $updateDone=$stmt->execute([$prodName,$prodDesc,$prodPrice,$prodPriceonsell,$storeID,$prodCategory,$prodID]);
                    }else{
                        $stmt = $con->prepare('UPDATE item SET
                                                        prodName=?,
                                                        description=?,
                                                        price=?,
                                                        priceOnSale=?,
                                                        image=?,
                                                        storeID=?,
                                                        classID=?
                                            WHERE itemID=?
                                        ');
                        $updateDone=$stmt->execute([$prodName,$prodDesc,$prodPrice,$prodPriceonsell,$imgNameFinal,$storeID,$prodCategory,$prodID]);
                    }
                    if($updateDone){
                        // Get ID of Updated item
                        // Second, insert Product details into details table after clear the old details
                        // details:the sizes and the colors of each size
                        
                        //clear the old details
                        
                        $stmt=$con->prepare('DELETE FROM details WHERE itemID=?');
                        $stmt->execute([$prodID]);
                        // The array $prodSize is accociatve  array contains sizes and colors
                        // The key indicate the sizes, the value is an array indicate the colors of this size
                        $q='INSERT INTO details(itemID,sizeID,colorID) Values(:itemID,:sizeID,:colorID)';
                        
                        if(!empty($prodSize)){// if there is new details, add them
                            foreach($prodSize as $size => $colors){
                                if((gettype($colors)!=='string')){
                                    foreach($colors as $color){
                                        $stmt=$con->prepare($q);
                                        $stmt->execute([
                                            'itemID' => $prodID,
                                            'sizeID' => $size,
                                            'colorID' => $color
                                        ]);
                                    }
                                }else{
                                    $stmt=$con->prepare($q);
                                    $stmt->execute([
                                        'itemID' => $prodID,
                                        'sizeID' => $colors,
                                        'colorID' => 35
                                    ]);
                                }
                            }
                        }
                        // Check if the data inserted
                        if($updateDone){
                            // Get the inserted data to show
                            $stmt=$con->prepare('SELECT prodName,description,price,priceOnSale,sizeValue,colorName 
                                                FROM item ,size,color,details
                                                WHERE   item.itemID=?
                                                and     details.itemID=item.itemID
                                                and     details.sizeID=size.sizeID
                                                and     details.colorID=color.colorID
                                                ');
                            $stmt->execute([$prodID]);
                            $rows=$stmt->fetchAll();
                            // Show message that the product added successfully
                            $price=number_format($rows[0]['price'],0,'.',',');
                            $priceOnSale=number_format($rows[0]['priceOnSale'],0,'.',',');
                            echo<<<"message1"
                                <div class="addProd-insertInfo-container ">
                                    <div class="message-container message-box success">
                                        <p>تم تعديل المنتج بنجاح</p>
                                    </div>
                                    <div class="message-container message-box info">
                                        <p>البيانات التي تم ادخالها:</p>
                                        <h2>{$rows[0]['prodName']}</h2>
                                        <p>{$rows[0]['description']}</p>
                                        <p>السعر قبل الحسم $price ل.س</p>
                                        <p>السعر بعد الحسم $priceOnSale ل.س</p>
                                        <table class="styled-table">
                                            <thead>
                                                <tr>
                                                    <th>المقاس</th>
                                                    <th>اللون</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                            message1;
                                        foreach($rows as $row){
                                            echo<<<"row"
                                                <tr>
                                                    <td>{$row['sizeValue']}</td>
                                                    <td>{$row['colorName']}</td>
                                                <tr>
                                            row;
                                        }
                            echo<<<"message2"
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="back-to-add-prod-btn">
                                        <a href="StoreProfile.php?do=Products">عودة لمنتجاتي</a>
                                    </div>
                                </div>
                            message2;
                        }
                    }else{
                        //update did not done idk why
                    }
                }else{
                    // Show Errors Messages And redirect to AddProduct section
                    echo "<div class='message-container message-box error'>";
                    foreach ($errors as $error) {
                    echo "<p>$error</p>";
                    }
                    echo '</div>';
                    echo '<div class="message-container">';
                    echo '<div class=" message-box info" > سيتم تحويلك لصفحة التعديل خلال 4 ثوانٍ</div>';
                    echo '</div>';
                    header('refresh:4 url=StoreProfile.php?do=addProduct');
                    exit();

                }
                break;
            case "deleteStore":
                //Request to Detele a store
                // Get Store ID
                $storeID=$_POST['storeID'];
                // delete its products and its details 
                $stmt=$con->prepare('DELETE FROM details 
                                     WHERE details.itemID IN (SELECT itemID FROM item WHERE item.storeID=?) ');
                $stmt->execute([$storeID]);
                // delete the products
                $stmt=$con->prepare('DELETE FROM item WHERE storeID=?');
                $stmt->execute([$storeID]);
                // delete the store
                $stmt=$con->prepare('DELETE FROM store WHERE storeID=?');
                $stmt->execute([$storeID]);
                if($stmt->rowCount()>0){
                    //show success message that the delete done
                    echo '<div class="message-container message-box success">';
                    echo '<p>تم حذف الحساب</p>';
                    echo '</div>';
                    echo '<div class="message-container">';
                    echo '<div class=" message-box info" > سيتم تحويلك للصفحة الرئيسية خلال 3 ثوانٍ</div>';
                    echo '</div>';
                    header('refresh:3 url=logout.php?do=fromStore');
                    exit();
                }else{
                    header('Location: logout.php?do=fromStore');
                    exit();
                }
                break;
            default:
                // Access not allowed, redireact to index.php
                header('Location: index.php');
                exit();
                break;
        }
    }else{
        // Request Method is not POST
        // Access not allowed, redireact to index.php
        header('Location: index.php');
        exit();
    }