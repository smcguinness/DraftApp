<?php

class Leauge {
	
	protected $id;
	protected $name;
	
	public function __construct($id, $name)
	  {
		$this->id = $id;
		$this->name = $name;
	  }
	  
	 public static function getLeaugeByID($id){
		return $player = getDatabase()->one('SELECT * FROM league WHERE leagueID=:id', array(':id' => $id));
	 }
	 
	 public static function getPlayerByTeam($id){
		return $players = getDatabase()->all('SELECT * FROM teamplayers JOIN players ON players.playerID = teamplayers.playerID WHERE teamplayers.teamID = '.$id);
	 }
	 
	 public function insertLeauge(){
		//$playerid = getDatabase()->execute('INSERT INTO league VALUE('.$this->id.','.$this->name')');
	 }
	 
	 public function updateLeauge(){
		$playerid = getDatabase()->execute('UPDATE league SET name = '.$this->name.'WHERE leagueID = '.$this->id);
	 }
	 
}

?>