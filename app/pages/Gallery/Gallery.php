<!DOCTYPE html>
<html lang="en">

<?php
    include_once("headerinside.php");
   
?>
<header class="text-white text-center py-5 masthead">
       <div class="container px-4 px-lg-5 d-flex h-100 align-items-center justify-content-center">
       <div class="d-flex justify-content-center"> 
       <div class="text-center" >
                        <h1 class=" display-4 mx-auto my-0 text-uppercase">Una cultura de reflexion</h1>
                        <h2 class="text-white-50 mx-auto mt-4 mb-6">La mejor solucion para el desarrollo web</h2>
                        <a class="btn btn-primary" href="#about">Get Started</a>
                    </div>
        </div>
    </div>

</header>

<section>
<div class="text-white text-center py-5 masthead2">
       <div class="container px-4 px-lg-5 d-flex h-200 align-items-left justify-content-left">
       <div class="d-flex justify-content-center"> 
       <div class="text-center" >
       <div class="container  px-6 px-lg-4 container-gallery">
                
       <div class="col-md-12 mb-5 mt-5 mb-md-0 ">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                
                            <table class="table table-bordered w-100 h-100">
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
                <th>Column 3</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="2">Row 1 & 2, Cell 1</td>
                <td>Row 1, Cell 2</td>
                <td>Row 1, Cell 3</td>
            </tr>
            <tr>
                
                <td>Row 2, Cell 2</td>
                <td>Row 2, Cell 3</td>
            </tr>
            <tr>
                <td>Row 3, Cell 1</td>
                <td>Row 3, Cell 2</td>
                <td>Row 3, Cell 3</td>
            </tr>
            <tr>
                <td>Row 4, Cell 1</td>
                <td>Row 4, Cell 2</td>
                <td>Row 4, Cell 3</td>
            </tr>
        </tbody>
    </table>
                            
                            <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Address</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">3111 Rio Zacatula, Mexicali BC</div>
                            </div>
                      </div>
                    </div>
                    

                    <div class="col-md-12 mb-5 mt-5 mb-md-0 ">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Address</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">3111 Rio Zacatula, Mexicali BC</div>
                            </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<section class="about-section text-center" id="About">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="text-white mb-4">Built with Bootstrap 5</h2>
                        <p class="text-white-50">
                           Implementando un aprendizaje eficiente.
                        </p>
                    </div>
                </div>
            </div>
</section>

        <section id="Contact" class="contact-section <?php echo isset($_SESSION["id"]) ? 'bg-success' : 'bg-info'; ?> "
                 >
            <div class="container    px-6 px-lg-4">
                <div class="row gx-2 gx-lg-5">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Address</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">3111 Rio Zacatula, Mexicali BC</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Email</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50"><a href="#!">leoaparedesb@gmail.com</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Phone</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">+52 (686) 225-6637</div>
                            </div>
                        </div>
                    </div> 
                </div>

                <div class="row gx-2 gx-lg-5">
                    <div class="col-md-12 mb-3 py-5 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center " >
                            
                                <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Address</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">3111 Rio Zacatula, Mexicali BC</div>
                         
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>    
<!-- Overlay for the login form -->
<div class="overlay" id="overlay"></div>

<!-- Login Form -->
<div class="Signup-form" id="SignupForm">
    <h3>Signup</h3>
    <form action="app/register.php" method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username"class="form-control" id="username" required>
        </div>
        <div class="mb-3">
            <label for="password"  class="form-label">Password</label>
            <input type="password" name="password" class="form-control" id="password" required>
        </div>
        <div class="mb-3">
            <label for="passwordrpt"  class="form-label">Repeat Password</label>
            <input type="password" name="passwordrpt" class="form-control" id="password" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary" >Submit</button>
        
    </form>
    <p class="mt-3">Already an user? <a href="#" name="switch" id="registerLink">Click here</a></p>
    <?php
    if(isset($_GET["error"])){
        if($_GET["error"]== "emptyInputSignUp"){
            echo "<p>Fill in all fields!<p>";
        }
    }
    
    if(isset($_GET["error"])){
        if($_GET["error"]== "passworddontmatch"){
            echo "<p>Match the passswords!<p>";
        }
    }
    if(isset($_GET["error"])){
        if($_GET["error"]== "UserAlredyExists"){
            echo "<p>Select another user!<p>";
        }
    }
    ?>
</div>

<div class="Login-form"  id="LoginForm">
    <h3>Login</h3>
    <form action="app/login.php" method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username"class="form-control" id="username" required>
        </div>
        <div class="mb-3">
            <label for="password"  class="form-label">Password</label>
            <input type="password" name="password" class="form-control" id="password" required>
        </div>
       
        <button type="submit" name="submit" class="btn btn-primary" >Submit</button>
         
    </form>
    <p class="mt-8 mb-6">Don't have an account?<a href="#"  id="registerLink2">Click here</a></p>
    <?php
    if(isset($_GET["error"])){
        if($_GET["error"]== "emptyInputLogin"){
            echo "<p>Fill in all fields!<p>";
        }
    }
    
    if(isset($_GET["error"])){
        if($_GET["error"]== "wronglogin"){
            echo "<p>Match the passswords!<p>";
        }
    }
   
    ?>
</div>

<form id="logoutForm" action="logout.php" method="POST" style="display: none;">
        <input type="hidden" name="logout" value="1">
    </form>

<footer class=" <?php echo isset($_SESSION["id"]) ? 'bg-success' : 'bg-info'; ?> text-center py-4">
    <div class="container   text-center text-white-50">
        <p>&copy; <?php echo date("Y"); ?> My Landing Page. All rights reserved.</p>
    </div>
</footer>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../scripts.js"></script>

</body>

</html>