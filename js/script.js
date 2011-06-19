$(document).ready(function() {

	var sessionId = '28757622dbf26a5a7599c2d21323765662f1d436';
	var token = 'devtoken';
	var apiKey = '413302';
	
	var session = TB.initSession(sessionId);
	
	var streamCount = 0;
	
	// TB.setLogLevel(TB.DEBUG);
	
	session.addEventListener('sessionConnected', sessionConnectedHandler);
	session.addEventListener('streamCreated', streamCreatedHandler);
	session.addEventListener('streamDestroyed', streamDestroyedHandler);
	
	session.connect(apiKey, token);
	
	function sessionConnectedHandler(event) {
		subscribeToStreams(event.streams);
	}
	
	function streamCreatedHandler(event) {
		subscribeToStreams(event.streams);
	}
	
	function streamDestroyedHandler(event) {
		streamCount--;
	}
	
	function subscribeToStreams(streams) {
		for (var i = 0; i < streams.length; i++) {
			var stream = streams[i];
			
			var div = document.createElement('div');
			div.setAttribute('id', 'stream-' + stream.streamid);
			
			switch (streamCount) {
				case 0:
					$('#currentPickSubscriber').append(div);
					break;
				case 1:
					$('#onDeckSubscriber').append(div);
					break;
				default:
					$('body').append(div);
					break;
			}
			
			session.subscribe(stream, div.id, { width: 75, height: 75 });
			
			streamCount++;
		}
	}
	
});