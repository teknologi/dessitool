<?php
class db extends mysqli {

	public function __construct($host='localhost',$user=null,$pass=null,$db=null,$port=null,$socket=null) {
		parent::mysqli($host,$user,$pass,$db,$port,$socket);
	}

	public function query($sql) {
		return parent::query($sql);
	}

}
