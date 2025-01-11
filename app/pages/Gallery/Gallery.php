<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="wishlist.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<?php
include_once("../../header.php");
include("../../connect.php");

// Step 1: Query all designs
$query = "
    SELECT designid, design.name AS design_name 
    FROM design
";

$stmt = $pdo->prepare($query);
$stmt->execute();

// Step 2: Start the carousel
$html = '
<header class="text-white text-center py-5 masthead " id="gallery">
    <div class="container px-4 px-lg-5 d-flex align-items-center justify-content-center">
        <div class="d-flex justify-content-center"> 
            <div class="text-center">
                <b class="display-4 mx-auto t1 my-0 text-uppercase">Explica otra dimensión</b>
                <h2 class="text-white-50 mx-auto mt-4 mb-6">La mejor solucion para el desarrollo web</h2>
            </div>
        </div>
    </div>
</header>
<body>
<section>
    <div class="text-white text-center masthead3">
    
    <div class="container-xl text-center justify-content-center px-6 align-items-center align-content-center flex-fill container-gallery">
            <div id="myCarousel" class="carousel carousel-dark slide px-sm-4 px-md-6 px-lg-4" data-bs-ride="carousel">
                <div class="carousel-inner">
                ';

if ($stmt->rowCount() > 0) {
    $isFirstItem = true;

    // Step 3: Loop through each design
    while ($designRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $designId = $designRow['designid'];

        // Query for images associated with the current design
        $imageQuery = "
            SELECT images.image_path, design.size, design.edition, design.category, design.description 
            FROM images 
            JOIN design ON design.designid = images.designid 
            WHERE design.designid = :designId
        ";

        $imageStmt = $pdo->prepare($imageQuery);
        $imageStmt->bindParam(':designId', $designId, PDO::PARAM_INT);
        $imageStmt->execute();

        // Check if there are images for the current design
        if ($imageStmt->rowCount() > 0) {
            while ($imageRow = $imageStmt->fetch(PDO::FETCH_ASSOC)) {
                
                     
                $html .= '
                <div class="carousel-item ' . ($isFirstItem ? 'active' : '') . ' mb-5">
                    <div class="card2 text-center mb-4 h-100">
                        <div class="card-body">
                         <section class="d-xs-none d-md-block ">
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <div class="col-4 "></div>   
                            <div class="col-xs-1 col-sm-  align-content-center text-center align-items-center">
                               <h1 class="td-text my-3 mx-auto ">' . htmlspecialchars($designRow['design_name']) . '</h1>
                            </div>
                            <div class="col-4 "></div>   
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 g-4">

                            <div class="col col-md-6">
                              <h1><img src="' . htmlspecialchars($imageRow['image_path']) . '" id="designimage" class="img-fluid"></h1>
                            <div class="row rows-col-3 details">
                             <div class="col td-text text-center align-items-center   mx-auto w-100 align-content-center" id="size"><p>Tamaño: ' . htmlspecialchars($imageRow['size']) . '</p></div>
                             <div class="col td-text text-center align-items-center  mx-auto w-100 align-content-center" id="edition"><p>Edición: ' . htmlspecialchars($imageRow['edition']) . '</p></div>
                            <div class="col-2 td-text text-center align-items-center   mx-auto w-100 align-content-center" id="category"><p>Categoría: ' . htmlspecialchars($imageRow['category']) . '</p></div>
                          
                            </div>
                              </div>   
                            <div class="col-xs-12 col-sm-12 col-md-6 align-content-center text-center align-items-center">
                                  <div class="row td-text mx-4 text-center align-items-center my-3 mx-auto w-100 align-content-center">
                            <div class="td-text mx-4 text-center align-items-center my-3 mx-auto  align-content-center desc" id="description">' . htmlspecialchars($imageRow['description']) . '</div>
                            
                                        </div>
                                    <div>
                                    <button class="btn btn-primary rounded-5 wishlist-button align-content-right align-items-right" 
                                    data-designid="' . htmlspecialchars($designRow['designid']) . '" >Add to Wishlist</button>
                                    </div>
                            </div>
                            
                        </div>

                    </section>    
                         
                        </div>
                    </div>
                </div>';
                $isFirstItem = false; // Set to false after the first item
            }
        }
    }
} else {
    $html .= "No results found.";
}



echo $html; // Output all at once
?>
<section>
    <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
    </div>
    </div>
</div> 
</section>

<section class="about-section text-center" id="About">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="text-white mb-4">Innovando el arte contemporáneo</h2>
                        <p class="text-white-50">
                           Implementando un aprendizaje optimo de eficiencia destacable.
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
                                <div class="small text-black-50"><a href="#!">leo.aparedesb@gmail.com</a></div>
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

<form id="logoutForm" action="logout.php" method="POST" style="display: none;">
        <input type="hidden" name="logout" value="1">
    </form>

<footer class=" <?php echo isset($_SESSION["id"]) ? 'bg-success' : 'bg-info'; ?> text-center py-4">
    <div class="container   text-center text-white-50">
        <p>&copy; <?php echo date("Y"); ?> My Landing Page. All rights reserved.</p>
    </div>
</footer>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>

</html>