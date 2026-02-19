var lteurl='http://172.20.168.1/restful/';
var refresh=5000;

function makeTable(table, data) {
    title = table.id.substring(table.id.search('/')+1, table.id.length);
    $(table).dataTable({
        "data": data,
        "columns": [{ "title": title}, null]
    });
    $('thead th:first-child').attr('colspan',2);
    $('thead th:last-child').hide();
}

function load(url, table) {
    $.ajax({ 
        type: "GET",
        dataType: "json",
        url: url+table.id,
        success: function(data){
            if (data.Status == "fail")
                return;
            if (data.Result.hasOwnProperty('data'))
            {
                makeTable(table, data.Result.data.flatMap(obj => [["-", "-"], ...Object.entries(obj)]));
            } else {
                makeTable(table, Object.entries(data.Result));
            }
        }
    });
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
        load(lteurl, table);
    });

    setInterval(function() {
        $('.table.refresh').each(function(index, table) {
            load(lteurl, table);
        });
    }, refresh);
});
