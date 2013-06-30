<?php
define("SITEHTML", getcwd()."/");
require_once(SITEHTML."/cfg.php");

if(!isset($user)) {
    header("location: ".SITEISO);
    exit();
}

$safe = array();
$safe["id"] = (int)$_GET["id"];

require_once(SITEHTML."class/project.class.php");
$project = new Project($db, LANG_ISO, $user->id);
$project->set_current($safe["id"]);
/* require_once(SITEHTML."class/dessi.class.php"); */
/* $dessi = new Dessi(&$db, 'en', $user->id); */

if(isset($_GET["step"]))
    $step = $_GET["step"];

if(isset($_GET["p"]))
    $page = (int)$_GET["p"];

if(isset($_GET["n"]))
    $num = (int)$_GET["n"];

if(isset($_GET["section"]))
    $section = $_GET["section"];

$ajax = (isset($_POST["content"]) && $_POST["content"]=="only");

/* echo "step:".$step."<br />"; */
/* echo "page:".$page."<br />"; */
/* echo "num:".$num."<br />"; */
/* echo "section:".$section."<br />"; */

/*------------------------------------*/
/*  Meta tags (individual)            */

    //Regular
    $info["title"]="";
    $info["description"]="Description";
    $info["keywords"]="Key,words";

    //Robost
    $info["robots"]=array("index"=>false,"follow"=>false,"archive"=>false);
/*------------------------------------*/

    /* This will be optimized later... */
    $breadcrumbs = '<ul id="breadcrumbs"><li><a href="'.SITEISO.'account/" title="Dashboard">'.$lingual->get_text(2426).'</a></li>';
    if(!isset($page)) {
        $breadcrumbs.="<li>".$project->get_cur_name()."</li>";
    } else {
        $breadcrumbs.="<li><a href='".SITEISO."project/".$project->get_cur_id()."/' title='".$project->get_cur_name()."'>".$project->get_cur_name()."</a></li>";

        if($page=="steps") {
            if(!isset($num)) {
                $breadcrumbs.='<li>'.$lingual->get_text(2448).'</li>';
            } else {
                $step_info = $project->get_step_info($num);
                $breadcrumbs.='<li><a href="'.SITEISO.'project/'.$project->get_cur_id().'/steps/">'.$lingual->get_text(2448).'</a></li>';
                $breadcrumbs.='<li>'.$step_info->name.'</li>';
            }
        } else if($page=="users") {
                $breadcrumbs.='<li>'.$lingual->get_text(2449).'</li>';
        } else if($page=="history") {
                $breadcrumbs.='<li>'.$lingual->get_text(2450).'</li>';
        } else if($page=="edit-project") {
                $breadcrumbs.='<li>'.$lingual->get_text(2451).'</li>';
        }
    }
    $breadcrumbs .= "</ul>";

    if(!$ajax) {
        require(SITEHTML."comp/html-head.php");

        echo '<div class="main"><div id="left-menu" class="lftcol"><ul>';
        echo '<li><a href="'.SITEISO.'project/'.$project->get_cur_id().'/">'.$lingual->get_text(2447).'</a></li>';
//          '<li><a href="'.SITEISO.'project/'.$project->get_cur_id().'/users/">'.$lingual->get_text(2449).'</a></li>'.
//          '<li><a href="'.SITEISO.'project/'.$project->get_cur_id().'/history/">'.$lingual->get_text(2450).'</a></li>'.
//          '<li><a href="'.SITEISO.'project/'.$project->get_cur_id().'/edit-project/">'.$lingual->get_text(2451).'</a></li>';

            echo '<li><a href="'.SITEISO.'project/'.$project->get_cur_id().'/steps/">'.$lingual->get_text(2448).'</a>'.$project->print_stepmenu().'</li>';

            echo '<li class="lower"><a href="'.SITEISO.'" title="Go back to main menu">'.$lingual->get_text(1312).'</a></li>';

            '<li><a href="'.SITEISO.'logout.php">'.$lingual->get_text(2419).'</a></li>';

            echo '</ul></div><div class="bigcol"><div class="content">';
    }

    /* echo "id:".$_GET["id"]."<br />"; */
    /* echo "page:".$_GET["p"]."<br />"; */
    /* echo "n:".$_GET["n"]."<br />"; */
