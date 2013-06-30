<?php

define("CHARSET","UTF-8");

if(!defined("SITEHTML"))
	define("SITEHTML",SITEROOT);

define("DB_HOST","host");
define("DB_USER","user");
define("DB_PASS","pass");
define("DB_NAME","name");


define("SITENAME","Dessi");
define("SITEURL","http://".$_SERVER["SERVER_NAME"]."/dessi/");

include_once(SITEHTML."class/db.class.php");
$db = new Db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
$db->query("SET CHARSET utf8");

if(isset($_GET["la"])) {
	$sql = "SELECT language FROM languages WHERE iso = '".$db->real_escape_string($_GET["la"])."';";
	$res = $db->query($sql);
	if($res&&$res->num_rows==1) {
		define("LANG_ISO", $db->real_escape_string($_GET["la"]));
		define("LANGUAGE", $res->fetch_object()->language);
	}
} else {
	header("Location: ".SITEURL."en/");
	die();
}

if(!defined("LANGUAGE") OR !defined("LANG_ISO")) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	exit();
}

define("SITEISO","http://".$_SERVER["SERVER_NAME"]."/dessi/".LANG_ISO."/");

include_once(SITEHTML."class/lingual.class.php");
$lingual = new lingual($db, LANG_ISO);

/* Start session and check if logged in*/
session_start();
if(isset($_SESSION['USER_ID'])) {
	$sql="SELECT * FROM users WHERE id='".$db->real_escape_string(trim($_SESSION['USER_ID']))."'";
	$res=$db->query($sql);

	if($res&&$res->num_rows==1) {
		$user = $res->fetch_object();
	} else {
		echo "Something went terribly wrong...";
	}
} else {
	$user = null;
}
