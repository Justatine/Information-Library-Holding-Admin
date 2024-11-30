// Initiate datatables in roles, tables, users page
(function() {
    'use strict';
    
    $('#dataTables-example').DataTable({
        responsive: true,
        pageLength: 20,
        lengthChange: false,
        searching: true,
        ajax: {
            url: 'https://ilibrary.zreky.muccs.host/back-end/api-log1/v1/logs',  // Replace with your API endpoint
            dataSrc: ''  // In case the API returns an array of objects
        },
        columns: [
            { data: 'log_id', visible: false },
            { data: 'activity' },  // Activity column
            { data: 'timestamp' }, // Timestamp column
            { data: 'admin_id' }   // Admin ID column
        ],
        ordering: true
    });
})();