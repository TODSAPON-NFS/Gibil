<?php


/*
*/

/* controlclasses.php contains the class files for objects used by the gibil serial controller. Most of these are wrappers for DB objects or information read from the serial port. The classes are as follows:

	*Message
	*Event
	*Panel
*/

/* Message is a wraper for data being send over the serial port to the aeu */
class Message {
	public $message;
	public $echolog;
	
	/* Constructor
	@param m (string) The message being send to the AEU
	@param l (string) the message to be output to the log file when the message is sent
	*/
	function Message ($m, $l) {
		$this->message = $m;
		$this->echolog = $l;
		return $this;
	}
}

/* Event is a wraper for data received from the AEU */
class Event {
	public $account;	//The account number of the panel [4001 - 7100]
	public $zone;		//The panel zone [1234ABCD]
	public $status;		//panel status 0=none, 1=ok, 2=alarm
	public $timestamp;	//datetime object of the timestamp
	public $message;	//The actual message received from the AEU
	
	/* constructor
	@param message, is the message received from the AEU 
		if message == ""then an event with no set values is returned

	The message format is DDMMYYhhmmssAAAAscs\r 
	for more info see AEU documentation.
	*/
	function Event($message = ""){
		if ($message == "") {
			return $this;
		}
		//Parse the date from the message
		$mon = substr($message,1,2);
		$d = substr($message,3,2);
		$y = substr($message,5,2);
		$h = substr($message,7,2);
		$min = substr($message,9,2);
		$s = substr($message,11,2);
		//build timesamp assuming the year is 20XX
		$datestr = "20".$y."-".$d."-".$mon." ".$h.":".$min.":".$s;
		$this->timestamp = \DateTime::createFromFormat('Y-d-m H:i:s',$datestr);
		if (! $this->timestamp) {
		    out( sprintf("'%s' is not a valid date.", $datestr));
		    return null;
		}	
		//parse account, zone, status, and message (removing the \r)
		$this->account = substr($message,13,4);
		$this->zone = substr($message,17,1);
		$this->status = substr($message,18,1);
		$this->message = substr($message,0,21);
		return $this;
	}
		
	/* Returns a string of the event */
	function String() {
		return sprintf("Account :%s, Zone :%s, Status :%s, Timestamp %s, Message %s",$this->account,$this->zone,$this->status,$this->timestamp->format(DateTime::RFC2822), $this->message);
	}
}

/* Panel is a wrapper for the panels in the DB, a panel has 8 zones, each one contains a timestamp from when its last event was received */
class Panel {
	public $account;	//account number
	public $as;		//alarm status
	public $at;		//alarm timestamp
	public $aws;		//alarm wiring status
	public $awt;		//alarm wiring timestamp
	public $ss;		//supervisory status
	public $st;		//supervisory timestamp
	public $sws;		//supervisory wiring status
	public $swt;		//supervisory wiring timestamp
	public $ts;		//trouble status
	public $tt;		//trouble timestamp
	public $tws;		//trouble wiring status
	public $twt;		//trouble wiring timestamp
	public $ps;		//power status
	public $pt;		//power timestamp
	public $pws;		//power wiring status
	public $pwt;		//power wiring timestamp
	public $timestamp;	//most recent timestamp
	public $message;	//message for aux usage
	
	//String representation of a panel
	function String() {
		return sprintf(
			"account :%s
			as  :%s \tat  :%s 
			aws :%s \tawt :%s
			ss  :%s \tst  :%s 
			sws :%s \tswt :%s
			ts  :%s \ttt  :%s 
			tws :%s \ttwt :%s
			ps  :%s \tpt  :%s 
			pws :%s \tpwt :%s
			timestamp :%s message %s",
			$this->account, 
			$this->as, $this->at, 
			$this->aws, $this->awt, 
			$this->ss, $this->st, 
			$this->sws, $this->swt, 
			$this->ts, $this->tt, 
			$this->tws, $this->twt, 
			$this->ps, $this->pt, 
			$this->pws, $this->pwt, 
			$this->timestamp,$this->message);
	}

