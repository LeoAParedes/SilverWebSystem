<?php
// /app/assets/navmenu/navmenu.php - INCLUDE VERSION (not standalone)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['id']);
$username = $_SESSION['username'] ?? 'Guest';
$currentPage = basename($_SERVER['PHP_SELF']);

$scriptPath = $_SERVER['SCRIPT_NAME'];
$pathDepth = substr_count($scriptPath, '/') - 1; // Count folder depth

if (strpos($scriptPath, '/app/pages/crud/') !== false) {
    // Inside /app/pages/crud/
    $toRoot = '../../../';
    $toApp = '../../';
    $toAssets = '../../assets/';
    $toCrud = '';
    $toPages = '../';
    $toOrderProcessing = '../orderprocessing/';
    
} elseif (strpos($scriptPath, '/app/pages/orderprocessing/') !== false) {
    // Inside /app/pages/orderprocessing/  ← ADD THIS NEW CONDITION
    $toRoot = '../../../';
    $toApp = '../../';
    $toAssets = '../../assets/';
    $toCrud = '../crud/';
    $toPages = '../';
    $toOrderProcessing = '';  // We're already in orderprocessing
    
} elseif (strpos($scriptPath, '/app/pages/') !== false && substr_count($scriptPath, '/', 1) > 3) {
    // Inside any other subdirectory of /app/pages/  ← GENERIC SUBDIRECTORY HANDLER
    $toRoot = '../../../';
    $toApp = '../../';
    $toAssets = '../../assets/';
    $toCrud = '../crud/';
    $toPages = '../';
    $toOrderProcessing = '../orderprocessing/';
    
} elseif (strpos($scriptPath, '/app/pages/') !== false) {
    // Inside /app/pages/ (direct files only)
    $toRoot = '../../';
    $toApp = '../';
    $toAssets = '../assets/';
    $toCrud = 'crud/';
    $toPages = '';
    $toOrderProcessing = 'orderprocessing/';
    
} elseif (strpos($scriptPath, '/app/') !== false) {
    // Inside /app/
    $toRoot = '../';
    $toApp = '';
    $toAssets = 'assets/';
    $toCrud = 'pages/crud/';
    $toPages = 'pages/';
    $toOrderProcessing = 'pages/orderprocessing/';
    
} else {
    // At root
    $toRoot = '';
    $toApp = 'app/';
    $toAssets = 'app/assets/';
    $toCrud = 'app/pages/crud/';
    $toPages = 'app/pages/';
    $toOrderProcessing = 'app/pages/orderprocessing/';
}


?>

<!-- Navigation Styles (only CSS, no head tags) -->
<style>
/* Forest Green Navigation */
.header-forest {
    background: linear-gradient(135deg, #34403a 0%, #34403a 100%);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    min-height: 70px;
    padding: 0.618rem 0;
    box-shadow: 0 2px 20px rgba(52, 64, 58, 0.15);
}

.nav-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo-forest {
    display: flex;
    align-items: center;
    gap: 0.618rem;
    text-decoration: none;
}

.logo-img {
    height: 40px;
    width: auto;
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: #18ff6d;
}

.navmenu-forest {
    display: flex;
    align-items: center;
}

.nav-list {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 1rem;
}

.nav-link-forest {
    padding: 0.5rem 1rem;
    color: #e4f2e9;
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.382s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-link-forest h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.nav-link-forest:hover {
    background: rgba(24, 255, 109, 0.1);
    color: #18ff6d;
}

.nav-link-forest.active {
    background: rgba(19, 138, 54, 0.2);
    color: #18ff6d;
}

.btn-forest-primary {
    background: linear-gradient(135deg, #138a36 0%, #138a36 100%);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.382s ease;
}

.btn-forest-primary:hover {
    background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(19, 138, 54, 0.3);
}

.btn-forest-user {
    background: rgba(180, 208, 191, 0.2);
    color: #e4f2e9;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    border: 1px solid rgba(180, 208, 191, 0.3);
    font-weight: 600;
    cursor: pointer;
}

.dropdown-menu-forest {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 40px rgba(52, 64, 58, 0.2);
    min-width: 200px;
    z-index: 1040;
}

.dropdown-item-forest {
    display: block;
    padding: 0.5rem 1rem;
    color: #34403a;
    text-decoration: none;
    transition: all 0.382s ease;
}

.dropdown-item-forest:hover {
    background: #e4f2e9;
    color: #138a36;
}

.mobile-nav-toggle {
    display: none;
}

@media (max-width: 991px) {
    .navmenu-forest {
        display: none;
    }
    
    .mobile-nav-toggle {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        background: transparent;
        border: none;
        cursor: pointer;
    }
    
    .hamburger-forest {
        display: block;
        width: 25px;
        height: 3px;
        background: #18ff6d;
        border-radius: 3px;
        margin: 3px 0;
        transition: all 0.382s ease;
    }
}

.hidden {
    display: none !important;
}

/* Fix body padding for fixed nav */
body {
    padding-top: 70px !important;
}

/* Auth Modal Styles */
.form-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(52, 64, 58, 0.8);
    z-index: 1050;
}

.form-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    max-width: 400px;
    width: 90%;
    z-index: 1051;
}

.heading-golden {
    color: #138a36;
    margin-bottom: 1.5rem;
}

.input-golden {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #d2e3d9;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.input-golden:focus {
    outline: none;
    border-color: #138a36;
}


/* Mobile Navigation Menu - Full Height */
.mobile-navmenu-forest {
    position: fixed;
    top: 0;
    right: -100%;
    width: 320px;
    height: 100vh;
    background: linear-gradient(180deg, #34403a 0%, #2a342f 100%);
    box-shadow: -5px 0 20px rgba(0, 0, 0, 0.4);
    transition: right 0.382s cubic-bezier(0.382, 0, 0.618, 1);
    z-index: 1031;
    display: flex;
    flex-direction: column;
}

.mobile-navmenu-forest.active {
    right: 0;
}

.mobile-menu-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
}

/* Mobile Menu Header */
.mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(24, 255, 109, 0.1);
    background: rgba(0, 0, 0, 0.1);
}

