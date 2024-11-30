$(document).ready(function(){
    $("#loginForm").submit(function(event){
        event.preventDefault();

        var formData = {
            idnum: $("#idnum").val(),
            password: $("#password").val()
        };

        $.ajax({
            url: `https://ilibrary.zreky.muccs.host/back-end/api-login/v1/admin_acc`,
            //url: "http://localhost/ilibrary/admin-side/back-end/api-login/v1/admin_acc",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response){
                console.log("Response:", response);
                if(response.msg === 'Login successful'){
                    setCookie("cookie-adminId", response.admin_id, 7);
                    alert("Login successful!");
                    window.location.replace('dashboard.html');
                } else {
                    alert(response.msg);
                }
            },
            error: function(xhr, status, error){
                console.error("Error:", error);
                alert("Error occurred while logging in. Please try again.");
            }
        });
    });
});