	/* getEvent reconstructs the last event message receved based on a zone
	@param zone the zone being queried
	@return the last event for the suppiled zone. 
		If the zone is not in the range [1234ABCD] null is returned
	*/
	function getEvent($zone){
		$event = new Event();
		$event->$zone = $zone;
		$event->account = $this->account;
		switch ($zone){
			case "1":
				$event->timestamp =  $this->at;
				$event->status = $this->as;
				break;
			case "2":
				$event->timestamp =  $this->st;
				$event->status = $this->ss;
				break;
			case "3":
				$event->timestamp =  $this->tt;
				$event->status = $this->ts;
				break;
			case "4":
				$event->timestamp =  $this->pt;
				$event->status = $this->ps;
				break;
			case "A":
				$event->timestamp =  $this->awt;
				$event->status = $this->aws;
				break;
			case "B":
				$event->timestamp =  $this->swt;
				$event->status = $this->sws;
				break;
			case "C":
				$event->timestamp =  $this->twt;
				$event->status = $this->tws;
				break;
			case "D":
				$event->timestamp =  $this->pwt;
				$event->status = $this->pws;
				break;
			default:
				$event = null;
				break;

		}
		$event->timestamp = date_create($event->timestamp);
		return $event;
	}

	/*construct a new panel in the off state, the timestamp for 
		each zone is set to unix epoch time ~1970, and
		the default message is set to ""
	*/
	function Panel($account){
		//Set the default time to UNIX EPOCH
		$epochTime = new DateTime();
		$epochTime->setTimestamp(0);
		$stamp = $epochTime->format(DateTime::RFC2822);
		
		$this->account = $account;
		$this->as = "0";
		$this->at = $stamp;
		$this->ss = "0";
		$this->st = $stamp;
		$this->ts = "0";
		$this->tt = $stamp;
		$this->ps = "0";
		$this->pt = $stamp;
		$this->aws = "0";
		$this->awt = $stamp;
		$this->sws = "0";
		$this->swt = $stamp;
		$this->tws = "0";
		$this->twt = $stamp;
		$this->pws = "0";
		$this->pwt = $stamp;
		$this->timestamp = $stamp;
		$this->message = "";
		//no timestamps in the default
		return $this;
	}
	/*Equals is the equivelece checker
		if all feilds are equal, return true
		else return false
		@param panel the panel being checked for equivilence with this
	*/
	function Equals($panel){
		if(
			$this->account == $panel->account &&
			$this->as == $panel->as &&
			$this->at == $panel->at &&
			$this->ss == $panel->ss &&
			$this->st == $panel->st &&
			$this->ts == $panel->ts &&
			$this->tt == $panel->tt &&
			$this->ps == $panel->ps &&
			$this->pt == $panel->pt &&
			$this->aws == $panel->aws &&
			$this->awt == $panel->awt &&
			$this->sws == $panel->sws &&
			$this->swt == $panel->swt &&
			$this->tws == $panel->tws &&
			$this->twt == $panel->twt &&
			$this->pws == $panel->pws &&
			$this->pwt == $panel->pwt &&
			$this->timestamp == $panel->timestamp &&
			$this->message == $panel->message
		) {
			return true;
		}
		return false;
	}
	/* Update merges an event with a panel, the panels timestamp is made
		equal to the most recent events, along with the message
		@param event an event to update the panel
	*/
	function Update($event){
		//update timestamp will have to be distributed later
		$this->timestamp = $event->timestamp->format(DateTime::RFC2822);
		$this->message = $event->message;
		switch ($event->zone){
			case "1":
				$this->as = $event->status;
				$this->at = $this->timestamp;
				break;
			case "2":
				$this->ss = $event->status;
				$this->st = $this->timestamp;
				break;
			case "3":
				$this->ts = $event->status;
				$this->tt = $this->timestamp;
				break;
			case "4":
				$this->ps = $event->status;
				$this->pt = $this->timestamp;
				break;
			case "A":
				$this->aws = $event->status;
				$this->awt = $this->timestamp;
				break;
			case "B":
				$this->sws = $event->status;
				$this->swt = $this->timestamp;
				break;
			case "C":
				$this->tws = $event->status;
				$this->twt = $this->timestamp;
				break;
			case "D":
				$this->pws = $event->status;
				$this->pwt = $this->timestamp;
				break;
			default:
		}

	}
}

?>
