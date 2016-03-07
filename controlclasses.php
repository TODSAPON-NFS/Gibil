<?php

class Message {
	public $message;
	public $echolog;

	function Message ($m, $l) {
		$this->message = $m;
		$this->echolog = $l;
		return $this;
	}
}

class Event {
	public $account;
	public $zone;
	public $status;
	public $timestamp;
	public $message;
	
	function Event($message){
		$d = substr($message,1,2);
		$mon = substr($message,3,2);
		$y = substr($message,5,2);
		$h = substr($message,7,2);
		$min = substr($message,9,2);
		$s = substr($message,11,2);

		$datestr = "20".$y."-".$d."-".$mon." ".$h.":".$min.":".$s;
		$this->timestamp = \DateTime::createFromFormat('Y-d-m H:i:s',$datestr);
		if (! $this->timestamp) {
		    echo sprintf("'%s' is not a valid date.", $datestr);
		    return null;
		}	
		$this->account = substr($message,13,4);
		$this->zone = substr($message,17,1);
		$this->status = substr($message,18,1);
		$this->message = substr($message,0,21);
		return $this;
	}
		
	function String() {
		return sprintf("[Account :%s, Zone :%s, Status :%s, Timestamp %s, Message %s]",$this->account,$this->zone,$this->status,$this->timestamp->format(DateTime::RFC2822), $this->message);
	}
}

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

	function getZoneTimestamp($zone){
		switch ($zone){
			case "1":
				return $this->at;
			case "2":
				return $this->st;
			case "3":
				return $this->tt;
			case "4":
				return $this->pt;
			case "A":
				return $this->awt;
			case "B":
				return $this->swt;
			case "C":
				return $this->twt;
			case "D":
				return $this->pwt;
		}
		return "";
	}

	//construct a new panel in the off state
	function Panel($account){
		//Set the default time to UNIX EPOCH
		$epochTime = new DateTime();
		$epochTime->setTimestamp(0);
		$stamp = $epochTime->format(DateTime::RFC2822);
		
		$this->account = $account;
		$this->as = "1";
		$this->at = $stamp;
		$this->ss = "1";
		$this->st = $stamp;
		$this->ts = "1";
		$this->tt = $stamp;
		$this->ps = "1";
		$this->pt = $stamp;
		$this->aws = "1";
		$this->awt = $stamp;
		$this->sws = "1";
		$this->swt = $stamp;
		$this->tws = "1";
		$this->twt = $stamp;
		$this->pws = "1";
		$this->pwt = $stamp;
		$this->timestamp = $stamp;
		$this->message = "";
		//no timestamps in the default
		return $this;
	}

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
