<?php 
include_once 'src/Epi.php';
include_once 'classes/class.players.php';
include_once 'classes/class.league.php';
include_once 'classes/class.draftselection.php';
include_once 'classes/class.statuscodes.php';
require('lib/Pusher.php');

Epi::setPath('base', 'src');
Epi::init('api','database');
EpiDatabase::employ('mysql','draftapp','localhost','root','');

Epi::init('api');
getApi()->get('/ImportPlayers.xml', array('Site', 'ImportPlayers'), EpiApi::external);
getApi()->get('/players.json', array('API', 'getAllPlayers'), EpiApi::external);
getApi()->get('/players/(\d+).json', array('API', 'getPlayer'), EpiApi::external);
getApi()->get('/teams/(\d+)/getPlayers.json', array('API', 'getTeamPlayers'), EpiApi::external);
getApi()->get('/teams/(\d+)/draftPlayer.json', array('API', 'draftPlayer'), EpiApi::external);
getApi()->get('/league/(\d+)/getDraftPicks.json', array('API', 'getAllDraftPicks'), EpiApi::external);
getApi()->get('/league/(\d+)/getTeams.json', array('API', 'getTeams'), EpiApi::external);
getRoute()->get('/', array('Site', 'home'));
getRoute()->run();

class Site {

	static public function home(){
		$pusher = new Pusher('3622b085f13686b6ab57', '3622b085f13686b6ab57', '6065');
		$pusher->trigger('my-channel', 'my_event', 'hello world');
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
		
		$players = getDatabase()->all('SELECT * FROM players');
		header('Content-Type: application/json');
		echo json_encode($players);	
	}
	
	static public function getPlayer($id){
		
		$player = Player::getPlayerByID($id);

		header('Content-Type: application/json');
		echo json_encode($player);	
		
	}
	
	static public function getTeamPlayers($id){
		
		$players = Player::getPlayerByTeam($id);
		
		header('Content-Type: application/json');
		echo json_encode($players);	
		
	}
	
	static public function draftPlayer($id){
		
		$playerid = $_GET['player_id'];
		$leagueid = $_GET['league_id'];
		$timestamp = date("Y-m-d H:i:s", time());
		$round = $_GET['round'];
		$slot = $_GET['slot'];
			
		if(Player::isPlayerAvailable($playerid, $leagueid)){
			$draftPick = new DraftSelection($id, $playerid, $leagueid, $timestamp, $round, $slot);
			$pickid = $draftPick->insertDraftSelection();
			
			$pusher = new Pusher('3622b085f13686b6ab57', '3622b085f13686b6ab57', '6065');
			$pusher->trigger('draftroom-'.$leagueid, 'drafted', 'hello world', true);
		}else{
			echo "Player taken";
		}		
	}
	
	static public function getAllDraftPicks($id){
		
		$players = Player::getPlayerByLeague($id);
		header('Content-Type: application/json');
		echo json_encode($players);	
	}
	
	static public function getTeams($id){


	}
	
	
}
?>