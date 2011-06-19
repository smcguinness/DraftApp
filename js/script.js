$(document).ready(function() {
	
	
	
	var leagueId = 1;
	var serverPath = '/DraftApp/';
	
	$.getJSON(serverPath + 'league/1/getTeams.json', function(data) {

		var draftRounds = 12; // NOTE: Should be dynamic, static for now

		// Create the draft pick grid
		for (var i = 0; i < draftRounds; i++) {
			var ul = $('<ul />', {
				'class': 'draftRound'
			});
			
			$.each(data, function(key, val) {
				var li = $('<li />', {
					'class': 'draftPick',
					'id': 'round' + (i + 1) + 'team' + val.teamID
				});
				
				$(ul).append(li);
			});
			
			$('#draftRoundHolder').append(ul);
		}
		
		// Create the team row
		$.each(data, function(key, val) {
			console.log('adding list item');
			var li = $('<li />');
			
			var h3 = $('<h3 />', {
				'text': val.name
			});
			
			$(li).append(h3);
			
			// These are the divs for the OpenTok streams to go in
			var div = $('<div />', {
				'id': 'videoContainer-' + val.teamID,
				'class': 'teamPic'
			});
			
			$(li).append(div);
			
			$('#teamList').append(li);
				
		});
		
		// Load the draft picks that have been made already
		$.getJSON(serverPath + 'league/1/getDraftPicks.json', function(data) {
			
		});
		
		
		// Connect to openTok session after divs are created		
		session.connect(apiKey, token);
	});
	
	

	var sessionId = '28757622dbf26a5a7599c2d21323765662f1d436';
	var token = 'devtoken';
	var apiKey = '413302';
	
	var session = TB.initSession(sessionId);
	
	var streamCount = 0;
	
	// TB.setLogLevel(TB.DEBUG);
	
	session.addEventListener('sessionConnected', sessionConnectedHandler);
	session.addEventListener('streamCreated', streamCreatedHandler);
	session.addEventListener('streamDestroyed', streamDestroyedHandler);
	
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
			
			$('#videoContainer-' + stream.name).append(div);
			
			session.subscribe(stream, div.id, { width: 75, height: 75 });
			
			streamCount++;
		}
	}
	
});