$(document).ready(function () {
    function fetchDepartments() {
        $.ajax({
            type: "GET",
            url: url+"api-department/departments.php",
            dataType: "json",
            success: function (response) {
                // console.log(response.data)
                const departmentSelect = $('#department');
                const departmentSelect1 = $('#edit_department');
                
                departmentSelect.empty();
                departmentSelect1.empty();

                departmentSelect.append('<option disabled selected>Select Department</option>');
                departmentSelect1.append('<option disabled selected>Select Department</option>');
                $.map(response.data, function (element, index) {
                    departmentSelect.append('<option value="' + element.dept_id + '">' + element.deptname + '</option>'); 
                    departmentSelect1.append('<option value="' + element.dept_id + '">' + element.deptname + '</option>'); 
                });
            }
        });
    }

    fetchDepartments();

    // Initialize DataTable
    const dataTable = $('#holdings-table').DataTable({
        responsive: true,
        pageLength: 20,
        lengthChange: false,
        searching: true,
        ajax: {
            url: url+'api-holding/holdings.php',
            dataSrc: 'data',
            error: function (xhr, error, thrown) {
                console.error('DataTables Ajax Error:', error, thrown);
            }
        },
        columns: [
            { data: 'hold_id' },
            { data: 'title' },
            { data: 'accss_num' },
            { data: 'call_num' },
            { data: 'published_year' },
            { data: 'copies' },
            { data: 'av_copies' },
            { data: 'course', visible: true },
            { data: 'deptname' },
            {
                data: null,
                render: function (data, type, row) {
                    return ` 
                        <button class="btn btn-warning btn-sm edit-btn" data-toggle="modal" data-target="#editModal"  data-id="${row.hold_id}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.hold_id}">Delete</button>
                    `;
                }
            }
        ],
        ordering: true
    });

    
    var editId = "";

    $(document).on("click", ".delete-btn", function () {
        const id = $(this).data('id')
        console.log(id) 
        var data = {id:id}
        $.ajax({
            type: "DELETE",
            url: url+"api-holding/delete-holding.php",
            data: JSON.stringify(data),
            dataType: "json",
            success: function (response) {
                if(response.success){
                    Swal.fire({
                        title: 'Success!',
                        text: 'Holding deleted successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        dataTable.ajax.reload();  
                    });
                } 
            }
        });        
    });
    
    $(document).on("submit", "#editBookForm", function (e) {
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
            url: url + 'api-holding/update-holding.php',
            data: JSON.stringify(data),
            contentType: "application/json", 
            processData: false, 
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $('#editModal').modal('hide');  
                    $('#editBookForm')[0].reset(); 

                    Swal.fire({
                        title: 'Success!',
                        text: 'Holding updated successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        dataTable.ajax.reload();  
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.error || 'Failed to update the holding.',
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
    
    var subjectsInput = document.querySelector('input[name=edit_subjects]');
    var editSubjectsInput = document.querySelector('input[name=edit_subjects]');

    new Tagify(subjectsInput);
    new Tagify(editSubjectsInput);
    var editTagify = new Tagify(editSubjectsInput);
    
    function setEditSubjects(holdingData) {
        editTagify.removeAllTags();
        
        if (holdingData.subjects && Array.isArray(holdingData.subjects)) {
            editTagify.addTags(holdingData.subjects);
        }
    }

    var authorsInput = document.querySelector('input[name=edit_authors]');
    var editAuthorsInput = document.querySelector('input[name=edit_authors]');

    new Tagify(authorsInput);
    new Tagify(editAuthorsInput);
    var editAuthorsTagify = new Tagify(editAuthorsInput);
    
    function setEditAuthors(holdingData) {
        editAuthorsTagify.removeAllTags();
        // console.log(holdingData)
        if (holdingData.authors && Array.isArray(holdingData.authors)) {
            editAuthorsTagify.addTags(holdingData.authors);
        }
    }

    $(document).on("click", ".edit-btn", function () {
        const holdId = $(this).data('id');
        editId = holdId;

        $('#editModal').modal('show');
        $.ajax({
            type: 'GET',
            url: url + 'api-holding/holdings.php',
            data: { id: holdId },
            dataType: 'json',
            success: function(response) {
                // console.log(response.data)
                setEditSubjects(response.data);
                setEditAuthors(response.data);
                $("#edit_title").val(response.data.title);
                $("#edit_accss_num").val(response.data.accss_num);
                $("#edit_call_num").val(response.data.call_num);
                $("#edit_published_year").val(response.data.published_year);
                $("#auth1").val(response.data.author_id);
                $("#edit_copies").val(response.data.copies);
                $("#edit_av_copies").val(response.data.av_copies);
                $("#edit_sub_name").val(response.data.sub_name);
                $("#edit_course").val(response.data.course);
                $("#edit_department").val(response.data.dept_id);
                $("#edit_keyword").val(response.data.keyword);
            },
            error: function(error) {
                console.error("Error fetching holding details:", error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Error fetching holding details. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $(document).on("submit", "#addBookForm", function (e) {
        e.preventDefault();
    
        var formData = new FormData(this);
    
        formData.forEach((value, key) => {
            console.log(`${key}: ${value}`);
        });
    
        $.ajax({
            type: "POST",
            url: url + 'api-holding/add-holding.php',
            data: formData,
            processData: false, 
            contentType: false, 
            dataType: "json",
            success: function (response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Library Holding Added Successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#exampleModal').modal('hide');
                    $('#addBookForm')[0].reset();
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
});