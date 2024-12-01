$(document).ready(function () {
    // console.log(url);
        // Initialize DataTable
    const dataTable = $('#authors-table').DataTable({
        responsive: true,
        pageLength: 20,
        lengthChange: false,
        searching: true,
        ajax: {
            // http://localhost/ilibrary/back-end/api-author/authors.php
            url: url+'api-author/authors.php',
            dataSrc: function(json) {
                // console.log('Received data:', json);
                return Array.isArray(json) ? json : (json.data || []);
            },
            error: function (xhr, error, thrown) {
                console.error('DataTables Ajax Error:', error, thrown);
                console.log('XHR Response:', xhr.responseText); // Add more error details
            }
        },
        columns: [
            { data: 'author_id' },
            { 
                data: 'fname',
                render: function(data) {
                    return data ? data : '<i>N/A</i>';
                }
            },
            { 
                data: 'lname',
                render: function(data) {
                    return data ? data : '<i>N/A</i>';
                }
            },
            { 
                data: 'corporate_author',
                render: function(data) {
                    return data ? data : '<i>N/A</i>';
                }
            },
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
        ordering: true,
        initComplete: function(settings, json) {
            console.log('DataTable data:', json);
        }
    });

    var editId = "";

    $(document).on("click", ".delete-btn", function () {
        const id = $(this).data('id')
        console.log(id) 
        var data = {id:id}
        $.ajax({
            type: "DELETE",
            url: url+"api-author/delete-author.php",
            data: JSON.stringify(data),
            dataType: "json",
            success: function (response) {
                if(response.success){
                    Swal.fire({
                        title: 'Success!',  
                        text: 'Author deleted successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        dataTable.ajax.reload();  
                    });
                } 
            }
        });        
    });
    
    $(document).on("submit", "#editAuthorForm", function (e) {
        e.preventDefault();
    
        var formData = new FormData(this);
        formData.append("id", editId);
    
        // Convert FormData to JSON
        var data = {};
        formData.forEach((value, key) => {
            data[key] = value;
            console.log(`${key}: ${value}`);
        });
    
        // Send JSON data via AJAX
        $.ajax({
            type: "PUT",
            url: url + 'api-author/edit-authors.php',
            data: JSON.stringify(data),
            contentType: "application/json", 
            processData: false, 
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $('#editModal').modal('hide');  
                    $('#editAuthorForm')[0].reset(); 

                    Swal.fire({
                        title: 'Success!',
                        text: 'Author updated successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        dataTable.ajax.reload();  
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.error || 'Failed to update the author.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $(document).on("click", ".edit-btn", function () {
        const holdId = $(this).data('id');
        editId = holdId;
        console.log('editId', editId)
        
        $('#editModal').modal('show');
        $.ajax({
            type: 'GET',
            url: url + 'api-author/authors.php',
            data: { id: holdId },
            dataType: 'json',
            success: function(response) {
                console.log(response.data)

                $("#fname1").val(response.data.fname);
                $("#lname1").val(response.data.lname);
                $("#corporate_author1").val(response.data.corporate_author);

            },
            error: function(error) {
                console.error("Error fetching author details:", error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Error fetching author details. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $(document).on("submit", "#addAuthorForm", function (e) {
        e.preventDefault();
    
        var formData = new FormData(this);
    
        formData.forEach((value, key) => {
            console.log(`${key}: ${value}`);
        });
    
        $.ajax({
            type: "POST",
            url: url + 'api-author/add-author.php',
            data: formData,
            processData: false, 
            contentType: false, 
            dataType: "json",
            success: function (response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Author added successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#addAuthorModal').modal('hide');
                    $('#addAuthorForm')[0].reset();
                    dataTable.ajax.reload();
                });
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred during the request.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                console.error('Error:', xhr.responseText || status || error);
            }
        });
    });
    
    function updateAuthorFields() {
        const fname = $('#fname').val();
        const lname = $('#lname').val();
        const corporateAuthor = $('#corporate_author').val();

        if (fname || lname) {
            $('#corporate_author').prop('disabled', true);
        } else if (corporateAuthor) {
            $('#fname, #lname').prop('disabled', true);
        } else {
            $('#fname, #lname, #corporate_author').prop('disabled', false);
        }
    }

    $('#fname, #lname, #corporate_author').on('input', updateAuthorFields);
});