<?php
function loginUser($pdo, $username, $password) {
    $usernameExists = usernameExists($pdo, $username);

    if ($usernameExists === false) {
        header("location: login.php?error=usernotfound");
        exit();
    }

    $passwordhashed = $usernameExists["password"];
    $checkpassword = password_verify($password, $passwordhashed);

    if ($checkpassword === false) {
        header("location: ../index.php?error=wronglogin");
        exit();
    } else {
        session_start();
        $_SESSION["id"] = $usernameExists["id"]; // Assuming 'id' is the primary key
        header("Location: ../index.php");
        exit();
    }
}
function usernameExists($pdo, $username) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    // Check if the username exists
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch(PDO::FETCH_ASSOC); // Return user data as an associative array
    } else {
        return false; // Username does not exist
    }
}  
function logoutUser($pdo, $username) {
    
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../index.php"); // Redirect to the index page
    exit();
}
function emptyInputSignUp($username,$password,$passwordrpt) {
$result;
if(empty($username) || empty($password) || empty($passwordrpt)) {
    $result= true;
}else {
    $result=false;
}

 
function pwdMatch($password,$passwordrpt) {
    $result;
    if(empty($username) || empty($password) || empty($passwordrpt)) {
        $result= true;
    }else {
        $result=false;
    }
}
    

}
