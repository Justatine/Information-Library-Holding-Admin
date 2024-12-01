$(document).ready(function () {

    // Function to fetch authors and populate the select dropdown
    function fetchAuthors() {
        $.ajax({
            url: url+'api-author/?p=v1/authors',
            //url: 'http://localhost/ilibrary/admin-side/api-author/v1/authors',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                const authorSelect = $('#auth');
                const authorSelect1 = $('#auth1');

                authorSelect.empty();
                authorSelect.append('<option value="">Select Author</option>');

                authorSelect1.empty();
                authorSelect1.append('<option value="">Select Author</option>');

                // Populate authors
                $.each(data, function (index, author) {
                    authorSelect.append('<option value="' + author.author_id + '">' + author.fname + ' ' + author.lname + '</option>');
                    authorSelect1.append('<option value="' + author.author_id + '">' + author.fname + ' ' + author.lname + '</option>');
                });

            },
            error: function (error) {
                console.error('Error fetching author data:', error);
            }
        });
    }

    // Initial function calls to fetch authors and publishers
    fetchAuthors();
});
