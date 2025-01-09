$(document).ready(function() {

    $('.editBtn').click(function(e) {
        e.preventDefault(); 
        $('#overlay').show(); 
        $('#editForm').toggle(); 
        const designid = $(this).data('designid');
        const name = $(this).data('name');
        const creationDate = $(this).data('creation_date');
        const description = $(this).data('description');
        const edition = $(this).data('edition');
        const unitLaunchPrice = $(this).data('unit-launch-price');
        const size = $(this).data('size');
        const category = $(this).data('category');
    
        $('#editId').val(designid);
        $('#editName').val(name);
        $('#editCreationDate').val(creationDate);
        $('#editDescription').val(description);
        $('#editEdition').val(edition);
        $('#editUnitLaunchPrice').val(unitLaunchPrice);
        $('#size').val(size);
        $('#category').val(category);
        $('#editFormAction').attr('action', 'edit.php?designid=' + designid);

    });




    $('#createBtn').click(function(e) {
        e.preventDefault();
        $('#overlay').toggle();
       $('#createForm').toggle(); 
    });

    
    $('#overlay').click(function() {
        
        $(this).hide();
        $('#editForm').hide();
        $('#createForm').hide(); 
        $('#DeleteForm').hide(); 
    });

   

  





});
