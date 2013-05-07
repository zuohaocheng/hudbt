var ws,
connected = false,
ports = [],
failCount = 0,
init = function() {
    ws = new WebSocket(self.name + "/ws/pm");
    ws.onopen = function() {
	connected = true;
    };
    ws.onclose = function() {
	connected = false;
	++failCount;
	if (failCount < 5) {
	    setTimeout(init, 5000);
	}
    };
    ws.onmessage = function(e) {
	var i;
	for (i=0; i<ports.length; ++i) {
	    ports[i].postMessage("done: "+ e.data);
	}
    }
};
init();

self.addEventListener("connect", function(e) {
    var port = e.ports[0];
    ports.push(port);
    port.start();
}, false);
