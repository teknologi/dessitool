<?php
define("SITEHTML", getcwd()."/");

//Require the configurations
require_once(SITEHTML."/cfg.php");

/*------------------------------------*/
/*  Meta tags (individual)            */

	//Regular
	$info["title"]="Title";
	$info["description"]="About";
	$info["keywords"]="Key,words";

	//Robots
	$info["robots"]=array("index"=>false,"follow"=>false,"archive"=>false);
/*------------------------------------*/

//Require the html head
require(SITEHTML."comp/html-head.php");


	echo '<div class="lftcol"></div>';
	echo '<div class="midcol"><div class="content">';
		echo '<h1>'.$lingual->get_text(2413).'</h1>';
		echo '<p>'.$lingual->get_text(2414).'</p>';
	echo '</div></div>';


//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
