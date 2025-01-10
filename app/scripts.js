
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
        $('#SignupForm').toggle(); // Show Signup form
    });
    
    $('#editBtn').click(function(e) {
        e.preventDefault();
        $('#overlay').toggle();
        $('#editForm').toggle(); // show Signup form
    });

    $('#createBtn').click(function(e) {
        e.preventDefault();
        $('#overlay').toggle();
        $('#createForm').toggle(); // show Signup form
    });

    $('#registerLink').click(function(e) {
        e.preventDefault(); // Prevent default anchor click behavior
        // Show overlay
        $('#SignupForm').toggle();
        $('#LoginForm').toggle(); // Show Login form
    });

    $('#registerLink2').click(function(e) {
        e.preventDefault(); // Prevent default anchor click behavior
        // Show overlay
        $('#SignupForm').toggle();
        $('#LoginForm').toggle(); // Show Login form
    });

    $('#overlay').click(function() {
        $(this).hide(); // Hide overlay
        $('#SignupForm').hide(); // Hide Signup form
        $('#LoginForm').hide();
        $('#createForm').hide();
        $('#editForm').hide();
    });

    

    function addToWishlist(designid) {
        fetch('wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ designid: designid }),
        })
        .then(response => response.json())
        .then(data => {
            // Show the modal with the response message
            document.getElementById('modalMessage').innerText = data.message;
            toggleModal();
        })
        .catch((error) => {
            console.error('Error:', error);
            document.getElementById('modalMessage').innerText = 'An error occurred while adding to wishlist.';
            toggleModal();
        });
    }
    
    function toggleModal() {
        const overlay = document.getElementById('overlay');
        overlay.style.display = overlay.style.display === 'none' ? 'flex' : 'none';
    }


});