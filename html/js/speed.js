function speedtest(type) {
    var ul = $.ajax({
        url: 'speed.php?test='+type,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            setVal(type, response.value);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            setVal(type, 'n/a');
        }
    });
}

const setVal = (id, val) => $(`#val-${id}`).text(val);
$('#st-btn').on('click', async function () {
    $(this).prop('disabled', true).text('Running speed test…');
    setVal('ping', '…'); 
    setVal('dl', '…'); 
    setVal('ul', '…');

    var ping = speedtest('ping');
    var dl = speedtest('dl');
    var ul = speedtest('ul');

    $.when(ping, dl, ul)
        .done(function() {
            $('#st-btn').prop('disabled', false).text('Restart test');
        })
        .fail(function() {
            $('#st-btn').prop('disabled', false).text('Restart test');
        });
});
