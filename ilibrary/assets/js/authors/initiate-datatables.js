(function() {
    'use strict';

    let authid = null;
    const accId = getCookie('cookie-adminId');

    // Initialize DataTable
    const dataTable = $('#dataTables-example').DataTable({
        responsive: true,
        pageLength: 20,
        lengthChange: false,
        searching: true,
        ajax: {
            url: 'https://ilibrary.zreky.muccs.host/back-end/api-author/v1/authors',
            dataSrc: ''
        },
        columns: [
            { data: 'author_id' },
            { data: 'fname' },
            { data: 'lname' },
            {
                data: null,
                render: function (data, type, row) {
                    return ` 
                        <button class="btn btn-warning btn-sm edit-btn" data-id="${row.author_id}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.author_id}">Delete</button>
                    `;
                }
            }
        ],
        ordering: true
    });

    // Handle Add Author form submission
    $('#addAuthorForm').on('submit', function(e) {
        e.preventDefault();

        // Collect form data
        const formData = {
            fname: $('input[name="fname"]').val(),
            lname: $('input[name="lname"]').val()
        };

        // Perform AJAX POST to add the new author
        $.ajax({
            type: 'POST',
            url: `https://ilibrary.zreky.muccs.host/back-end/api-author/v1/authors/${accId}`, // Adjust the URL if necessary
            data: formData,
            dataType: "json",
            success: function(response) {
                console.log("Author added:", response);
                alert("Author added successfully!");
                $('#exampleModal').modal('hide');  // Close the modal
                $('#addAuthorForm')[0].reset();  // Reset the form
                dataTable.ajax.reload();  // Reload the DataTable to show the new author
            },
            error: function(error) {
                console.error("Error adding author:", error);
                alert("Error adding the author. Please try again.");
            }
        });
    });

    $('#dataTables-example').on('click', '.edit-btn', function () {
        authid = $(this).data('id');
    
        $.ajax({
            type: 'GET',
            url: `https://ilibrary.zreky.muccs.host/back-end/api-author/v1/authors/${authid}`,
            dataType: 'json',
            success: function(auth) {

                // Fill form fields with book data
                $('input[name="fname1"]').val(auth[0].fname);
                $('input[name="lname1"]').val(auth[0].lname);

                // Show the modal
                $('#exampleModal1').modal('show');
            },
            error: function(error) {
                console.error("Error fetching book details:", error);
                alert("Error fetching book details. Please try again.");
            }
        });
    });

    $('#editAuthorForm').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            fname: $('input[name="fname1"]').val(),
            lname: $('input[name="lname1"]').val(),
        };

        $.ajax({
            type: 'PUT',
            url: `https://ilibrary.zreky.muccs.host/back-end/api-author/v1/authors/${authid}/${accId}`,
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                console.log("Author updated:", response);
                alert("Author updated successfully!");
                $('#exampleModal1').modal('hide');
                $('#editAuthorForm')[0].reset();
                dataTable.ajax.reload();
            },
            error: function(error) {
                console.error("Error updating book:", error);
                alert("Error updating the book. Please try again.");
            }
        });
    });

    $('#dataTables-example').on('click', '.delete-btn[data-id]', function () {

        authid = $(this).data('id'); // Get the subject ID from the data attribute
        
        if (confirm("Are you sure you want to delete this author?")) {
            // Send AJAX DELETE request
            $.ajax({
                url: `https://ilibrary.zreky.muccs.host/back-end/api-author/v1/authors/${authid}/${accId}`,
                //url: `http://localhost/ilibrary/admin-side/back-end/api-holding/v1/holdings/${holdId}`,
                type: 'DELETE',
                success: function (response) {
                    console.log(response);
                    alert(response.msg);
                    window.location.replace("authors.html");
                },
                error: function (xhr, status, error) {
                    console.error('Error occurred:', xhr, status, error);
                    alert('Error occurred while deleting subject. Please try again.');
                }
            });
        }
    });    
})();
