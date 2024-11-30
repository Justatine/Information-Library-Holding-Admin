(function() {
    'use strict';

    let userid = null;
    const accId = getCookie('cookie-adminId');

    // Initialize DataTable
    const dataTable = $('#dataTables-example').DataTable({
        responsive: true,
        pageLength: 20,
        lengthChange: false,
        searching: true,
        ajax: {
            url: 'https://ilibrary.zreky.muccs.host/back-end/api-user/v1/admin_acc',
            dataSrc: ''
        },
        columns: [
            { data: 'admin_id' },
            { data: 'fname' },
            { data: 'lname' },
            { data: 'email' },
            {
                data: null,
                render: function (data, type, row) {
                    return ` 
                        <button class="btn btn-warning btn-sm edit-btn" data-id="${row.admin_id}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.admin_id}">Delete</button>
                    `;
                }
            }
        ],
        ordering: true
    });

    // Handle Add Author form submission
    $('#addUsersForm').on('submit', function(e) {
        e.preventDefault();

        // Get password and confirmPassword values from the form
        const password = $('input[name="password"]').val();
        const confirmPassword = $('input[name="conf_password"]').val();

        // Validate passwords match
        if (password && password !== confirmPassword) {
            alert("Passwords do not match. Please try again.");
            return;
        }

        const formData = {
            idnum: $('input[name="idnum"]').val(),
            fname: $('input[name="fname"]').val(),
            lname: $('input[name="lname"]').val(),
            email: $('input[name="email"]').val(),
        };

        if (password) {
            formData.password = password;
        }

        // Perform AJAX POST to add the new author
        $.ajax({
            type: 'POST',
            url: `https://ilibrary.zreky.muccs.host/back-end/api-user/v1/admin_acc/${accId}`,
            data: formData,
            dataType: "json",
            success: function(response) {
                console.log("User added:", response);
                alert("User added successfully!");
                $('#exampleModal').modal('hide');  
                $('#addUsersForm')[0].reset();  
                dataTable.ajax.reload();  
            },
            error: function(error) {
                console.error("Error adding user:", error);
                alert("Error adding the user. Please try again.");
            }
        });
    });

    $('#dataTables-example').on('click', '.edit-btn', function () {
        userid = $(this).data('id');
    
        $.ajax({
            type: 'GET',
            url: `https://ilibrary.zreky.muccs.host/back-end/api-user/v1/admin_acc/${userid}`,
            dataType: 'json',
            success: function(user) {
                console.log(user);
                // Fill form fields with user data
                $('input[name="fname1"]').val(user[0].fname);
                $('input[name="lname1"]').val(user[0].lname);
                $('input[name="email1"]').val(user[0].email);

                // Show the modal
                $('#exampleModal1').modal('show');
            },
            error: function(error) {
                console.error("Error fetching user details:", error);
                alert("Error fetching user details. Please try again.");
            }
        });
    });

    $('#editUsersForm').on('submit', function (e) {
        e.preventDefault();

        // Get password and confirmPassword values from the form
        const password = $('input[name="password1"]').val();
        const confirmPassword = $('input[name="conf_password1"]').val();

        // Validate passwords match
        if (password && password !== confirmPassword) {
            alert("Passwords do not match. Please try again.");
            return;
        }

        const formData = {
            idnum: $('input[name="idnum1"]').val(),
            fname: $('input[name="fname1"]').val(),
            lname: $('input[name="lname1"]').val(),
            email: $('input[name="email1"]').val(),
        };

        if (password) {
            formData.password = password;
        }

        $.ajax({
            type: 'PUT',
            url: `https://ilibrary.zreky.muccs.host/back-end/api-user/v1/admin_acc/${userid}/${accId}`,
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                console.log("User updated:", response);
                alert("User updated successfully!");
                $('#exampleModal1').modal('hide');
                $('#editUsersForm')[0].reset();
                dataTable.ajax.reload();
            },
            error: function(error) {
                console.error("Error updating user:", error);
                alert("Error updating the user. Please try again.");
            }
        });
    });

    $('#dataTables-example').on('click', '.delete-btn[data-id]', function () {

        userid = $(this).data('id'); // Get the subject ID from the data attribute
        
        if (confirm("Are you sure you want to delete this user?")) {
            // Send AJAX DELETE request
            $.ajax({
                url: `https://ilibrary.zreky.muccs.host/back-end/api-user/v1/admin_acc/${userid}/${accId}`,
                //url: `http://localhost/ilibrary/admin-side/back-end/api-holding/v1/holdings/${holdId}`,
                type: 'DELETE',
                success: function (response) {
                    console.log(response);
                    alert(response.msg);
                    window.location.replace("users.html");
                },
                error: function (xhr, status, error) {
                    console.error('Error occurred:', xhr, status, error);
                    alert('Error occurred while deleting user. Please try again.');
                }
            });
        }
    });    
})();
