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
    

    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
   
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="mainNav">
            <div class="container">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse my-3 " id="navbarSupportedContent">
                <a class="navbar-brand" href="/../../index.php">Silver Web System</a>    
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    
                   
                

                    <div class="align-content-end">
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
                            <button  class='btn btn-catalog' role='button'><a href='/../../app/pages/crud/Designs.php' >Catalog</a></button>
                            </li>

                            
                            <li class='nav-item'>
                            <a type='button' class='btn btn-secondary' href='/../app/pages/Gallery/Gallery.php' role='button'>
                                Gallery
                            </a>
                            </li>

                            <li class='nav-item'>
                            <form id='logoutForm' action='/../../app/logout.php' method='POST' style='display: inline;'>
                            <button type='submit' class='btn btn-info' id='SignoutBtn' role='button'>Signout</button>
                            </form>
                            </li>
                            
                            ";
                         } else {
                            echo "<li class='nav-item'><button type='button' class='btn btn-primary'  id='loginBtn' role='button'>Log in</button></li>";
                        }                    ?>    
                    </ul>
</div>
                </div>
            </div>
        </nav>
    </head>