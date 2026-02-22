var config = {};

$(document).ready(function() {
    $.ajax({
        url: 'config.php',
        method: 'GET',
        dataType: 'json',
        async: false,
        success: function(response) {
            config = response;
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching the configuration:', textStatus, errorThrown);
        }
    });
});
