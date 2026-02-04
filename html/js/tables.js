var lteurl='http://172.20.168.1/restful/';

function makeTable(table, obj) {
    if (table.id != '') {
        tRow = $('<tr>');
        title = table.id.substring(table.id.search('/')+1, table.id.length);
        tCell = $('<th>').attr('colspan', 2).text(title);
    };
    $(table).append(tRow.append(tCell));
    $.each(obj, function(key, val) {
        tRow = $('<tr>');
        tCell = $('<td>').text(key);
        $(table).append(tRow.append(tCell));
        tCell = $('<td>').text(val);
        $(table).append(tRow.append(tCell));
    });
};

$(document).ready(function () {
    $('.table').each(function(index, element) {
        $.ajax({ 
            type: "GET",
            dataType: "json",
            url: lteurl+element.id,
            success: function(data){
                var ex = false;
                $.each(data.Result, function(key, val) {
                    if (key == "data") {
                        ex = true;
                        $.each(val, function(ikey, ival) {
                            makeTable(element, ival);
                        });
                    };
                });
                if (!ex) {
                    makeTable(element, data.Result);
                };
            }
        });
    });
});
