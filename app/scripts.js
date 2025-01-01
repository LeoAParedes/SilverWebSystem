

$(document).ready(function() {
    
    $('#loginBtn').click(function(e) {
        e.preventDefault(); // Prevent default anchor click behavior
        $('#overlay').toggle(); // Show overlay
        $('#SignupForm').toggle(); // Show Signup form
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
    });

});