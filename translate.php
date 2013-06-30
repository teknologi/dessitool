<?php
define("SITEHTML", getcwd()."/");
require_once(SITEHTML."/cfg.php");

if(!$user) {
	header("location: ".SITEISO."login.php");
	exit();
} else {
//can user edit ?
}

require_once(SITEHTML."class/project.class.php");
$project = new Project($db, LANG_ISO, $user->id);

/* True, this isn't optimal. This will be redone later in the process */
if(isset($_GET['p'])) {
	$breadcrumb = array("Dashboard");
	$pos = explode('/',$_GET['p']);
	$accpage = $pos[sizeof($pos)-1];
} else {
	echo "variable is not set...";
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
$safe = array();
$safe["lafrom"] = $db->real_escape_string($_GET["lafrom"]);
$safe["lato"] = $db->real_escape_string($_GET["lato"]);

  if($_GET["p"]=="translate") {
	$breadcrumbs = "<ul id='breadcrumbs'><li><a href='".SITEISO."account/' title='Dashboard'>".$lingual->get_text(2426)."</a></li><li>".$lingual->get_text(2428)."</li></ul>";
  } else {
	$breadcrumbs = '<ul id="breadcrumbs"><li><a href="'.SITEISO.'account/" title="Dashboard">'.$lingual->get_text(2426).'</a></li><li><a href="'.SITEISO.'translate/'.$safe["lafrom"].'/'.$safe["lato"].'/1/" title="Dashboard">'.$lingual->get_text(2428).'</a></li><li>'.$lingual->get_text(2429).'</li></ul>';
  }

if(isset($_POST['save'])) {
	$safe["edid"] = (int)$_POST["edid"];
	$safe["text"] = $db->real_escape_string($_POST["text"]);
	$safe["comment"] = $db->real_escape_string($_POST["comment"]);

	if(isset($_POST['go'])) {
		$goto = $_POST['go'];
	} else {
		$goto = SITEISO.'translate/'.$safe["lafrom"].'/'.$safe["lato"].'/1/';
	}

	$qry = sprintf('SELECT user_id FROM lingual_rights WHERE user_id="%d" AND iso="%s";', $user->id, $safe["lato"]);
	$res = $db->query($qry);
	if($res && $res->num_rows) {
		$qry = sprintf('SELECT id FROM lingual WHERE id="%1$d" AND iso = "%2$s"', $safe["edid"], $safe["lato"]);
		$res = $db->query($qry);
		if($res && $res->num_rows==1) {
			if ($stmt = $db->prepare("UPDATE lingual SET set_by = ?, text = ?, comment = ? WHERE id = ? AND iso = ? LIMIT 1;")) {
			    $stmt->bind_param("issis", $user->id, $_POST["text"], $_POST["comment"], $_POST["edid"], $_GET["lato"]);
			    $stmt->execute();
			}
		} else {
			/* prepared statement */
			if ($stmt = $db->prepare("INSERT INTO lingual (id, iso, set_by, text, comment) VALUES (?, ?, ?, ?, ?);")) {
			    $stmt->bind_param("isiss", $_POST["edid"], $_GET["lato"], $user->id, $_POST["text"], $_POST["comment"]);
			    $stmt->execute();
			}
		}
	} else {
		//report
		exit("Ooops, something went wrong. The error has been logged and reported...");
	}

	header('Refresh: 1; URL='.$goto);
}

	//Require the html head
	require(SITEHTML."comp/html-head.php");
	echo '<div class="main">';
	echo '<div id="left-menu" class="lftcol"><ul>';
	echo '<li><a href="'.SITEISO.'" title="'.$lingual->get_text(1344).'">'.$lingual->get_text(1343).'</a></li>';
	echo '<li><a class="logout" href="'.SITEISO.'logout.php">'.$lingual->get_text(2419).'</a></li>';
	echo '</ul></div><div class="bigcol"><div class="content">';
	if(isset($_POST['save'])) {
		echo '<div class="savedirect">';
		echo '<p class="big">'.$lingual->get_text(2473).'</p>';
		echo '<p class="small">'.$lingual->get_text(2474).'</p>';
		echo '</div>';
	} else if($_GET["p"]=="translate") {
		$safe["npage"] = (int)$_GET["npage"];
		$safe["find"] = $db->real_escape_string($_GET["find"]);
?>
<h1><?= $lingual->get_text(2430) ?></h1>
<p><?= $lingual->get_text(2431) ?></p>
<form class="find">
    <label for="find"><?= $lingual->get_text(2432) ?></label><input type="text" name="find" id="find" value="<?= (empty($safe["find"])? "" : $safe["find"]) ?>" />
<input type="submit" value="<?= $lingual->get_text(2433) ?>" />
</form>
<?php

	$qry_find = "";
	if(!empty($safe["find"])) {
		$qry_find .= ' AND (first.text like "%'.$safe["find"].'%" OR second.text like "%'.$safe["find"].'%" OR first.comment like "%'.$safe["find"].'%" OR second.comment like "%'.$safe["find"].'%")';
	}
	$qry = sprintf("SELECT DISTINCT(first.id) as id, first.text as first_text, second.text as second_text, first.comment as first_comment, second.comment as second_comment FROM lingual as first LEFT JOIN lingual as second ON first.id = second.id AND second.iso = '%s' WHERE first.iso = '%s'%s limit %d, %d;", $safe["lato"], $safe["lafrom"], $qry_find, ($safe["npage"]-1)*100, ($safe["npage"])*100);

	$res = $db->query($qry);
	if($res && $res->num_rows) {
		$gomenu = array("backward" => "","forward" => "");

		if ($_GET["npage"]>1)
			$gomenu["backward"] = '<a href="'.SITEISO.'translate/'.$safe["lafrom"].'/'.$safe["lato"].'/'.($safe["npage"]-1).'/'.'" title="'.$lingual->get_text(2435).'">'.$lingual->get_text(2434).'</a>';
		if ($res->num_rows >= 100)
			$gomenu["forward"] = '<a href="'.SITEISO.'translate/'.$safe["lafrom"].'/'.$safe["lato"].'/'.($safe["npage"]+1).'/'.'" title="'.$lingual->get_text(2437).'">'.$lingual->get_text(2436).'</a>';

		echo '<p class="gomenu">'.$gomenu["backward"].'</p><p class="gomenu right">'.$gomenu["forward"].'</p>';
		echo '<table id="lingual"><tr>';

  //languages from
		echo '<th>';

  //		echo '<select name="langfrom" ONCHANGE="location = this.options[this.selectedIndex].value;">';
		$qry = ("SELECT * FROM languages ORDER BY languages.iso");
		$result = $db->query($qry);
		if($result && $result->num_rows) {
			while($reslang = $result->fetch_object()) {
				echo '<a href="'.SITEISO.'translate/'.$reslang->iso.'/'.$safe['lato'].'/1/"'.(($safe["lafrom"]==$reslang->iso)?' class="selected"':'').'>'.$reslang->language.'</a>';
			}
		}
		echo '</select>';
		echo '</th>';

	//languages to
		echo '<th>';
  //		echo '<select name="langto" ONCHANGE="location = this.options[this.selectedIndex].value;">';
		$qry = sprintf("SELECT lingual_rights.iso as iso, languages.language as language FROM lingual_rights LEFT JOIN languages ON lingual_rights.iso=languages.iso WHERE lingual_rights.user_id = '%d' ORDER BY lingual_rights.iso", $user->id);
		$result = $db->query($qry);
		if($result && $result->num_rows) {
			while($reslang = $result->fetch_object()) {
				echo '<a href="'.SITEISO.'translate/'.$safe['lafrom'].'/'.$reslang->iso.'/1/"'.(($safe["lato"]==$reslang->iso)?' class="selected"':'').'>'.$reslang->language.'</a>';
			}
		}
  //		echo '</select>';
		echo '</th></tr>';

		//page
		while($reslang = $res->fetch_object()) {
			echo '<tr>';
			$goto = SITEISO.'translate/'.$reslang->id.'/'.$safe["lafrom"].'/'.$safe["lato"].'/';
			echo '<td><a href="'.$goto.'" title="'.$reslang->first_comment.'">'.(empty($reslang->first_text) ? "String not found" : $reslang->first_text).'</a></td>';
			echo '<td><a href="'.$goto.'" title="'.$reslang->second_comment.'">'.$reslang->second_text.'</td>';
			echo '</tr>';
		}
	}

	echo "</table>";
	echo '</div>';
?>

</div>
<?php
	} else if($_GET["p"] == "edit") {
		$safe["edid"] = (int)$_GET["edid"];

		//find language names (shold be optimiced, maybe a lookup language function)
		$language = array("from", "to");
		$res = $db->query(sprintf("SELECT language FROM languages WHERE iso = '%s';", $safe["lafrom"]));
		if($res && $res->num_rows)
			$language["from"] = $res->fetch_object()->language;
		else
			exit("Something went wrong when looking up language");

		$res = $db->query(sprintf("SELECT language FROM languages WHERE iso = '%s';", $safe["lato"]));
		if($res && $res->num_rows)
			$language["to"] = $res->fetch_object()->language;
		else
			exit("Something went wrong when looking up language");

		$first = $second = array();

		$qry = sprintf("SELECT lingual.*, users.username FROM lingual LEFT JOIN users on lingual.set_by = users.id WHERE lingual.id='%d' AND lingual.iso = '%s';",
		$safe["edid"],
		$safe["lafrom"]);

		$res = $db->query($qry);
		if($res && $res->num_rows)
			$first = $res->fetch_array();
		if (count($first)==0)
			$first = array("language" => "","username" => "","stamp" => "","text" => "","comment" => "");

		$qry = sprintf("SELECT lingual.*, users.username, languages.language FROM lingual LEFT JOIN users on lingual.set_by = users.id LEFT JOIN languages ON lingual.iso = languages.iso WHERE lingual.id='%d' AND lingual.iso = '%s';",
		$safe["edid"],
		$safe["lato"],
		$safe["edid"]);

		$res = $db->query($qry);
		if($res && $res->num_rows)
			$second = $res->fetch_array();

		if (count($second)==0)
			$second = array("language" => "","username" => "","stamp" => "","text" => "","comment" => "");

		echo '<div class="half">';
		echo '<p class="title">'.$lingual->get_text(2440).' '.$language["from"].'</p>';
		echo '<p class="left">'.$lingual->get_text(2438).':</p><p class="right">'.$first["username"].'</p>';
		echo '<p class="left">'.$lingual->get_text(2439).':</p><p class="right">'.$first["stamp"].'</p>';
		echo '<label>'.$lingual->get_text(2442).':</label>';
		echo '<textarea disabled>'.$first["text"].'</textarea>';
		echo '<label>'.$lingual->get_text(2443).':</label>';
		echo '<textarea disabled>'.$first["comment"].'</textarea>';
		echo '<input type="button" value="'.$lingual->get_text(2444).'" onClick="history.go(-1);return true;" />';
		echo '</div>';


		echo '<div class="half">';
		echo '<form method="post">';
		echo '<p class="title">'.$lingual->get_text(2441).' '.$language["to"].'</p>';
		echo '<p class="left">'.$lingual->get_text(2438).':</p><p class="right">'.$second["username"].'</p>';
		echo '<p class="left">'.$lingual->get_text(2439).':</p><p class="right">'.$second["stamp"].'</p>';
		echo '<label for="text">'.$lingual->get_text(2442).':</label>';
		echo '<textarea name="text" id="text">'.$second["text"].'</textarea>';
		echo '<label for="comment">'.$lingual->get_text(2443).':</label>';
		echo '<textarea name="comment" id="comment">'.$second["comment"].'</textarea>';
		if (isset($_SERVER["HTTP_REFERER"])) {
			echo '<input type="hidden" name="go" value="'.$_SERVER["HTTP_REFERER"].'" />';
		}
		echo '<input type="hidden" name="edid" value="'.$safe["edid"].'" />';
		echo '<input type="submit" name="save" value="'.$lingual->get_text(2445).'" />';

		echo '</form>';
		echo '</div>';
	}

//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
