<?php

class Team {
		
	public $leagueid;
	public $name;
	public $draft_pos;
	public $isCommish;
	
	public function __construct($leagueid, $name, $draft_pos, $isCommish = 0)
	  {
		$this->leagueid = $leagueid;
		$this->name = $name;
		$this->draft_pos = $draft_pos;
		$this->isCommish  = $isCommish;
	  }
	  
	 public static function getTeamByID($id){
		return $player = getDatabase()->one('SELECT * FROM teams WHERE teamID=:id', array(':id' => $id));
	 }
	 
	 public static function getTeamByLeague($id){
		return $players = getDatabase()->all('SELECT * FROM teams WHERE leagueID =:id', array(':id' => $id));
	 }
	 
	 public function insertLeauge(){
		//$playerid = getDatabase()->execute('INSERT INTO league VALUE('.$this->id.','.$this->name')');
	 }
	 
	 public function updateLeauge(){
		$playerid = getDatabase()->execute('UPDATE league SET name = '.$this->name.'WHERE leagueID = '.$this->id);
	 }
	 
}

?>