.mobile-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mobile-logo-img {
    height: 35px;
    width: auto;
}

.mobile-logo-text {
    color: #18ff6d;
    font-size: 1.25rem;
    font-weight: 700;
}

.mobile-close-btn {
    background: transparent;
    border: none;
    color: #18ff6d;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.mobile-close-btn:hover {
    transform: rotate(90deg);
    color: #ff6b6b;
}

/* Mobile Menu Content */
.mobile-menu-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem 1rem;
}

.mobile-nav-list {
    list-style: none;
    padding: 0;
    margin: 0 0 2rem 0;
}

.mobile-nav-item {
    margin-bottom: 0.618rem;
}

.mobile-nav-link-forest {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    color: #e4f2e9;
    background: rgba(19, 138, 54, 0.05);
    border-radius: 0.618rem;
    text-decoration: none;
    transition: all 0.382s cubic-bezier(0.382, 0, 0.618, 1);
    border: 1px solid transparent;
    font-weight: 500;
    font-size: 1.1rem;
}

.mobile-nav-link-forest:hover {
    background: linear-gradient(135deg, rgba(19, 138, 54, 0.2) 0%, rgba(24, 255, 109, 0.15) 100%);
    color: #18ff6d;
    transform: translateX(0.618rem);
    border-color: rgba(24, 255, 109, 0.3);
    text-decoration: none;
}

.mobile-nav-link-forest.active {
    background: rgba(19, 138, 54, 0.15);
    color: #18ff6d;
    border-color: rgba(24, 255, 109, 0.5);
}

.mobile-nav-link-forest i {
    font-size: 1.25rem;
    width: 28px;
    text-align: center;
}

/* Mobile User Section */
.mobile-user-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(24, 255, 109, 0.1);
}

.mobile-user-btn {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, rgba(180, 208, 191, 0.1) 0%, rgba(180, 208, 191, 0.05) 100%);
    border: 1px solid rgba(180, 208, 191, 0.3);
    border-radius: 0.618rem;
    color: #e4f2e9;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.382s ease;
}

.mobile-user-btn:hover {
    background: linear-gradient(135deg, rgba(180, 208, 191, 0.2) 0%, rgba(180, 208, 191, 0.1) 100%);
    border-color: #18ff6d;
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.mobile-user-info i {
    font-size: 1.5rem;
    color: #18ff6d;
}

.mobile-chevron {
    transition: transform 0.382s ease;
}

.mobile-user-btn.active .mobile-chevron {
    transform: rotate(180deg);
}

/* Mobile User Dropdown */
.mobile-user-dropdown {
    margin-top: 0.618rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 0.618rem;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.382s ease;
}

.mobile-user-dropdown:not(.hidden) {
    max-height: 400px;
}

.mobile-dropdown-list {
    list-style: none;
    padding: 0.618rem;
    margin: 0;
}

.mobile-dropdown-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #e4f2e9;
    text-decoration: none;
    border-radius: 0.382rem;
    transition: all 0.3s ease;
    background: transparent;
    border: none;
    width: 100%;
    cursor: pointer;
    font-size: 1rem;
}

