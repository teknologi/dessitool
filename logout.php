<?php
	define("SITEHTML", getcwd()."/");
	require_once(SITEHTML."/cfg.php");

	session_unset();

	header("location: ".SITEISO);
	exit();
?>