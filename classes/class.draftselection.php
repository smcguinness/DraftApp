<?php

class DraftSelection {
	
	protected $teamid;
	protected $playerid;
	protected $leagueid;
	protected $timestamp;
	protected $round;
	protected $slot;
	
	public function __construct($teamid, $playerid, $leagueid, $timestamp, $round, $slot)
	  {
		$this->teamid = $teamid;
		$this->playerid = $playerid;
		$this->leagueid = $leagueid;
		$this->timestamp = $timestamp;
		$this->round = $round;
		$this->slot = $slot;
		
	  }
	  
	 public static function getDraftSelectionByID($id){
		return $player = getDatabase()->one('SELECT * FROM teamplayers WHERE pickID=:id', array(':id' => $id));
	 }
	 
	 public static function getPlayerByTeam($id){
		return $players = getDatabase()->all('SELECT * FROM teamplayers WHERE teamID=:id', array(':id' => $id));
	 }
	 
	 public function insertDraftSelection(){
		return $pickid = getDatabase()->execute('INSERT INTO teamplayers (teamID,playerID,LeagueID,picktimestamp,round,slot) VALUES('.$this->teamid.','.$this->playerid.','.$this->leagueid.',\''.$this->timestamp.'\','.$this->round.','.$this->slot.')');
	 }
	 
	 public function getDraftSelection($id){
		return $DraftPick = getDatabase()->one('SELECT * FROM teamplayers JOIN players ON players.playerid = teamplayers.playerid WHERE pickID = '.$id);
	 }
	 
}

?>