$(document).ready(function () {
    let subid = null;
    const accId = getCookie('cookie-adminId');

    // Initialize DataTable
    const dataTable = $('#dataTables-example1').DataTable({
        responsive: true,
        pageLength: 20,
        lengthChange: false,
        searching: true,
        ajax: {
            url: url+'api-subject/?p=v1/subjects',
            dataSrc: ''
        },
        columns: [
            { data: 'sub_id', visible: false },
            { data: 'sub_name' },
            { data: 'sub_desc' },
            { data: 'year_level' },
            { data: 'acad_year' },
            { data: 'semester' },
            { data: 'course' },
            {
                data: null,
                render: function (data, type, row) {
                    return `                     
                        <button type="button" class="btn btn-warning btn-sm edit-btn" data-toggle="modal" data-target="#editModal" data-id="${row.sub_id}">Edit</button>

                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.sub_id}">Delete</button>
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
            url: url+"api-subject/delete-subject.php",
            data: JSON.stringify(data),
            dataType: "json",
            success: function (response) {
                if(response.success){
                    Swal.fire({
                        title: 'Success!',
                        text: 'Subject deleted successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#exampleModal').modal('hide');  
                        $('#addSubjectForm')[0].reset();  
                        dataTable.ajax.reload();  
                    });
                    dataTable.ajax.reload();
                } 
            }
        });        
    });
    
    $(document).on("submit", "#editBookForm", function (e) {
        e.preventDefault();
        
        var formData=new FormData(this);
        formData.append("id", editId);

        var data = {}

        formData.forEach((value, key)=>{
            data[key] = value;
            // console.log(key+" "+value)
        })

        $.ajax({
            type: "PUT",
            url: url+'api-subject/edit-subject.php',
            data: JSON.stringify(data),
            processData: false,
            contentType: false,  
            dataType: "json",
            success: function (response) {
                if(response.success){
                    Swal.fire({
                        title: 'Success!',
                        text: 'Subject updated successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#editModal').modal('hide');  
                        $('#editBookForm')[0].reset();  
                        dataTable.ajax.reload();  
        
                    });
                    $('#editBookForm')[0].reset();
                    dataTable.ajax.reload();
                }   
            }
        });

    });
    
    $(document).on("click", ".edit-btn", function () {
        const subid = $(this).data('id');
        editId = subid; 

        $('#editModal').modal('show');
        $.ajax({
            type: 'GET',
            url: url+'api-subject/get-subject.php',
            data: {id:subid},
            dataType: 'json',
            success: function(response) {
                $("#edit_sub_name").val(response.data.sub_name);
                $("#edit_sub_desc").val(response.data.sub_desc);
                $("#edit_year_level").val(response.data.year_level);
                $("#edit_acad_year").val(response.data.acad_year);
                $("#edit_semester").val(response.data.semester);
                $("#edit_course").val(response.data.course);
            },
            error: function(error) {
                console.error("Error fetching subject details:", error);
                alert("Error fetching subject details. Please try again.");
            }
        });
    });

    $(document).on("submit", "#addSubjectForm", function (e) {
        e.preventDefault();
        
        var formData=new FormData(this);
        formData.forEach((value, key)=>{
            console.log(key+" "+value)
        })

        $.ajax({
            type: "POST",
            url: url+'api-subject/add-subject.php',
            data: formData,
            processData: false,
            contentType: false,  
            dataType: "json",
            success: function (response) {
                if(response.success){
                    Swal.fire({
                        title: 'Success!',
                        text: 'Subject added successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#exampleModal').modal('hide');  
                        $('#addSubjectForm')[0].reset();  
                        dataTable.ajax.reload();  
        
                    });
                    $('#addSubjectForm')[0].reset();
                    dataTable.ajax.reload();
                }   
            }
        });

    });
});