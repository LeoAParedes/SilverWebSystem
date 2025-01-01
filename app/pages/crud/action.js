$(document).ready(function() {
    $('#editBtn').click(function(e) {
        e.preventDefault(); // Prevent default button behavior
        $('#overlay').toggle(); // Show overlay
        $('#editForm').load(); // Show edit form
    });
});