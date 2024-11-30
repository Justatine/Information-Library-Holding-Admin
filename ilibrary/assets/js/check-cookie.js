function checkCookieAndRedirect(cookieName, loginPageURL) {
    var cookieValue = getCookie(cookieName);
    if (!cookieValue) {
        // Redirect to login page if the cookie is not present
        window.location.href = loginPageURL;
    }
}

checkCookieAndRedirect("cookie-adminId", "index.html");