/* This will be moved to a class later on */
    if(!isset($page)) {
        echo '<h1>'.$lingual->get_text(2472).'</h1>'.
        '<p>'.$lingual->get_text(2475).'</p>'.
        '<div class="centralize"><a class="btn startdessi" href="'.SITEISO.'project/'.$project->get_cur_id().'/steps/1/">'.$lingual->get_text(1334).'</a></div>';
    } else if($page=="steps"){
        if(isset($num)) {
            $module = $project->load_module($num);
            if (isset($_POST["save"])) $module->save();

            if($ajax)
                $module->ajax();
            else
                $module->load();
        }

        if(!isset($num)) {
            $teps = $project->get_steps();
            echo '<div class="trin_lst">'.$project->print_steps($teps).'</div>';
        } else if($num == 1) {

        } else if($num == 777) {
            echo "<p>Choose track</p>";
        } else if($num == 2) {

        } else if($num == 3) {

        } else if($num == 4) {

        } else if($num == 5) {

        } else if($num == 7) {

        }

    } else if($page=="users") {
        echo '<h1>'.$lingual->get_text(2476).'</h1>';
        echo '<p>'.$lingual->get_text(2477).'</p>';
    } else if($page=="history") {
        echo '<h1>'.$lingual->get_text(2478).'</h1>';
        echo '<p>'.$lingual->get_text(2479).'</p>';
        echo $project->get_log();
    } else if($page=="edit-project") {
        echo '<h1>'.$lingual->get_text(2480).'</h1>';
        echo '<p>'.$lingual->get_text(2481).'</p>';
    }

    if(!$ajax) {
        /* make list */
        $bottomarr = $project->bottomstep();
        $alldone = true;
        $liststeps = '<ul>'."\n";
        $stepstatus_class = array('not_required', 'present', 'notdone', 'alldone');
        $stepstatus_msg = array($lingual->get_text(1339),
                                $lingual->get_text(1340),
                                $lingual->get_text(1341),
                                $lingual->get_text(1342));

        for($i=0; $i < count($bottomarr); $i++) {
            if ($bottomarr[$i]["status"] == -1) {
                $stepstatus = 0;
            } else if($bottomarr[$i]["status"] == 1) {
                $stepstatus = 3;
            } else {
                if($alldone) {
                    $alldone = false;
                    $stepstatus = 1;
                } else {
                    $stepstatus = 2;
                }
            }
            $isactive = (isset($num) && $num == $bottomarr[$i]["id"]);
            if($isactive) {
                if($i>0) {
                    $step_prev = $bottomarr[($i-1)]["url"];
                }
                if($i < count($bottomarr)-1) {
                    $step_next = $bottomarr[($i+1)]["url"];
                }
            }

            $liststeps .=  '<li><div class="'.$stepstatus_class[$stepstatus].'"><a href="'.$bottomarr[$i]["url"].'" title="'.$bottomarr[$i]["name"].(!empty($stepstatus_msg[$stepstatus]) ? ' ('.$stepstatus_msg[$stepstatus].')' : '').'">'.($isactive ? '<div class="activetab"></div>' : '&nbsp;').'</a></div>'."\n";
        }
        $liststeps .= '</ul>'."\n";


        echo '<div id="navigation">';
        echo '<div class="bottomleft">'.(isset($step_prev) ? '<a href="'.$step_prev.'" title="Goto previous step">Back</a>' : '').'</div>';
        echo '<div class="overview">';
        echo $liststeps;
        echo '</div>';
        echo '<div class="bottomright">'.(isset($step_next) ? '<a href="'.$step_next.'" title="Goto next step">Next</a>' : '').'</div>';
        echo '</div>';

        echo '</div></div>';
        //Require the html foot
        require(SITEHTML."comp/html-foot.php");
    }
?>
