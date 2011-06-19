<?php 
include_once 'src/Epi.php';
include_once 'classes/class.players.php';
include_once 'classes/class.league.php';
include_once 'classes/class.teams.php';
include_once 'classes/class.draftselection.php';
include_once 'classes/class.statuscodes.php';

require('lib/Pusher.php');

Epi::setPath('base', 'src');
Epi::init('route', 'api','database');
//EpiDatabase::employ('mysql','draftapp','localhost','draftapp_db','swdfw');
EpiDatabase::employ('mysql','draftapp','localhost','root','root');

getApi()->get('/players.json', array('API', 'getAllPlayers'), EpiApi::external);
getApi()->get('/players/(\d+).json', array('API', 'getPlayer'), EpiApi::external);
getApi()->get('/players/(\d+)/getAvailability.json', array('API', 'getAvailability'), EpiApi::external);
getApi()->get('/teams/(\d+)/getPlayers.json', array('API', 'getTeamPlayers'), EpiApi::external);
getApi()->post('/teams/(\d+)/draftPlayer.json', array('API', 'draftPlayer'), EpiApi::external);
getApi()->get('/league/(\d+)/getDraftPicks.json', array('API', 'getAllDraftPicks'), EpiApi::external);
getApi()->get('/league/(\d+)/getTeams.json', array('API', 'getTeams'));
getRoute()->get('/ImportPlayers.xml', array('Site', 'ImportPlayers'));
getRoute()->get('/', array('Site', 'home'));
getRoute()->run();

class Site {

	static public function home(){
		
		$players = getApi()->invoke('/players.json');	
		
		foreach($players as $player){
			echo $player["name"];
			exit;
		}
		
	}
	
	static public function ImportPlayers(){
		$url = "http://football.myfantasyleague.com/2011/export?TYPE=players";  

		$ch = curl_init($url);  

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_FAILONERROR,true);

		$output = curl_exec($ch);  

		curl_close($ch);  

		$players = new SimpleXmlElement($output);
		$pos = array("Def", "QB", "WR", "TE", "RB", "PK");
		foreach($players as $player){
			$status = ($player['status'] != '') ? $player['status']: null;
			$myPlayer = new Player($player['id'], $player['name'], $player['position'], $player['team'], $status);
			if(in_array($myPlayer->position, $pos)){
				$myPlayer->insertPlayer();
			}			
		}		
	}
}

class API {

	static public function getAllPlayers(){
		
		
		$players = getDatabase()->all('SELECT * FROM Players');
		return $players;
	}
	
	static public function getPlayer($id){
		
		$player = Player::getPlayerByID($id);

		return $player;	
		
	}
	static public function getAvailability($id){
		
		$leagueid = $_GET["league_id"];
		
		return Player::isPlayerAvailable($id, $leagueid);
	
	}
	
	static public function getTeamPlayers($id){
		
		$players = Player::getPlayerByTeam($id);
		
		return $players;	
		
	}
	
	static public function draftPlayer($id){
		
		$playerid = $_POST["player_id"];
		$leagueid = $_POST["league_id"];
		$timestamp = date("Y-m-d H:i:s", time());
		$round = $_POST["round"];
		$slot = $_POST["slot"];
		
		$StatusCode = new StatusCodes();

		if(Player::isPlayerAvailable($playerid, $leagueid)){
			$draftPick = new DraftSelection($id, $playerid, $leagueid, $timestamp, $round, $slot);
			$pickid = $draftPick->insertDraftSelection();
			header($StatusCode->httpHeaderFor('200'));
			$pickInfo = DraftSelection::getDraftSelection($pickid);
			$pusher = new Pusher('8aab2b64a30d6d627644', '3622b085f13686b6ab57', '6065');
			$pusher->trigger('draftroom-'.$leagueid, 'PlayerDrafted', $pickInfo);
		}else{
			header('Content-Type: application/json');
			header($StatusCode->httpHeaderFor('401'));
			echo json_encode(array('error' => 'Player taken.'));
			exit;
		}		
	}
	
	static public function getAllDraftPicks($id){
		
		$players = Player::getPlayerByLeague($id);
		return $players;	
	}
	
	static public function getTeams($id){
		$teams = Team::getTeamByLeague($id);
		return $teams;	
	}
	
	
}
?>