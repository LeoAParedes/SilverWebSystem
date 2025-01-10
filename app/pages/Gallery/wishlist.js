document.addEventListener('DOMContentLoaded', function() {
    // Function to add an item to the wishlist
    function addToWishlist(designid) {
        fetch('wishlist.php', { // Adjust the path as necessary
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ designid: designid })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Notify the user of success
            } else {
                alert('Error: ' + data.message); // Notify the user of the error
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding to wishlist: ' + error.message);
        });
    }

    // Set up event listeners for all wishlist buttons
    document.querySelectorAll('.wishlist-button').forEach(button => {
        button.addEventListener('click', function() {
            const designid = this.getAttribute('data-designid'); // Get the design ID from the button
            addToWishlist(designid);
        });
    });
});