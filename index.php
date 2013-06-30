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
            header("location: ".SITEISO);
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
?>
<div class='main'>
<?php

if($user) {
    echo '<div id="left-menu" class="lftcol"><ul>';
    $qry = sprintf("SELECT iso FROM lingual_rights WHERE user_id = '%d';", $user->id);
    $res = $db->query($qry);
    if($res && $res->num_rows) {
        echo '<li><a href="'.SITEISO.'translate/en/'.$res->fetch_object()->iso.'/1/" title="'.$lingual->get_text(2373).'" class="translate">'.$lingual->get_text(2418).'</a></li>';
    }
    echo '<li class="lower"><a class="logout" href="'.SITEISO.'logout.php">'.$lingual->get_text(2419).'</a></li>';
    echo '</ul></div>';

    echo '<div class="midcol"><div class="content">';

    require_once(SITEHTML."class/project.class.php");
    $project = new Project($db, LANG_ISO, $user->id);
    if(isset($_POST['cmd']) && $_POST['cmd'] = "rm_prj") {
        $project->del_project(substr($_POST['actid'], 1), $_POST['actname']);
    }
    $curpro = $project->get_projects();

    echo '<h1>'.$lingual->get_text(2415).'</h1>';
    echo '<p>'.$lingual->get_text(2416).'</p><br />';

    for($i = 0; $i < count($curpro); $i++) {
        echo '<div class="projectcon"><a href="'.SITEISO.'project/'.$curpro[$i]["id"].'/" title="'.$curpro[$i]["name"].'" class="projectbar">'.$curpro[$i]["name"].'</a><a href="#'.$curpro[$i]["id"].'" title="Delete the project '.$curpro[$i]["name"].'" class="deleteproject deleteicon">&nbsp</a></div>';
    }
    echo '<div><a href="'.SITEISO.'new-project.php"  title="'.$lingual->get_text(2417).'" class="projectbar">'. $lingual->get_text(2417).'</a></div>';
    echo '<div class="delsure hide" id="delsure">';
    echo '<a href="#" class="close">&nbsp</a>';
    echo '<h3>Are you ABSOLUTELY sure?</h3>';
    echo '<p>This will <strong>DELETE</strong> the project and any data it may contain. This action CANNOT be undone.</p><br />';
    echo '<p>Please type in the name of the project to confirm the action.</p>';

    echo '<form method="post"><input type="text" name="prjname" id="prjname" />';
    echo '<input type="hidden" name="actname" id="actname" value="" />';
    echo '<input type="hidden" name="actid" id="actid" value="" />';
    echo '<input type="hidden" name="cmd" value="rm_prj" />';
    echo '<a href="#" class="btn btngray" id="sbmt">Delete this project</a>';
    echo '</form>';
    echo '<script type="text/javascript">'."\n".'$(document).ready(function() {';
    echo '$(".deleteproject").click(function() {
        $("#actname").attr("value", $(this).parent().children(".projectbar").eq(0).html());
        $("#actid").attr("value", $(this).attr("href"));
        $(".delsure").toggleClass("hide");
        return false;
    });';
    echo '$("#prjname").keyup(function() {
        if($("#actname").attr("value") == $(this).val()) {
            $("#sbmt").attr("class", "btn btnred");
        } else {
            $("#sbmt").attr("class", "btn btngray");
        }
        return false;
    });';
    echo '$("#sbmt").click(function() {
        if ($("#sbmt").attr("class") == "btn btnred") {
            $(this).parent().submit();
        } else {
            return false;
        }

    });';
    echo '});'."\n".'</script>';
    echo '</div>';

    echo '<br /><p>'.$lingual->get_text(2372).'</p>';
    echo '</div>';
    echo '</div>';

} else {
    echo '<div class="lftcol"></div>';
    echo '<div class="midcol"><div class="content">';
        echo '<h1>'.$lingual->get_text(2410).'</h1>';
        echo '<p>'.$lingual->get_text(2411).'</p>';
    echo '</div></div>';
    echo '<div id="login" class="rgtcol">';

    echo '<form method="post" action="'.SITEISO.'" class="airform stanform loginform">';
    echo '<fieldset>';
    echo '<legend>'.$lingual->get_text(2408).'</legend>';

    if($login && $err) echo '<p>'.$lingual->get_text(2402).'</p>';
    echo '<ul>'.'<li>'.'<label for="userid">'.$lingual->get_text(2403).'</label><input type="text" id="userid" name="userid" value="'.($login ? $_POST["userid"] : '').'" />';

    if(isset($errmsg["userid"])) echo "<p>".$errmsg["userid"]."</p>";

    echo '</li><li>';
    echo '<label for="pass">'.$lingual->get_text(2404).'</label>';

    echo '<input type="password" id="pass" name="pass" value="'.($login ? $_POST["pass"] : '').'" />';

    if(isset($errmsg["pass"])) echo "<p>".$errmsg["pass"]."</p>";

    echo '</li></ul>';

    echo '<p class="signup">'.$lingual->get_text(2405).'<a href="'.SITEISO.'signup.php">'.$lingual->get_text(2406).'</a></p>';

    echo '</fieldset><input type="submit" name="Submit" value="'.$lingual->get_text(2407).'" />';
    echo '</form>';
    echo '</div></div>';
}


//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
