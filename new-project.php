<?php
define("SITEHTML", getcwd()."/");
require_once(SITEHTML."/cfg.php");
require_once(SITEHTML."class/project.class.php");
$project = new Project($db, LANG_ISO, $user->id);

if(isset($_POST["save"])) {
	$breadcrumb[] = $accpage;
	$errmsg = "";
	$create_project = isset($_POST['name']);
	if($create_project) {
		require_once(SITEHTML."/class/validate.class.php");
		if(!validate::length_between(trim($_POST['name']),3,20)) {
			$errmsg = $lingual->get_text(2425);
		} else {
			if($project->new_project($_POST['name'])) {
				header("location: ".SITEISO);
				exit();
			} else {
				echo "something went wrong";
			}
		}
	}
}

/*------------------------------------*/
/*  Meta tags (individual)            */

	//Regular
	$info["title"]="";
	$info["description"]="Description";
	$info["keywords"]="Key,words";

	//Robots
	$info["robots"]=array("index"=>false,"follow"=>false,"archive"=>false);

/*------------------------------------*/

//Require the html head
require(SITEHTML."comp/html-head.php");
?>
<div class='main'>
<?php

if($user) {
	echo '<div id="left-menu" class="lftcol"><ul>';
	echo '<li><a href="'.SITEISO.'" title="'.$lingual->get_text(1344).'">'.$lingual->get_text(1343).'</a></li>';
	echo '<li><a class="logout" href="'.SITEISO.'logout.php">'.$lingual->get_text(2419).'</a></li>';
	echo '</ul></div><div class="bigcol"><div class="content">';

	/* Only temporary thrown here */
	echo '<h1>'.$lingual->get_text(2420).'</h1>';
	echo '<p>'.$lingual->get_text(2421).'</p>';

	$project->new_project_webform($errmsg);

	echo '</div></div></div>';
}
//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
