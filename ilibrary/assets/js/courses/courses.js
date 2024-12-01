$(document).ready(function () {
    getCourses();   
});

function getCourses(){
    $.ajax({
        type: "GET",
        url: url+"api-courses/courses.php",
        dataType: "json",
        success: function (response) {
            // console.log(response);  
            $.map(response.data, function (element,index) {
                $("#course").append("<option value='"+element.course+"'>"+element.course+"</option>");  
                $("#edit_course").append("<option value='"+element.course+"'>"+element.course+"</option>");  
            });
        }
    });
}