.mobile-dropdown-link:hover {
    background: rgba(24, 255, 109, 0.1);
    color: #18ff6d;
    transform: translateX(0.382rem);
}

.mobile-dropdown-link.active {
    background: rgba(19, 138, 54, 0.1);
    color: #18ff6d;
}

.mobile-dropdown-link i {
    width: 20px;
    text-align: center;
}

.mobile-dropdown-divider {
    height: 1px;
    background: rgba(24, 255, 109, 0.1);
    margin: 0.618rem 0;
}

.mobile-dropdown-link.logout-btn {
    color: #ff6b6b;
}

.mobile-dropdown-link.logout-btn:hover {
    background: rgba(220, 53, 69, 0.1);
    color: #ff4444;
}

/* Mobile Login Section */
.mobile-login-section {
    margin-top: 2rem;
}

.mobile-login-btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #138a36 0%, #138a36 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.382s ease;
}

.mobile-login-btn:hover {
    background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(19, 138, 54, 0.3);
}

/* Mobile Menu Footer */
.mobile-menu-footer {
    padding: 1rem;
    border-top: 1px solid rgba(24, 255, 109, 0.1);
    background: rgba(0, 0, 0, 0.1);
}

.mobile-copyright {
    text-align: center;
    color: rgba(228, 242, 233, 0.5);
    font-size: 0.875rem;
    margin: 0;
}

/* Mobile Nav Overlay */
.mobile-nav-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    opacity: 0;
    visibility: hidden;
    transition: all 0.382s ease;
    z-index: 1030;
}

.mobile-nav-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Prevent body scroll when menu is open */
body.mobile-nav-active {
    overflow: hidden;
}

/* Forms */
.mobile-logout-form {
    width: 100%;
    margin: 0;
}

/* Hamburger Animation */
.mobile-nav-toggle.active .hamburger-forest:nth-child(1) {
    transform: translateY(9px) rotate(45deg);
}

.mobile-nav-toggle.active .hamburger-forest:nth-child(2) {
    opacity: 0;
}

.mobile-nav-toggle.active .hamburger-forest:nth-child(3) {
    transform: translateY(-9px) rotate(-45deg);
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .mobile-navmenu-forest {
        width: 100%;
    }
}





</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">



