<?php 
session_start();

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silver Web System</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/../../app/styles.css">
    
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="mainNav">
            <div class="container">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <a class="navbar-brand" href="../../../index.php">Silver Web System</a>    
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    
                   
                <li class='nav-item'>
                    <a class='nav-link' href="#About"><h4>About</h4></a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href="#Contact"><h4>Contact</h4></a>
                </li>
                        <?php
                        if(isset($_SESSION["id"])){
                            echo "
                           
                            <li class='nav-item'>
                            <button type='button' class='btn btn-tertiary' href='#Gallery' role='button'>Gallery</button>
                            </li>
                            
                            <li class='nav-item'>
                            <button type='button' class='btn btn-secondary'  role='button'><a href='Gallery.php' >Projects</a></button>
                            </li>

                            <li class='nav-item'>
                            <form id='logoutForm' action='../../logout.php' method='POST' style='display: inline;'>
                            <button type='submit' class='btn btn-info' id='SignoutBtn' role='button'>Signout</button>
                            </form>
                            </li>
                            
                            ";
                         } else {
                            echo "<li class='nav-item'><a class='btn btn-primary' href='#' id='loginBtn' role='button'>Login</a></li>";
                    
                        }                    ?>    
                    </ul>
                </div>
            </div>
        </nav>
    </head>