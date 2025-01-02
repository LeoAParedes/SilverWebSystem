$(document).ready(function() {
    // Handle click event for all edit buttons
    $('.editBtn').click(function() {
        $('.overlay').toggle(); // Show the overlay

        // Retrieve data attributes from the clicked button
        const id = $(this).data('id');
        const name = $(this).data('name');
        const creationDate = $(this).data('creation-date');
        const description = $(this).data('description');
        const details = $(this).data('details');
        const edition = $(this).data('edition');
        const unitLaunchPrice = $(this).data('unit-launch-price');

        // Populate the edit form with the retrieved data
        $('#editId').val(id);
        $('#editName').val(name);
        $('#editCreationDate').val(creationDate);
        $('#editDescription').val(description);
        $('#editDetails').val(details);
        $('#editEdition').val(edition);
        $('#editUnitLaunchPrice').val(unitLaunchPrice);

        // Show the edit form
        $('#editForm').show();
    });

    // Hide overlay and forms when overlay is clicked
    $('#overlay').click(function() {
        $(this).hide(); // Hide overlay
        $('#editForm').hide(); // Hide edit form
        // Add other forms to hide if necessary
    });
});