var lteurl='http://172.20.168.1/restful/';
var refresh=5000;

function makeTable(table) {
    title = table.id.substring(table.id.search('/')+1, table.id.length);
    $(table).DataTable({
        ajax: {
            url: lteurl + table.id,
            dataSrc: function(data) {
                if (data.Result.hasOwnProperty('data')) {
                    return data.Result.data.flatMap(obj => [["-", "-"], ...Object.entries(obj)]);
                } else {
                    return Object.entries(data.Result);
                }
            }
        },
        columns: [{ title: title }, null],
        searching: false,
        ordering: false,
        paging: false,
        info: false,
        destroy: true
    });
    $('thead th:first-child').attr('colspan',2);
    $('thead th:last-child').hide();
}

$(document).ready(function() {
    $.extend($.fn.dataTable.defaults, {
        searching: false,
        ordering:  false,
        paging: false,
        info: false,
        destroy: true
    });

    $('.table').each(function(index, table) {
        makeTable(table);
    });

    setInterval(function() {
        $('.table.refresh').each(function(index, table) {
            const dataTable = $(table).DataTable();
            dataTable.ajax.url(lteurl + table.id).load();
        });
    }, refresh);
});
