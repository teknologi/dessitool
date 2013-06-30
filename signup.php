<?php
define("SITEHTML", getcwd()."/");

//Require the configurations
require_once(SITEHTML."/cfg.php");
require_once(SITEHTML.'misc/recaptcha-php-1.11/recaptchalib.php');

// Get a key from https://www.google.com/recaptcha/admin/create
	$publickey = "6LdbbdQSAAAAADpM2uL0lzCTlR6teVAbXSpGtfL3";
	$privatekey = "6LdbbdQSAAAAAAsNl6j88neK_7GWrRIfd_0jChow ";

	/* the response from reCAPTCHA */
	$resp = null;

$register = isset($_POST['name']);
if($register) {
	$errmsg = array();
	$err = false;

	/* was there a reCAPTCHA response? */
	if ($_POST["recaptcha_response_field"]) {
	        $resp = recaptcha_check_answer ($privatekey,
	                                        $_SERVER["REMOTE_ADDR"],
	                                        $_POST["recaptcha_challenge_field"],
	                                        $_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) {
			/* set the error code so that we can display it */
			$error = $resp->error;
			$err = true;
		}
	} else {
		$err = true;
		$errmsg["captcha"] = $lingual->get_text(2459);
	}

	require_once(SITEHTML."/class/validate.class.php");

	$_POST['name'] = trim($_POST['name']);
	$_POST['mail'] = trim($_POST['mail']);
	$_POST['username'] = trim($_POST['username']);

	//Input Validations
	if(!validate::length_between($_POST['name'],3,20)) {
		$err = true;
		$errmsg["name"] = $lingual->get_text(2460);
	}
	if(!validate::email($_POST['mail'])) {
		$err = true;
		$errmsg["mail"] = $lingual->get_text(2461);
	}
	if(!validate::length_between($_POST['username'],3,20)) {
		$errmsg["username"] = $lingual->get_text(2462);
		$err = true;
	} else if(!validate::username($_POST['username'])) {
		$errmsg["username"] = $lingual->get_text(2463);
		$err = true;
	}
	if(strcmp($_POST['pass1'], $_POST['pass2']) != 0 ) {
		$errmsg["pass"] = $lingual->get_text(2464);
		$err = true;
	} else if(!validate::length_between($_POST['pass1'],6,20)) {
		$errmsg["pass"] = $lingual->get_text(2465);
		$err = true;
	}

	//Sanitize POST values and prevent SQL injection
	$name = $db->real_escape_string($_POST['name']);
	$mail = $db->real_escape_string($_POST['mail']);
	$username = $db->real_escape_string($_POST['username']);

	//Check for duplicate login ID
	$qry = "SELECT id FROM users WHERE username='$username'";
	$res = $db->query($qry);
	if($res && $res->num_rows) {
		$errmsg["username"] = $lingual->get_text(2466);
		$err = true;
	}

	//Check for duplicate login ID
	$qry = "SELECT id FROM users WHERE mail='$mail'";
	$res = $db->query($qry);
	if($res && $res->num_rows) {
		$errmsg["mail"] = $lingual->get_text(2467);
		$err = true;
	}

	if(!$err) {
		require_once(SITEHTML."/class/passwordhash.class.php");
		$phpass = new PasswordHash(8, FALSE);
		$hash = $db->real_escape_string($phpass->HashPassword($_POST["pass1"]));
		//$check = $phpass->CheckPassword($correct, $hash);
		unset($phpass);

		//Create INSERT query
		$qry = "INSERT INTO users(name, mail, username, phash) VALUES('$name','$mail','$username','$hash')";
		$res = $db->query($qry);

		//Check whether the query was successful or not
		if(!$res) {
			echo "fail";
			// header("location: register-success.php");
			// exit();
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

	if($register && !$err)
		echo "<meta http-equiv='refresh' content='5; URL=".SITEISO."login.php'>";
/*------------------------------------*/

//Require the html head
require(SITEHTML."comp/html-head.php");
?>
<div class='main'><div class="lftcol"></div><div class="midcol2"><div id="content">
<?php
	echo '<h1>'.$lingual->get_text(2400).'</h1>';
	echo '<p>'.$lingual->get_text(2401).'</p>';

	echo '</div></div><div id="signup" class="rgtcol2">';

	if($register && !$err) {
		echo '<h1>'.$lingual->get_text(2468).'</h1>';
		echo '<p>'.$lingual->get_text(2469).' <a href="'.SITEISO.'login.php">'.$lingual->get_text(2471).'</a> '.$lingual->get_text(2470).'</p>';
	} else {
?>
<script type="text/javascript"> var RecaptchaOptions = { theme : 'white' };</script>
<form method='post' action='<?= SITEISO?>signup.php' id="signup"class='airform stanform'>
	<fieldset>
	<legend><?= $lingual->get_text(2452) ?></legend>
	<ul>
	<li>
		<label for="name"><?= $lingual->get_text(2453) ?></label>
		<input type="text" id="name" name="name" value="<?php if($register) echo $_POST["name"] ?>" />
		<?php if(isset($errmsg["name"])) echo "<p class='err'>".$errmsg["name"]."</p>"; ?>
	</li>
	<li>
		<label for="mail"><?= $lingual->get_text(2454) ?></label>
		<input type="text" id="mail" name="mail" value="<?php if($register) echo $_POST["mail"] ?>" />
		<?php if(isset($errmsg["mail"])) echo "<p class='err'>".$errmsg["mail"]."</p>"; ?>
	</li>
	<li>
		<label for="username"><?= $lingual->get_text(2455) ?></label>
		<input type="text" id="username" name="username" value="<?php if($register) echo $_POST["username"] ?>" />
		<?php if(isset($errmsg["username"])) echo "<p class='err'>".$errmsg["username"]."</p>"; ?>
	</li>
	<li>
		<label for="pass1"><?= $lingual->get_text(2456) ?></label>
		<input type="password" id="pass1" name="pass1" value="<?php if($register) echo $_POST["pass1"] ?>" />
		<?php if(isset($errmsg["pass"])) echo "<p class='err'>".$errmsg["pass"]."</p>"; ?>
	</li>
	<li>
		<label for="pass2"><?= $lingual->get_text(2457) ?></label>
		<input type="password" id="pass2" name="pass2" value="<?php if($register) echo $_POST["pass2"] ?>" />
		<?php if(isset($errmsg["pass"])) echo "<p class='err'>".$errmsg["pass"]."</p>"; ?>
	</li>
	</ul>

<div class="captcha">
<?php
echo recaptcha_get_html($publickey, $error);
if(isset($errmsg["captcha"])) echo "<p class='err'>".$errmsg["captcha"]."</p>";
?>
</div>
	</fieldset>
	<input type="submit" name="Submit" value="<?= $lingual->get_text(2458) ?>" />

</form>
</div>
<?php
	}
?>
</div>
<?php
//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