<!-- Forest Green Navigation Header -->
<header class="header-forest sticky-top">
    <div class="container">
        <nav class="nav-wrapper">
            
            <!-- Logo/Brand -->
            <a href="<?php echo $toRoot; ?>index.php" class="logo-forest">
                <img src="<?php echo $toAssets; ?>img/LOGO1.1.png" alt="Logo" class="logo-img">
                <span class="logo-text">Silver Web System</span>
            </a>
            
            <!-- Desktop Navigation -->
                <div class="navmenu-forest">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo $toRoot; ?>index.php" class="nav-link-forest <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <h4>Home</h4>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="../../../index.php#About" class="nav-link-forest">
                            <i class="fas fa-info-circle"></i>
                            <h4>About</h4>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="../../../index.php#Contact" class="nav-link-forest">
                            <i class="fas fa-envelope"></i>
                            <h4>Contact</h4>
                        </a>

                    </li>
			<?php 
				$cartCount = 0;
				if(isset($_SESSION['cart'])) {
				    foreach($_SESSION['cart'] as $item) {
				        $cartCount += $item['quantity'];
				    }
				}
				?>
				<a href="<?php echo $toApp; ?>pages/orderprocessing/cart.php" class="btn btn-outline-light position-relative ms-2">
				    <i class="fas fa-shopping-cart"></i>
				    <?php if($cartCount > 0): ?>
				    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
				        <?= $cartCount ?>
				        <span class="visually-hidden">items in cart</span>
				    </span>
				    <?php endif; ?>
				</a>
                    </li>
                    <?php if($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <button class="btn-forest-user" onclick="toggleUserDropdown(event)">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($username); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            
                            <div class="dropdown-menu-forest hidden" id="userDropdownMenu">
                                <a href="<?php echo $toApp; ?>profile.php" class="dropdown-item-forest">
                                    <i class="fas fa-user"></i>
                                    Profile
                                </a>
                                <a href="<?php echo $toApp; ?>settings.php" class="dropdown-item-forest">
                                    <i class="fas fa-cog"></i>
                                    Settings
                                </a>
                                <?php if(isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
				    <a href="<?php echo $toApp; ?>pages/crud/Designs.php" class="dropdown-item-forest">
				        <i class="fas fa-palette"></i>
				        Designs
				    </a>
				<?php endif; ?> 
				
                                <div style="height: 1px; background: #e4f2e9; margin: 0.5rem 0;"></div>
                                <form method="POST" action="<?php echo $toAssets; ?>navmenu/logout.php">
                                    <button type="submit" class="dropdown-item-forest" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <button class="btn-forest-primary" onclick="openLoginModal()">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Mobile Toggle -->
           <button class="mobile-nav-toggle" id="hamburgerMenu" onclick="toggleMobileMenu()" aria-label="Toggle navigation">
    <span class="hamburger-forest"></span>
    <span class="hamburger-forest"></span>
    <span class="hamburger-forest"></span>
</button>
        </nav>
    </div>
</header>
<div class="mobile-navmenu-forest hidden" id="mobileMenu">
    <div class="mobile-menu-container">
        <!-- Mobile Menu Header -->
        <div class="mobile-menu-header">
            <div class="mobile-logo">
                <img src="<?php echo $toAssets; ?>img/LOGO1.1.png" alt="Logo" class="mobile-logo-img">
                <span class="mobile-logo-text">Silver</span>
            </div>
            <button class="mobile-close-btn" onclick="closeMobileMenu()" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Mobile Menu Content -->
        <div class="mobile-menu-content">
            <!-- Main Navigation -->
            <ul class="mobile-nav-list">
                <li class="mobile-nav-item">
                    <a href="<?php echo $toRoot; ?>index.php" class="mobile-nav-link-forest <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                
                <li class="mobile-nav-item">
                    <a href="<?php echo $toRoot; ?>index.php#Contact" class="mobile-nav-link-forest">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
		<?php 
		$cartCount = 0;
		if(isset($_SESSION['cart'])) {
		    foreach($_SESSION['cart'] as $item) {
		        $cartCount += $item['quantity'];
		    }
		}
		?>
		<a href="<?php echo $toApp; ?>pages/orderprocessing/cart.php" class="btn btn-outline-light position-relative ms-2">
		    <i class="fas fa-shopping-cart"></i>
		    <?php if($cartCount > 0): ?>
		    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
		        <?= $cartCount ?>
		        <span class="visually-hidden">items in cart</span>
		    </span>
		    <?php endif; ?>
		</a>


            </ul>




            <?php if($isLoggedIn): ?>
                <!-- User Section with Dropdown -->
                <div class="mobile-user-section">
                    <button class="mobile-user-btn" onclick="toggleMobileUserDropdown(event)">
                        <div class="mobile-user-info">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($username); ?></span>
                        </div>
                        <i class="fas fa-chevron-down mobile-chevron"></i>
                    </button>
                    
                    <div class="mobile-user-dropdown hidden" id="mobileUserDropdown">
                        <ul class="mobile-dropdown-list">
                            <li>
                                <a href="<?php echo $toApp; ?>profile.php" class="mobile-dropdown-link">
                                    <i class="fas fa-user"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $toApp; ?>settings.php" class="mobile-dropdown-link <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                            </li>
                            
				<?php if(isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
				    <li>
				        <a href="<?php echo $toCrud; ?>Designs.php" class="mobile-dropdown-link <?php echo $currentPage == 'Designs.php' ? 'active' : ''; ?>">
				            <i class="fas fa-palette"></i>
				            <span>Designs</span>
				        </a>
				    </li>
				<?php endif; ?>
				<li class="mobile-dropdown-divider"></li>
                     <li>
                                <form method="POST" action="<?php echo $toAssets; ?>navmenu/logout.php" class="mobile-logout-form">
                                    <button type="submit" class="mobile-dropdown-link logout-btn">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <!-- Login Button for Non-Logged Users -->
                <div class="mobile-login-section">
                    <button class="mobile-login-btn" onclick="openLoginModal(); closeMobileMenu();">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Menu Footer -->
        <div class="mobile-menu-footer">
            <p class="mobile-copyright">© 2025 Silver Web System</p>
        </div>
    </div>
</div>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>


<div id="overlay" class="form-overlay hidden"></div>
<div id="LoginForm" class="form-modal hidden">
    <h3 class="heading-golden">Login</h3>
    <form id="login-form" method="POST">
        <input type="text" name="username" class="input-golden" placeholder="Username" required>
        <input type="password" name="password" class="input-golden" placeholder="Password" required>
        <div id="login-message"></div>
        <button type="submit" class="btn-forest-primary" style="width: 100%;">Login</button>
    </form>
    <p style="text-align: center; margin-top: 1rem;">
        Don't have an account? <a href="#" id="registerLink" style="color: #138a36;">Sign up</a>
    </p>
</div>

<!-- Signup Modal -->
<div id="SignupForm" class="form-modal hidden">
    <h3 class="heading-golden">Create Account</h3>
    <form id="signup-form" method="POST">
        <input type="email" name="email" class="input-golden" placeholder="Email" required>
        <input type="text" name="username" class="input-golden" placeholder="Username" required>
        <input type="password" id="signup-password" name="password" class="input-golden" placeholder="Password" required>
        <input type="password" id="signup-passwordrpt" name="passwordrpt" class="input-golden" placeholder="Confirm Password" required>
        <div id="signup-message"></div>
        <button type="submit" class="btn-forest-primary" style="width: 100%;">Sign Up</button>
    </form>
    <p style="text-align: center; margin-top: 1rem;">
        Already have an account? <a href="#" id="registerLink2" style="color: #138a36;">Login</a>
    </p>
</div>






<script>
// Make functions globally available
window.toggleMobileMenu = function() {
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileNavOverlay');
    const hamburgerBtn = document.querySelector('.mobile-nav-toggle');
    const body = document.body;
    
    if (mobileMenu && mobileOverlay) {
        const isActive = mobileMenu.classList.contains('active');
        
        if (!isActive) {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('active');
            mobileOverlay.classList.add('active');
            hamburgerBtn?.classList.add('active');
            body.classList.add('mobile-nav-active');
        } else {
            closeMobileMenu();
        }
    }
}

window.closeMobileMenu = function() {
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileNavOverlay');
    const hamburgerBtn = document.querySelector('.mobile-nav-toggle');
    const body = document.body;
    
    if (mobileMenu) {
        mobileMenu.classList.remove('active');
        mobileOverlay?.classList.remove('active');
        hamburgerBtn?.classList.remove('active');
        body.classList.remove('mobile-nav-active');
        
        setTimeout(() => {
            if (!mobileMenu.classList.contains('active')) {
                mobileMenu.classList.add('hidden');
            }
        }, 382);
    }
}

window.toggleMobileUserDropdown = function(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('mobileUserDropdown');
    const button = event.currentTarget;
    
    if (dropdown) {
        const isHidden = dropdown.classList.contains('hidden');
        
        if (isHidden) {
            dropdown.classList.remove('hidden');
            button.classList.add('active');
        } else {
            dropdown.classList.add('hidden');
            button.classList.remove('active');
        }
    }
}

window.toggleUserDropdown = function(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userDropdownMenu');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

window.openLoginModal = function() {
    document.getElementById('overlay')?.classList.remove('hidden');
    document.getElementById('LoginForm')?.classList.remove('hidden');
    document.getElementById('SignupForm')?.classList.add('hidden');
    closeMobileMenu();
}

window.openSignupModal = function() {
    document.getElementById('overlay')?.classList.remove('hidden');
    document.getElementById('SignupForm')?.classList.remove('hidden');
    document.getElementById('LoginForm')?.classList.add('hidden');
    closeMobileMenu();
}

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // SWITCH BETWEEN LOGIN AND SIGNUP MODALS
    const registerLink = document.getElementById('registerLink');
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('LoginForm')?.classList.add('hidden');
            document.getElementById('SignupForm')?.classList.remove('hidden');
        });
    }
    
    const registerLink2 = document.getElementById('registerLink2');
    if (registerLink2) {
        registerLink2.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('SignupForm')?.classList.add('hidden');
            document.getElementById('LoginForm')?.classList.remove('hidden');
        });
    }
    
