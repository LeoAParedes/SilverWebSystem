<?php
 require("connect.php");
 require ("functions.php");
if(isset($_POST["submit"]) ) {

  try{
    $username=trim($_POST["username"]);
    $password=trim($_POST["password"]);
   loginUser($pdo, $username,$password);

    if(emptyInputLogin($username,$password)!==false) {
        header("location: ../index.php?error=emptyInputLogIn");
        exit();
    
  }else  header("location: index.php?error=none");
        

}    catch (PDOException $e) {
    echo "Database error: " . $e->getMessage(); // Handle any database errors
  }
 
}