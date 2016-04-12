var conn = new WebSocket('ws://rpi.lan:8080');
conn.onopen = function(e) {
    console.log("Connection established!");
};

conn.onmessage = function(e) {
    var data = jQuery.parseJSON(e.data);
    var newClass = (data.state == 'on') ? 'btn-danger' : 'btn-success';
    var oldClass = (data.state == 'on') ? 'btn-success' : 'btn-danger';
    var text = (data.state == 'on') ? 'Eteindre' : 'Allumer';
    $("#pin" + data.pin).removeClass(oldClass);
    $("#pin" + data.pin).addClass(newClass);
    $("#pin" + data.pin).html(text);
};

function sendTooglePinRequest(pin) {
    conn.send(pin);
}