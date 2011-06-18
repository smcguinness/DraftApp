<?php

class Player {
	
	public $id;
	public $name;
	public $position;
	public $team;
	public $status;
	
	public function __construct($id, $name, $position, $team, $status = null)
	  {
		$this->id = $id;
		$this->name = $name;
		$this->position = $position;
		$this->team = $team;
		$this->status = $status;
	  }
	  
	 public static function getPlayerByID($id){
		return $player = getDatabase()->one('SELECT * FROM players WHERE playerID=:id', array(':id' => $id));
	 }
	 
	 public static function getPlayerByName($name){
		return $players = getDatabase()->all('SELECT * FROM players WHERE playerID LIKE \'%'.$name.'%\'');
	 }
	 
	 public static function getPlayerByTeam($id){
		return $players = getDatabase()->all('SELECT * FROM teamplayers JOIN players ON players.playerID = teamplayers.playerID WHERE teamplayers.teamID = '.$id);
	 }
	 
	 public static function getPlayerByLeague($id){
		return $players = getDatabase()->all('SELECT * FROM teamplayers JOIN players ON players.playerID = teamplayers.playerID WHERE teamplayers.leagueID = '.$id);
	 }
	 
	 public function insertPlayer(){
		$playerid = getDatabase()->execute('INSERT INTO players VALUE(:id,:name,:position,:team,:status)', array(':id' => $this->id, ':name' => $this->name, ':position' => $this->position, ':team' => $this->team, ':status' => $this->status));
	 }
	 
	 public function updatePlayer(){
		$playerid = getDatabase()->execute('UPDATE players SET playerID = '.$this->id.', name = '.$this->name.', position = '.$this->position.', team = '.$this->team.','.$this->status.'WHERE playerID = '.$this->id);
	 }
	 
	 public static function isPlayerAvailable($playerid, $leagueid){

		$player = getDatabase()->one('SELECT * FROM teamplayers WHERE playerID = '.$playerid.' AND leagueID = '.$leagueid);
		
		if($player){
			return false;
		}else{
			return true;
		}
	 }
	 
}

?>