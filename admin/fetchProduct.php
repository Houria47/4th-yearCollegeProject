<?php
SESSION_START();
require 'connect.php';
include 'includes/functions/functions.php';
if($_SERVER['REQUEST_METHOD']=='GET'){
    // if the request to get products
    if($_GET['do']=='getProducts' && isset($_GET['kind'])){
        echo getProductCards($con,$_GET['kind']);
    }elseif($_GET['do']=='likeProduct'){// else request to handel like action
        $res=[];
        //check if there is a user logged in
        if(isset($_SESSION['userID']) && !empty($_SESSION['userID'])){
            //there is a user in the session, check if it's valid then do the requested action
            //get user ID
            $userID=$_SESSION['userID'];
            // check if userID exist in DB
            $stmt=$con->prepare('SELECT userID FROM user WHERE userID =?');
            $stmt->execute([$userID]);
            if($stmt->rowCount()>0){
                // get action
                $action=$_GET['action'];
                //get productID
                $prodID=$_GET['prodID'];
                if($action=='add'){
                    //like the product by adding record to favorit
                    //check if it exists already in favorite table
                    $stmt=$con->prepare('SELECT favoritID FROM favorite WHERE itemID=? AND userID=?');
                    $stmt->execute([$prodID,$userID]);
                    if($stmt->rowCount()>0){
                        //get the ID of the recorde to update it
                        $row=$stmt->fetch();
                        $favRecoreID=$row['favoritID'];
                        //update the recorde
                        $stmt=$con->prepare('UPDATE favorite SET isLiked=? WHERE favoritID=?');
                        $stmt->execute([1,$favRecoreID]);// 1 cuz we want like this prod 
                        if($stmt->rowCount()>0){
                            // the request done, return true
                            $res=[
                                "success" => true,
                                "message" => "تمت إضافة الإعجاب للمنتج"
                            ];
                        }else{//else, idk what could cause the query to fail     
                            $res=[
                                "message" => "idk what could cause the query to fail line: 44"
                            ];
                        }
                    }else{
                        //there is no recored with this info in favorite table, add new one
                        $stmt=$con->prepare('INSERT INTO favorite(itemID,userID,isLiked) 
                                                    VALUES (:prodID, :userID, :liked)');
                        $stmt->execute([
                                        'prodID' => $prodID,
                                        'userID' => $userID,
                                        'liked' => 1,
                                       ]);
                        if($stmt->rowCount()>0){
                            // the request done, return true
                            $res=[
                                "success" => true,
                                "message" => "تمت إضافة الإعجاب للمنتج"
                            ];
                        }else{//else, idk what could cause the query to fail     
                            $res=[
                                "message" => "idk what could cause the query to fail line: 64"
                            ];
                        }
                    }
                }elseif($action =='remove'){
                    //of course there is a record in favorite table so just update it
                    $stmt=$con->prepare('UPDATE favorite SET isLiked=? WHERE itemID=? AND userID=?');
                    $stmt->execute([0,$prodID,$userID]);// 0 cuz we want remove the like of this prod
                    if($stmt->rowCount()>0){
                        
                        // the request done, return true
                        $res=[
                            "success" => true,
                            "message" => "تمت إزالة الإعجاب من المنتج"
                        ];
                        //check if the recorde become useless
                        $stmt=$con->prepare("SELECT favoritID, isFavorit FROM favorite WHERE itemID=? AND userID=?");
                        $stmt->execute([$prodID,$userID]);
                        $row=$stmt->fetch();
                        if($row['isFavorit']==0){
                            //Delete this recorde
                            $stmt=$con->prepare("DELETE FROM favorite WHERE favoritID=?");
                            $stmt->execute([$row['favoritID']]);
                        }
                    }//else, idk what could cause the query to fail
                }//else unvalid action, do nothing
            }else{
                //there is no user with this ID, return info message 
                $res=[
                    "success" => false,
                    "message" => "يجب أن تسجل دخول للموقع لتتمكن من تسجيل إعجابك بالمنتجات"
                ];
            }
        }else{
            //there is no user in the session, return info message 
            $res=[
                "success" => false,
                "message" => "يجب أن تسجل دخول للموقع لتتمكن من تسجيل إعجابك بالمنتجات"
            ];
        }
        //return the result $res by printing the json code of it
        echo (json_encode($res));
    }elseif($_GET['do']=='favProduct'){// else request to handel favorit action
        $res=[];
        //check if there is a user logged in
        if(isset($_SESSION['userID']) && !empty($_SESSION['userID'])){
            //there is a user in the session, check if it's valid then do the requested action
            //get user ID
            $userID=$_SESSION['userID'];
            // check if userID exist in DB
            $stmt=$con->prepare('SELECT userID FROM user WHERE userID =?');
            $stmt->execute([$userID]);
            if($stmt->rowCount()>0){
                // get action
                $action=$_GET['action'];
                //get productID
                $prodID=$_GET['prodID'];
                if($action=='add'){
                    //add the product to favorit
                    //check if it exists already in favorite table
                    $stmt=$con->prepare('SELECT favoritID  FROM favorite WHERE itemID=? AND userID=?');
                    $stmt->execute([$prodID,$userID]);
                    if($stmt->rowCount()>0){
                        //get the ID of the recorde to update it
                        $row=$stmt->fetch();
                        $favRecoreID=$row['favoritID'];
                        //update the recorde
                        $stmt=$con->prepare('UPDATE favorite SET isFavorit=? WHERE favoritID=?');
                        $stmt->execute([1,$favRecoreID]);// 1 cuz we want to make this prod favored
                        if($stmt->rowCount()>0){
                            // the request done, return true
                            $res=[
                                "success" => true,
                                "message" => "تمت إضافة المنتج للمفضلة"
                            ];
                        }//else, idk what could cause the query to fail
                    }else{
                        //there is no recored with this info in favorite table, add new one
                        $stmt=$con->prepare('INSERT INTO favorite(itemID,userID,isFavorit) 
                                                    VALUES (:prodID, :userID, :fav)');
                        $stmt->execute([
                                        'prodID' => $prodID,
                                        'userID' => $userID,
                                        'fav' => 1,
                                       ]);
                        if($stmt->rowCount()>0){
                            // the request done, return true
                            $res=[
                                "success" => true,
                                "message" => "تمت إضافة المنتج للمفضلة"
                            ];
                        }//else, idk what could cause the query to fail     
                    }
                }elseif($action =='remove'){
                    //of course there is a record in favorite table so just update it
                    $stmt=$con->prepare('UPDATE favorite SET isFavorit=? WHERE itemID=? AND userID=?');
                    $stmt->execute([0,$prodID,$userID]);// 0 cuz we want to make this prod unfavored
                    if($stmt->rowCount()>0){
                        // the request done, return true
                        $res=[
                            "success" => true,
                            "message" => "تمت إزالة المنتج من المفضلة"
                        ];
                        //check if the recorde become useless
                        $stmt=$con->prepare("SELECT favoritID, isLiked FROM favorite WHERE itemID=? AND userID=?");
                        $stmt->execute([$prodID,$userID]);
                        $row=$stmt->fetch();
                        if($row['isLiked']==0){
                            //Delete this recorde
                            $stmt=$con->prepare("DELETE FROM favorite WHERE favoritID=?");
                            $stmt->execute([$row['favoritID']]);
                        }
                    }//else, idk what could cause the query to fail
                }//else unvalid action, do nothing
            }else{
                //there is no user with this ID, return info message 
                $res=[
                    "success" => false,
                    "message" => "يجب أن تسجل دخول للموقع لتتمكن من إضافة منتجات للمفضلة"
                ];
            }
        }else{
            //there is no user in the session, return info message 
            $res=[
                "success" => false,
                "message" => "يجب أن تسجل دخول للموقع لتتمكن من إضافة منتجات للمفضلة"
            ];
        }
        //return the result $res by printing the json code of it
        echo (json_encode($res));
    }
}
