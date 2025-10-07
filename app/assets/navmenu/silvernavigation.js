/**
 * SilverWebSystem Navigation JavaScript
 * Forest Green Theme with Golden Ratio Animations
 * Location: /app/assets/navmenu/silvernavigation.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const loginBtn = document.getElementById('loginBtn');
    const overlay = document.getElementById('overlay');
    const loginForm = document.getElementById('LoginForm');
    const signupForm = document.getElementById('SignupForm');
    const registerLink = document.getElementById('registerLink');
    const registerLink2 = document.getElementById('registerLink2');
    const loginFormElement = document.getElementById('login-form');
    const signupFormElement = document.getElementById('signup-form');
    
    // Open auth modal
    window.openAuthModal = function() {
        if (overlay) overlay.classList.remove('hidden');
        if (loginForm) loginForm.classList.remove('hidden');
    };
    
    // Close auth modal
    window.closeAuthModal = function() {
        if (overlay) overlay.classList.add('hidden');
        if (loginForm) loginForm.classList.add('hidden');
        if (signupForm) signupForm.classList.add('hidden');
    };
    
    // Toggle mobile menu
    window.toggleMobileMenu = function() {
        const navmenu = document.getElementById('navmenu');
        if (navmenu) {
            navmenu.classList.toggle('show');
        }
    };
    
    // Toggle user dropdown
    window.toggleUserDropdown = function(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    };
    
    // Login button click
    if (loginBtn) {
        loginBtn.addEventListener('click', openAuthModal);
    }
    
    // Switch between login and signup
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (loginForm) loginForm.classList.add('hidden');
            if (signupForm) signupForm.classList.remove('hidden');
        });
    }
    
    if (registerLink2) {
        registerLink2.addEventListener('click', function(e) {
            e.preventDefault();
            if (signupForm) signupForm.classList.add('hidden');
            if (loginForm) loginForm.classList.remove('hidden');
        });
    }
    
    // Close modal on overlay click
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeAuthModal();
            }
        });
    }
    
    // Handle login form submission
    if (loginFormElement) {
        loginFormElement.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('login-message');
            const formData = new FormData(loginFormElement);
            
            messageDiv.innerHTML = '<div class="text-info">Logging in...</div>';
            
            fetch(loginFormElement.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `<div class="p-3 rounded bg-success text-white">${data.message}</div>`;
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    messageDiv.innerHTML = `<div class="p-3 rounded bg-danger text-white">${data.message || 'Login failed'}</div>`;
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                // If JSON parsing fails, try regular form submission
                loginFormElement.submit();
            });
        });
    }
    
    // Handle signup form submission
    if (signupFormElement) {
        signupFormElement.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('signup-message');
            const formData = new FormData(signupFormElement);
            
            // Validate passwords match
            const password = document.getElementById('signup-password').value;
            const passwordRpt = document.getElementById('signup-passwordrpt').value;
            
            if (password !== passwordRpt) {
                messageDiv.innerHTML = '<div class="p-3 rounded bg-danger text-white">Passwords do not match!</div>';
                return;
            }
            
            messageDiv.innerHTML = '<div class="text-info">Creating account...</div>';
            
            fetch(signupFormElement.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `<div class="p-3 rounded bg-success text-white">${data.message}</div>`;
                    setTimeout(() => {
                        if (signupForm) signupForm.classList.add('hidden');
                        if (loginForm) loginForm.classList.remove('hidden');
                        const loginMessage = document.getElementById('login-message');
                        if (loginMessage) {
                            loginMessage.innerHTML = '<div class="p-3 rounded bg-success text-white">Registration successful! Please login.</div>';
                        }
                    }, 2000);
                } else {
                    messageDiv.innerHTML = `<div class="p-3 rounded bg-danger text-white">${data.message || 'Registration failed'}</div>`;
                }
            })
            .catch(error => {
                console.error('Signup error:', error);
                // If JSON parsing fails, try regular form submission
                signupFormElement.submit();
            });
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const userDropdown = document.getElementById('userDropdown');
        if (userDropdown && !e.target.closest('.dropdown')) {
            userDropdown.classList.add('hidden');
        }
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#0') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Navbar shrink on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('header');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-shrink');
            } else {
                navbar.classList.remove('navbar-shrink');
            }
        }
    });
});



document.addEventListener('DOMContentLoaded', function() {
    // Get mobile elements
    const hamburger = document.querySelector('.mobile-nav-toggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileNavOverlay');
    const body = document.body;
    
    // Mobile menu toggle handler
    if (hamburger) {
        hamburger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle states
            this.classList.toggle('active');
            mobileMenu?.classList.toggle('active');
            mobileOverlay?.classList.toggle('active');
            body.classList.toggle('mobile-nav-active');
            
            // Manage hidden class
            if (mobileMenu) {
                if (mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('hidden');
                } else {
                    setTimeout(() => {
                        if (!mobileMenu.classList.contains('active')) {
                            mobileMenu.classList.add('hidden');
                        }
                    }, 382); // Golden ratio timing
                }
            }
        });
    }
    
    // Close mobile menu function
    function closeMobileMenu() {
        hamburger?.classList.remove('active');
        mobileMenu?.classList.remove('active');
        mobileOverlay?.classList.remove('active');
        body.classList.remove('mobile-nav-active');
        
        if (mobileMenu) {
            setTimeout(() => {
                if (!mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.add('hidden');
                }
            }, 382);
        }
    }
    
    // Close on overlay click
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu?.classList.contains('active')) {
            closeMobileMenu();
        }
    });
    
    // Handle navigation clicks in mobile menu
    document.querySelectorAll('.mobile-nav-link-forest').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't close for buttons (login/logout)
            if (this.tagName === 'BUTTON' && !this.classList.contains('login-btn')) {
                return;
            }
            
            // Check if it's an anchor link
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                closeMobileMenu();
                
                const target = document.querySelector(href);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 400);
                }
            } else if (href && !href.startsWith('#')) {
                // For regular links, close menu after a short delay
                setTimeout(closeMobileMenu, 100);
            }
        });
    });
    
    // Swipe gestures for mobile menu (optional enhancement)
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (mobileMenu) {
        mobileMenu.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        mobileMenu.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
    }
    
    function handleSwipe() {
        const swipeThreshold = 50;
        if (touchEndX - touchStartX > swipeThreshold) {
            // Swiped right - close menu
            closeMobileMenu();
        }
    }
    
    // Update active menu item based on scroll position
    function updateActiveMenuItem() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPosition = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                // Update mobile menu active states
                document.querySelectorAll(`.mobile-nav-link-forest[href="#${sectionId}"]`).forEach(link => {
                    link.classList.add('active');
                });
                
                // Remove active from others
                document.querySelectorAll(`.mobile-nav-link-forest:not([href="#${sectionId}"])`).forEach(link => {
                    if (!link.classList.contains('login-btn') && !link.classList.contains('logout-btn')) {
                        link.classList.remove('active');
                    }
                });
            }
        });
    }
    
    // Throttled scroll handler for active menu items
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            window.cancelAnimationFrame(scrollTimeout);
        }
        scrollTimeout = window.requestAnimationFrame(updateActiveMenuItem);
    });
});

// Export functions to global scope
window.toggleMobileMenu = function() {
    const hamburger = document.querySelector('.mobile-nav-toggle');
    hamburger?.click();
};

window.closeMobileMenu = function() {
    const hamburger = document.querySelector('.mobile-nav-toggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileNavOverlay');
    const body = document.body;
    
    hamburger?.classList.remove('active');
    mobileMenu?.classList.remove('active');
    mobileOverlay?.classList.remove('active');
    body.classList.remove('mobile-nav-active');
    
    if (mobileMenu) {
        setTimeout(() => {
            if (!mobileMenu.classList.contains('active')) {
                mobileMenu.classList.add('hidden');
            }
        }, 382);
    }
};

// User dropdown toggle
function toggleUserDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userDropdownMenu');
    dropdown.classList.toggle('active');
    
    // Close on outside click
    document.addEventListener('click', function closeDropdown(e) {
        if (!e.target.closest('.user-dropdown')) {
            dropdown.classList.remove('active');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

// Login modal functions
function openLoginModal() {
    const modal = document.getElementById('authModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLoginModal() {
    const modal = document.getElementById('authModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function switchToSignup() {
    document.getElementById('loginFormContainer').classList.add('hidden');
    document.getElementById('signupFormContainer').classList.remove('hidden');
}

function switchToLogin() {
    document.getElementById('signupFormContainer').classList.add('hidden');
    document.getElementById('loginFormContainer').classList.remove('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('authModal');
        if (!modal.classList.contains('hidden')) {
            closeLoginModal();
        }
    }
});

