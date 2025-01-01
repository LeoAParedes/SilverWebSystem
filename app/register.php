<?php 

include 'connect.php';
include 'functions.php';
try {
if(isset($_POST['submit'])){
    echo "it works";
    $username=$_POST['username'];
    $password=$_POST['password'];
    $passwordrpt=$_POST['passwordrpt'];
    $password=password_hash($password, PASSWORD_DEFAULT);
    $checkuser = $pdo->prepare("SELECT * FROM users WHERE username = :username;");
    $checkuser->bindParam(':username', $username);
    $checkuser->execute();

    // Check if the user already exists
    if ($checkuser->rowCount() > 0) {
        header("Location: ../index.php?error=UserAlredyExists");
    } else {
        // Prepare the SQL statement to insert a new user
        $Query = $pdo->prepare("INSERT INTO users (username, password, created_at) VALUES (:username, :password, NOW())");
        $Query->bindParam(':username', $username);
        $Query->bindParam(':password', $password);
        // Execute the insert query
        if ($Query->execute()) {
            
            header("Location: ../index.php");
            exit(); // Always exit after a header redirect
        } else {
            echo "Error: " . implode(", ", $Query->errorInfo()); // Display error message
        }
    }

    if(emptyInputSignUp($username,$password, $passwordrpt)!==false) {
        header("location: ../index.php?error=emptyInputSignUp") ;
    }   
    if(pwdMatch($password,$passwordrpt)!==false) {
        header("location: ../index.php?error=passworddontmatch") ;     
    }
    
}else  header("location: ../index.php?error=invalid") ;
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage(); // Handle any database errors
}


