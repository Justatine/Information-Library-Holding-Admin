/*------------------------------------------------------------------
* Bootstrap Simple Admin Template
* Version: 3.0
* Author: Alexis Luna
* Website: https://github.com/alexis-luna/bootstrap-simple-admin-template
-------------------------------------------------------------------*/
(function() {
    'use strict';

    // Toggle sidebar on Menu button click
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#body').toggleClass('active');
    });

    // Auto-hide sidebar on window resize if window size is small
    // $(window).on('resize', function () {
    //     if ($(window).width() <= 768) {
    //         $('#sidebar, #body').addClass('active');
    //     }
    // });
})();

// Call the function when the document is ready
$(document).ready(function() {
    fetchAuthorCount();
    fetchSubjectCount();
    fetchHoldingsCount();
    
    $('#logout').click(function (event) {
        event.preventDefault(); // Prevent the default anchor behavior
            // Function to delete all cookies
        function deleteAllCookies() {
            const cookies = document.cookie.split(";");

            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i];
                const eqPos = cookie.indexOf("=");
                const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                // Set the cookie's expiration date to the past
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
            }
        }

        deleteAllCookies(); // Call the function to delete cookies
        window.location.replace('index.html');
    });
});

function fetchAuthorCount() {
    $.ajax({
        url: 'https://ilibrary.zreky.muccs.host/back-end/api-author/v1/authors/count', // Replace with your actual API endpoint
        method: 'GET',
        success: function(response) {
            $('#authorcounts').text(response.count); // Update the author count in the DOM
        },
        error: function() {
            console.error("Failed to fetch the author count.");
        }
    });
}
function fetchSubjectCount() {
    $.ajax({
        url: 'https://ilibrary.zreky.muccs.host/back-end/api-subject/v1/subjects/count', // Replace with your actual API endpoint
        method: 'GET',
        success: function(response) {
            $('#subjectcounts').text(response.count); // Update the author count in the DOM
        },
        error: function() {
            console.error("Failed to fetch the author count.");
        }
    });
}
function fetchHoldingsCount() {
    $.ajax({
        url: 'https://ilibrary.zreky.muccs.host/back-end/api-holding/v1/holdings/count', // Replace with your actual API endpoint
        method: 'GET',
        success: function(response) {
            $('#holdingcounts').text(response.count); // Update the author count in the DOM
        },
        error: function() {
            console.error("Failed to fetch the author count.");
        }
    });
}

