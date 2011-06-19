$(document).ready(function() {
	
	
	
	var leagueId = 1;
	var serverPath = '/DraftApp/';
	
	var teams;
	var picks;
	var round;
	
	$.getJSON(serverPath + 'league/1/getTeams.json', function(data) {
		
		teams = data;

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
			
			picks = data;
			
			$.each(data, function(key, val) {
				
				addDraftPick(val);
								
			});
			
			updatePickInfo();
		});
		
		
		// Connect to openTok session after divs are created		
		session.connect(apiKey, token);
	});
	
	var pusher = new Pusher('8aab2b64a30d6d627644');
	var channel = pusher.subscribe('draftroom-1');
	pusher.bind('PlayerDrafted', function(data) {
		
		picks.push(data);
		
		addDraftPick(data);
		
		updatePickInfo();
	});
	
	function updatePickInfo() {
				
		var lastPick = picks[picks.length - 1];		

		var onClockPickInfo = getNextTeamID(lastPick.teamID, lastPick.round);
		var onDeckPickInfo = getNextTeamID(onClockPickInfo.teamID, onClockPickInfo.round);
		
		var onClockTeam = teams[onClockPickInfo.teamID - 1];
		var onDeckTeam = teams[onDeckPickInfo.teamID - 1];				
		
		$('#currentPickBox p.teamName').text(onClockTeam.name);
		$('#onDeckBox p.teamName').text(onDeckTeam.name);
		
		$('#round i').text(onClockPickInfo.round);
		$('#pick i').text(onClockPickInfo.pick);
		
		$.getJSON(serverPath + 'teams/' + onDeckTeam.teamID + '/getPlayers.json', function(data) {
			var lastTeamPick = data[data.length - 1];
			if (lastTeamPick) {
				$('#onDeckBox p.lastPick').text(lastTeamPick.name);
			}
		});
		
		// Calculate the pick number in the round
		// $('#round i').text()
		
		function getNextTeamID(teamID, round) {
			
			// If the round ended the last time this function was called, increment the round
			if (this.roundEnd) {
				round++;
			}
			
			var pick;
			
			if ((lastPick.round % 2) == 0) {
				// If this is an even round, the last team to pick is team 1
				if (lastPick.teamID != 1) {
					// If this is not the last pick in the round, decrement the team
					teamID--;
					this.roundEnd = false;
				} else {
					this.roundEnd = true;
				}
				
				pick = teams.length - teamID + 1;
			} else {
				// If this is an odd round, the last team to pick is the last team
				if (lastPick.teamId != teams.length) {
					// If this is not the last pick in the round, increment the team
					teamID++;
					this.roundEnd = false;
				} else {
					this.roundEnd = true;
				}
				
				pick = teamID;
			}
			
			
			
			return { 
				teamID: teamID, 
				round: round,
				pick: pick
			};
		}
	}
	

	
	function addDraftPick(pick) {
		//round1team1
		
		var pickDiv = $('#round' + pick.round + 'team' + pick.teamID);
		
		var playerDiv = $('<div />', {
			'class': 'pick ' + pick.position
		});
		
		var playerName = $('<h3 />', {
			'text': pick.name
		});
		
		var playerPosition = $('<span />', {
			'class': 'playerPosition',
			'text': pick.position
		});
		
		var playerTeam = $('<span />', {
			'class': 'playerTeam',
			'text': pick.team
		});
		
		$(playerDiv).append(playerName);
		$(playerDiv).append(playerPosition);
		$(playerDiv).append(playerTeam);
		
		$(pickDiv).append(playerDiv);
	}
	
	

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
			
			// Must have a name set
			if (stream.name) {
				var div = document.createElement('div');
				div.setAttribute('id', 'stream-' + stream.streamId);

				$('#videoContainer-' + stream.name).append(div);

				session.subscribe(stream, div.id, { width: 75, height: 75 });

				streamCount++;				
			}
		}
	}
	
});