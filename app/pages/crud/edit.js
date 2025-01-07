$(document).ready(function() {
 

    $('#createBtn').click(function(e) {
        e.preventDefault();
        $('#overlay').toggle();
       $('#createForm').toggle(); 
    });

    
    $('#editBtn').click(function(e) {
        e.preventDefault(); 
        $('#overlay').show(); 
        $('#editForm').toggle(); 
        const designid = $(this).data('designid');
        const name = $(this).data('name');
        const creationDate = $(this).data('creation-date');
        const description = $(this).data('description');
        const details = $(this).data('details');
        const edition = $(this).data('edition');
        const unitLaunchPrice = $(this).data('unit-launch-price');

        $('#editId').val(designid);
        $('#editName').val(name);
        $('#editCreationDate').val(creationDate);
        $('#editDescription').val(description);
        $('#editDetails').val(details);
        $('#editEdition').val(edition);
        $('#editUnitLaunchPrice').val(unitLaunchPrice);
    });

    $('#overlay').click(function() {
        
        $(this).hide();
        $('#editForm').hide();
        $('#createForm').hide(); 
    });

});