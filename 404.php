<?php
define("SITEHTML", getcwd()."/");

//Require the configurations
require_once(SITEHTML."/cfg.php");

/*------------------------------------*/
/*  Meta tags (individual)            */

	//Regular
	$info["title"]="Title";
	$info["description"]="Description";
	$info["keywords"]="Key,words";

	//Robots
	$info["robots"]=array("index"=>false,"follow"=>false,"archive"=>false);
/*------------------------------------*/

//Require the html head
require(SITEHTML."comp/html-head.php");
?>
<div class="main">
    <div id="left-menu" class="lftcol"></div>
    <div class="midcol">
        <div class="content">
            <p>The requested page was not found</p>
        </div>
    </div>
</div>
<?php
//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
