<?php
session_start();
$isLoggedIn = isset($_SESSION["id"]);
$primaryColor = $isLoggedIn ? 'forest-green' : 'black-olive';
?>




<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SilverWebSystem - Diseño Web con Proporciones Áureas en Mexicali, BC">
    <meta name="author" content="Leonardo Antonio Paredes Bobadilla">
    <title>SilverWebSystem | Arte bordado </title>
    
    <!-- Forest Green Design System CSS -->
    <link href="app/assets/css/output.css" rel="stylesheet">
    
    <!-- Font Awesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<body>

<?php include 'app/assets/navmenu/navmenu.php'; ?>


    <header id="home" class="masthead">



        <!-- Fondo Animado con Espiral Áurea -->
        <div class="absolute inset-0 opacity-10">
            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1618 1000">
                <path d="M 0,500 Q 309,500 309,309 T 500,309 Q 500,191 405,191 T 309,191 Q 309,236 332,236 T 355,236" 
                      stroke="white" stroke-width="2" fill="none" 
                      stroke-dasharray="3000" stroke-dashoffset="3000">
                    <animate attributeName="stroke-dashoffset" to="0" dur="6.18s" repeatCount="indefinite"/>
                </path>
            </svg>
        </div>
        
        <!-- Contenido Hero -->
        <div class="golden-container relative z-10  text-center animate-golden-fade">

<h1 class="">
                Silver Web System
            </h1>
            <h2 class="text-white opacity-80 text-xl md:text-2xl lg:text-3xl font-light mb-6 max-w-3xl mx-auto">
                The best solution for the technology behind your business.
            </h2>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#About" class="btn-golden rounded-full text-lg">
                    <span class="relative z-10">Know what's behind</span>
                </a>
                <a href="#Contact" class="btn btn-secondary rounded-full text-white">
                    Contact
                </a>
            </div>
        </div>


        <!-- Indicador de Scroll -->
        <div class="scroll-indicator">
            <svg class="w-full h-full" style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </header>
    <!-- About Section -->
    <section id="About" class="py-6 bg-black-olive-200 text-center">
        <div class="golden-container">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-white text-4xl md:text-5xl font-bold mb-4">Built with consciousness</h2>
                <p class="text-white opacity-80 text-lg md:text-xl leading-relaxed">
                    Implementando un aprendizaje eficiente, 
                    <br>con las proporciones matemáticas 
                    perfectas de la naturaleza.
                    <br> Cada elemento está diseñado siguiendo las proporciones doradas y el razonamiento lógico 
                </p>
            </div>
        </div>
    </section>

	<section>
<?php
require_once "gallery-component.php";
?>

</section>	


    
    <!-- Contact Section con Grid Dorado -->
    <section id="Contact" class="py-6 <?php echo $isLoggedIn ? 'bg-forest-green' : 'bg-black-olive'; ?>">
        <div class="golden-container">
            
            <!-- Grid de 3 Columnas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                
                <!-- Tarjeta de Dirección -->
                <div class="golden-card contact-card">
                    <div class="relative z-10 text-center">
                        <div class="mx-auto mb-4 rounded-full <?php echo $isLoggedIn ? 'bg-forest-green' : 'bg-black-olive'; ?> flex items-center justify-center" style="width: 5rem; height: 5rem;">
                            <i class="fas fa-map-marked-alt text-white text-3xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold uppercase tracking-wider mb-2 text-black-olive">Address</h4>
                        <hr class="mx-auto mb-4 <?php echo $isLoggedIn ? 'bg-forest-green-600' : 'bg-black-olive-600'; ?>" style="width: 5rem; height: 0.25rem;" />
                        <div class="text-black-olive-600 text-lg">
                            2401 Portico Blvd. Suite#6
				Calexico CA 92231
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Email -->
                <div class="golden-card contact-card">
                    <div class="relative z-10 text-center">
                        <div class="mx-auto mb-4 rounded-full <?php echo $isLoggedIn ? 'bg-forest-green' : 'bg-black-olive'; ?> flex items-center justify-center" style="width: 5rem; height: 5rem;">
                            <i class="fas fa-envelope text-white text-3xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold uppercase tracking-wider mb-2 text-black-olive">Email</h4>
                        <hr class="mx-auto mb-4 <?php echo $isLoggedIn ? 'bg-forest-green-600' : 'bg-black-olive-600'; ?>" style="width: 5rem; height: 0.25rem;" />
                        <a href="mailto:leoaparedesb@gmail.com" class="text-<?php echo $primaryColor; ?> hover:text-<?php echo $primaryColor; ?>-600 text-lg transition-colors duration-382">
                            leoaparedesb@gmail.com
                        </a>
                    </div>
                </div>
                
                <!-- Tarjeta de Teléfono -->
                <div class="golden-card contact-card">
                    <div class="relative z-10 text-center">
                        <div class="mx-auto mb-4 rounded-full <?php echo $isLoggedIn ? 'bg-forest-green' : 'bg-black-olive'; ?> flex items-center justify-center" style="width: 5rem; height: 5rem;">
                            <i class="fas fa-mobile-alt text-white text-3xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold uppercase tracking-wider mb-2 text-black-olive">Phone</h4>
                        <hr class="mx-auto mb-4 <?php echo $isLoggedIn ? 'bg-forest-green-600' : 'bg-black-olive-600'; ?>" style="width: 5rem; height: 0.25rem;" />
                        <div class="text-black-olive-600 text-lg">
                            +52 (686) 225-6637
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="app/assets/js/scripts.js"></script>
	<script src="app/assets/js/main.js"></script>

<script>
// Handle direct links to login/signup
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    
    if (hash === '#login') {
        openLoginModal();
    } else if (hash === '#signup') {
        // Open signup modal
        document.getElementById('overlay')?.classList.remove('hidden');
        document.getElementById('LoginForm')?.classList.add('hidden');
        document.getElementById('SignupForm')?.classList.remove('hidden');
    }
    
    // Clear hash
    if (hash === '#login' || hash === '#signup') {
        history.replaceState(null, null, ' ');
    }
});
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ESGX5LYM25"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ESGX5LYM25');
</script>
</body>
</html>