// LOGIN FORM HANDLER
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.removeAttribute('action');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const formData = new FormData(this);
        const loginMessage = document.getElementById('login-message');
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        loginMessage.innerHTML = '';
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        submitBtn.disabled = true;
        
        fetch('<?php echo $toAssets; ?>navmenu/silverauth.php?action=login', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Get the response text first to see what we're actually receiving
            return response.text().then(text => {
                try {
                    // Try to parse as JSON
                    const data = JSON.parse(text);
                    return data;
                } catch (e) {
                    console.error('Response was not JSON:', text);
                    throw new Error('Invalid response format');
                }
            });
        })
        .then(data => {
            if (data.success) {
                loginMessage.innerHTML = '<div class="alert alert-success" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(24, 255, 109, 0.1); color: #138a36; border-radius: 0.5rem;">' + data.message + '</div>';
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                loginMessage.innerHTML = '<div class="alert alert-danger" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 0.5rem;">' + (data.message || 'Login failed') + '</div>';
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Login error details:', error);
            loginMessage.innerHTML = '<div class="alert alert-danger" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 0.5rem;">Connection error. Check console for details.</div>';
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
        
        return false;
    });
}

// SIGNUP FORM HANDLER (similar update)
const signupForm = document.getElementById('signup-form');
if (signupForm) {
    signupForm.removeAttribute('action');
    
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const formData = new FormData(this);
        const signupMessage = document.getElementById('signup-message');
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        signupMessage.innerHTML = '';
        
        // Validate passwords
        const password = document.getElementById('signup-password').value;
        const passwordRpt = document.getElementById('signup-passwordrpt').value;
        
        if (password !== passwordRpt) {
            signupMessage.innerHTML = '<div class="alert alert-danger" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 0.5rem;">Passwords do not match</div>';
            return false;
        }
        
        if (password.length < 8) {
            signupMessage.innerHTML = '<div class="alert alert-danger" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 0.5rem;">Password must be at least 8 characters</div>';
            return false;
        }
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
        submitBtn.disabled = true;
        
        fetch('<?php echo $toAssets; ?>navmenu/silverauth.php?action=signup', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    const data = JSON.parse(text);
                    return data;
                } catch (e) {
                    console.error('Response was not JSON:', text);
                    throw new Error('Invalid response format');
                }
            });
        })
        .then(data => {
            if (data.success) {
                signupMessage.innerHTML = '<div class="alert alert-success" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(24, 255, 109, 0.1); color: #138a36; border-radius: 0.5rem;">' + data.message + '</div>';
                
                signupForm.reset();
                
                setTimeout(() => {
                    document.getElementById('SignupForm').classList.add('hidden');
                    document.getElementById('LoginForm').classList.remove('hidden');
                    
                    const loginMessage = document.getElementById('login-message');
                    if (loginMessage) {
                        loginMessage.innerHTML = '<div class="alert alert-success" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(24, 255, 109, 0.1); color: #138a36; border-radius: 0.5rem;">Account created! Please login.</div>';
                    }
                }, 2000);
            } else {
                signupMessage.innerHTML = '<div class="alert alert-danger" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 0.5rem;">' + (data.message || 'Registration failed') + '</div>';
            }
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Signup error details:', error);
            signupMessage.innerHTML = '<div class="alert alert-danger" style="padding: 0.5rem; margin-bottom: 1rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 0.5rem;">Connection error. Check console for details.</div>';
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
        
        return false;
    });
}




    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        const userDropdown = document.getElementById('userDropdownMenu');
        if (userDropdown && !e.target.closest('.dropdown')) {
            userDropdown.classList.add('hidden');
        }
        
        const mobileUserDropdown = document.getElementById('mobileUserDropdown');
        const mobileUserBtn = document.querySelector('.mobile-user-btn');
        if (mobileUserDropdown && !e.target.closest('.mobile-user-section')) {
            mobileUserDropdown.classList.add('hidden');
            mobileUserBtn?.classList.remove('active');
        }
    });
    
    // Close mobile menu on overlay click
    const mobileOverlay = document.getElementById('mobileNavOverlay');
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Close modals on overlay click
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            this.classList.add('hidden');
            document.getElementById('LoginForm')?.classList.add('hidden');
            document.getElementById('SignupForm')?.classList.add('hidden');
        });
    }
    
    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
            document.getElementById('overlay')?.classList.add('hidden');
            document.getElementById('LoginForm')?.classList.add('hidden');
            document.getElementById('SignupForm')?.classList.add('hidden');
        }
    });
});
</script>


