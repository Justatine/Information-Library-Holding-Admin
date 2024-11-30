$(document).ready(function () {
    var input1 = document.querySelector('#edit_subjects');
    var input = document.querySelector('#subjects');
    var tagify1 = new Tagify(input1);
    var tagify = new Tagify(input);

    var input2 = document.querySelector('#authors');
    var input3 = document.querySelector('#edit_authors');   

    var tagify2 = new Tagify(input2);   
    var tagify3 = new Tagify(input3);   

    function fetchAuthors() {
        $.ajax({
            url: url+'back-end/api-author/authors.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                // console.log(data.data)
                let authors = data.data.map(author => ({ value: author.fname+' - '+author.lname, author_id: author.author_id }));
                tagify2.settings.whitelist = authors;
                tagify3.settings.whitelist = authors;
            },
            error: function (error) {
                console.error('Error fetching author data:', error);
            }
        });
    }

    function loadSubjects() {
        $.ajax({
            url: url+'back-end/api-subject/?p=v1/subjects',
            // url: url+'back-end/api-subject/get-subject.php',
            //url: "http://localhost/ilibrary/admin-side/back-end/api-subject/v1/subjects",  // Your API endpoint to get subjects
            method: 'GET',
            success: function (data) {
                // Assuming the response is an array of subjects
                let subjects = data.map(subject => ({ value: subject.sub_name+' - '+subject.course, sub_id: subject.sub_id }));
                
                // Set the suggestions for Tagify
                tagify.settings.whitelist = subjects;
                tagify1.settings.whitelist = subjects;
            },
            error: function (xhr, status, error) {
                console.log('Error fetching subjects: ', error);
            }
        });
    }

    // Call the function to load subjects
    loadSubjects();
    fetchAuthors();
});
