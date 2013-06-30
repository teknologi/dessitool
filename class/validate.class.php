<?php
class validate {

	/*  Validate email address */
	public static function email($vmail) {
		return (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $vmail));
	}

	public static function length_between($str,$min,$max,$include=true) {
		$str_len=mb_strlen($str, 'UTF-8');
		if(($include&&$str_len>=$min&&$str_len<=$max)||(!$include&&$str_len>$min&&$str_len<$max)) return true;
		else return false;
	}

	public static function username($str) {
		return preg_match('/^[a-zA-Z0-9_-]+$/', $str);
	}
}

?>