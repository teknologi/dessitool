<?php
define("SITEHTML", getcwd()."/");
require_once(SITEHTML."/cfg.php");

$login = isset($_POST['userid']);

if($login) {
	require_once(SITEHTML."/class/validate.class.php");

	$err = false;
	$userid = $db->real_escape_string(trim($_POST['userid']));

	//Check for duplicate login ID

	$qry = "SELECT id, phash FROM users WHERE username='$userid' OR mail='$userid'";
	$res = $db->query($qry);
	if($res && $res->num_rows == 1) {
		$userinfo = $res->fetch_object();

		require_once(SITEHTML."class/passwordhash.class.php");
		$phpass = new PasswordHash(8, FALSE);
		$passed = $phpass->CheckPassword($_POST['pass'], $userinfo->phash);
		unset($phpass); //remove object
		if($passed) {
			session_regenerate_id();
			$_SESSION['USER_ID'] = $userinfo->id;
			session_write_close();
			header("location: ".SITEISO."account/");
			exit();

		} else {
			unset($userinfo);
			$err = true;
		}
	} else {
		$err = true;
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


$sql="SELECT * FROM users";
$res=$db->query($sql);


if($res&&$res->num_rows) {
	while($row = $res->fetch_object()) {
		$name =  $row->name;
	}
}
?>
<div class='main'>
	<h1><?= $lingual->get_text(2400) ?></h1>
	<p><?= $lingual->get_text(2401) ?></p>
	<div id="login">
		<form method='post' action='<?= SITEISO?>login.php' class='airform stanform'>
			<fieldset>
			<legend><?= $lingual->get_text(2408) ?></legend>
			<?php if($login && $err) echo '<p>'.$lingual->get_text(2402).'</p>'; ?>
			<ul>
			<li>
				<label for="userid"><?= $lingual->get_text(2403) ?></label>
				<input type="text" id="userid" name="userid" value="<?php if($login) echo $_POST["userid"] ?>" />
				<?php if(isset($errmsg["userid"])) echo "<p>".$errmsg["userid"]."</p>"; ?>
			</li>
			<li>
				<label for="pass"><?= $lingual->get_text(2404) ?></label>
				<input type="password" id="pass" name="pass" value="<?php if($login) echo $_POST["pass"] ?>" />
				<?php if(isset($errmsg["pass"])) echo "<p>".$errmsg["pass"]."</p>"; ?>
			</li>
			</ul>

			<p class='signup'><?= $lingual->get_text(2405) ?><a href="<?= SITEISO; ?>signup.php"><?= $lingual->get_text(2406) ?></a></p>
			</fieldset>
			<input type="submit" name="Submit" value="<?= $lingual->get_text(2407) ?>" />

		</form>
	</div>
</div>
<?php
//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
