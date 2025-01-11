
window.addEventListener('DOMContentLoaded', event => {

    function scrollToSection() {
        const hash = window.location.hash;
        if (hash) {
            const targetElement = document.querySelector(hash);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }
    
    window.addEventListener('load', scrollToSection);
    window.addEventListener('hashchange', scrollToSection);
    
    // Navbar shrink function
    function navbarShrink() {
        const navbarCollapsible = document.body.querySelector('#mainNav');
        if (!navbarCollapsible) {
            return;
        }
        if (window.scrollY === 0) {
            navbarCollapsible.classList.remove('navbar-shrink');
        } else {
            navbarCollapsible.classList.add('navbar-shrink');
        }

    }

    // Shrink the navbar 
    navbarShrink();

    // Shrink the navbar when page is scrolled
    document.addEventListener('scroll', navbarShrink);

    // Activate Bootstrap scrollspy on the main nav element
    const mainNav = document.body.querySelector('#mainNav');
    if (mainNav) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#mainNav',
            rootMargin: '0px 0px -40%',
        });
    };

    // Collapse responsive navbar when toggler is visible
    const navbarToggler = document.body.querySelector('.navbar-toggler');
    const responsiveNavItems = [].slice.call(
        document.querySelectorAll('#navbarResponsive .nav-link')
    );
    responsiveNavItems.map(function (responsiveNavItem) {
        responsiveNavItem.addEventListener('click', () => {
            if (window.getComputedStyle(navbarToggler).display !== 'none') {
                navbarToggler.click();
            }
        });
    });

});
$(document).ready(function() {
    $('#loginBtn').click(function(e) {
        e.preventDefault(); // Prevent default anchor click behavior
        $('#overlay').toggle(); // Show overlay
        $('#LoginForm').toggle(); 
    });
    
    $('#editBtn').click(function(e) {
        e.preventDefault();
        $('#overlay').toggle();
        $('#editForm').toggle();
    });

    $('#createBtn').click(function(e) {
        e.preventDefault();
        $('#overlay').toggle();
        $('#createForm').toggle();
    });

    $('#registerLink').click(function(e) {
        e.preventDefault();
        $('#SignupForm').toggle();
        $('#LoginForm').toggle();
    });
    
    $('#registerLink2').click(function(e) {
        e.preventDefault(); 
        $('#SignupForm').toggle();
        $('#LoginForm').toggle(); 
    });

    $('#overlay').click(function() {
        $(this).hide(); // Hide overlay
        $('#SignupForm').hide(); 
        $('#LoginForm').hide();
        $('#createForm').hide();
        $('#editForm').hide();
    });

    

});