$(document).ready(function(){
    
    $('#logout-link').click(function (event) {
        event.preventDefault(); // Prevent the default anchor behavior
            // Function to delete all cookies
        console.log('test');
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
        // Clear local storage and session storage
        localStorage.clear();
        sessionStorage.clear();

        // Delete all cookies and redirect to login page
        deleteAllCookies();
        window.location.replace('index.html');
    });
});