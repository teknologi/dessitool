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
    $info["js"][] = SITEURL.'script/jquery-ui-1.10.0.custom.min.js';
    $info["css"][] = SITEURL.'css/jquery-ui-1.10.0.custom.css';

/*------------------------------------*/

/* $lang=$db->real_escape_string($_GET["la"]); */
if(isset($_GET["id"]) && isset($_GET["ln"])) {
    $id=(int)$_GET["id"];
    $link=$db->real_escape_string($_GET["ln"]);

    if(isset($_GET["criterion"]) && isset($_GET["investment"])) {
        $criterion=(int) $_GET["criterion"];
        $investment=(int) $_GET["investment"];
        $crit_inv_isset = true;
    } else {
        $crit_inv_isset = false;
    }

    //Require the html head
    require(SITEHTML."comp/html-head.php");

    echo '<div class="main"><div id="left-menu" class="lftcol">';

    $qry = "SELECT mod6_dim.dimension, mod6_dim.id as dimension_id, status.project_id, status.step_id FROM mod4_status status LEFT JOIN mod4_group ON status.id=mod4_group.mod4_id LEFT JOIN mod6_dim ON mod4_group.dimension_id = mod6_dim.id WHERE mod4_group.mod4_id=".$id." AND mod4_group.link='".$link."' AND status.expire > NOW();";



    $res = $db->query($qry);
    $foundit = false;
    if($res && $res->num_rows) {
        $link_info = $res->fetch_object();

        $invest = array();
        $qry = "SELECT id, name, description FROM dessi_investments WHERE enabled=1 AND project_id=".$link_info->project_id." ORDER BY priority";
        $res = $db->query($qry);
        if($res && $res->num_rows) {
            while($invest_res = $res->fetch_array()) {
                $invest[$invest_res["id"]] = $invest_res;
            }
            $first_invarr = current(array_filter($invest));
            $first_inv = $first_invarr["id"];
        } else {
            echo "<p>Did not find any investments</p>";
            exit();
        }

        echo '<p class="dimension">
<a href="'.SITEISO.'link/'.$id.'-'.$link.'/" >'.$link_info->dimension.'</a></p>';
        echo '<ul class="criteria">';

        $qry = sprintf("SELECT dcrit.num, dcrit.title FROM mod6_crit dcrit WHERE dcrit.dimension_id='%d';", $link_info->dimension_id);

        $result = $db->query($qry);
        if($result && $result->num_rows) {
            while($dcrit = $result->fetch_object()) {

                echo '<li><a href="'.SITEISO.'link/'.$id.'-'.$link.'/'.$dcrit->num.'/'.$first_inv.'/"'.(($crit_inv_isset && $criterion==$dcrit->num)?' class="active"':'').' title="'.$dcrit->title.'">'.$dcrit->title.'</a></li>';
            }
        }

            echo '</ul>';
        $foundit = true;
    }

    echo '</div>';
    echo '<div class="bigcol"><div class="content">';
    if($crit_inv_isset) {
        if($foundit) {
            $qry = sprintf("SELECT question, explanation, description FROM mod6_crit WHERE dimension_id='%d' AND num ='%d';",
                $link_info->dimension_id,
                $criterion);

            $result = $db->query($qry);
            if ($result && $result->num_rows) {
                $tmp_obj = $result->fetch_object();
                $question = $tmp_obj->question;
                $explain = $tmp_obj->explanation;
                $description = $tmp_obj->description;
            } else {
        //group tried to access a not defined group
                exit();
            }

            $qry = "SELECT * FROM mod4_group_input input WHERE input.mod4_id=".$id." AND input.dimension_id=".$link_info->dimension_id." AND input.criteria='".$criterion."' AND input.investment='".$investment."';";
            $result = $db->query($qry);
            $exist = ($result && $result->num_rows);
            $fields = array("open_discussion"=>"","summary"=>"","rating"=>"0","message"=>"");

            if(isset($_POST["save"])) {
                $fields["open_discussion"] = $_POST["open_discussion"];
                $fields["summary"] = $_POST["summary"];
                $fields["rating"] = $_POST["rating"];
                $fields["message"] = $_POST["message"];

                $insrate = (($_POST["rating"] == -3) ? NULL : $_POST["rating"]);
                if ($exist) {
                    /* prepared statement */
                    if ($stmt = $db->prepare("UPDATE mod4_group_input SET open_discussion = ?, summary = ?, rating = ?, message = ? WHERE mod4_id = ? AND dimension_id = ? AND criteria = ? AND investment = ?")) {

                        $stmt->bind_param("ssisiiii",
                            $_POST["open_discussion"],
                            $_POST["summary"],
                            $insrate,
                            $_POST["message"],
                            $id,
                            $link_info->dimension_id,
                            $criterion,
                            $investment);
                        $stmt->execute();
                    }
                } else {
                    if ($stmt = $db->prepare("INSERT INTO mod4_group_input (mod4_id, dimension_id, criteria, investment, open_discussion, summary, rating, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {

                        $stmt->bind_param("iiiissis",
                            $id,
                            $link_info->dimension_id,
                            $criterion,
                            $investment,
                            $_POST["open_discussion"],
                            $_POST["summary"],
                            $insrate,
                            $_POST["message"]);
                        $stmt->execute();
                    }
                }

                if(isset($_POST["jssend"])) {
                    exit("saved");
                } else {
                    echo "<script type='text/javascript'>".
                    "$(document).ready(function() {".
                    "$('#msg').fadeIn('slow', function() { ;".
                    "$('#msg').fadeOut('slow', function() { $('#msg').attr('class', 'hide'); });"."}); ".
                    "});".
                    "</script>";
                }
            } else {
                if($exist) {
                    $objects = $result->fetch_object();
                    $fields["open_discussion"] = $objects->open_discussion;
                    $fields["summary"] = $objects->summary;
                    $fields["rating"] = $objects->rating;
                    $fields["message"] = $objects->message;
                }
            }
            if(!isset($invest[($investment)])) {
                echo "<p>Did not find investment</p>";
                exit();
            }


            /* taken out  $lingual->get_text(1258), */
            $rating = array($lingual->get_text(1259),$lingual->get_text(1260),$lingual->get_text(1261),$lingual->get_text(1262),$lingual->get_text(1263));

            $qry = "SELECT * FROM scenario WHERE project_id=".$link_info->project_id.";";
            $res = $db->query($qry);
            $scenario = Array();
            if($res && $res->num_rows) while($scenario[] = $res->fetch_object());

            /* cut from here */
            $qry = sprintf("SELECT title FROM mod6_crit WHERE dimension_id='%d' AND num = '%d';", $link_info->dimension_id, $criterion);
            $result = $db->query($qry);
            if($result && $result->num_rows == 1) {
                $dim_name = $result->fetch_object()->title;
            }

        echo '<h1>Workshop assessment 1</h1>';
        echo '<h2>'.$link_info->dimension.(isset($dim_name) ? ' - '.$dim_name : '').'</h2>';

        ?>
            <br />
            <p id="msg" class="hide">saved</p>

            <form id="link_form" method="post" class="stanform assess">

        <?php
            echo '<p class="labelquestion">Question</p>';
            echo '<p class="thequestion">'.$question.' <a href="#" class="questext"><span class="hide">'.($explain).'</span></a></p><br />';
        ?>

                <label for="investment"><?= $lingual->get_text(1174); ?></label>
                <select id="investment" name="investment">

        <?php

            foreach ($invest as $inv) {
                    echo
                    '<option value="'.SITEISO.'link/'.$id.'-'.$link.'/'.$criterion.'/'.$inv["id"].'/"'.(($investment==$inv["id"])?' selected="selected"':'').'>'.$inv["name"].'</option>';

            }
        ?>
                </select><br />
                <p style="width:320px; margin-left:155px;"><?= $invest[$investment]["description"] ?></p>
        <?php

                if($link_info->step_id==24 && !empty($scenario)>0) {
                      echo '<p style="float:right; width:200px; border:
                #333 solid 1px;">';


                    foreach($scenario as $item) {
                        echo '<span style="display:inline-block;font-weight:bold; width:100%; text-align:center;">'.$item->title.'</span>';
                        echo '<span style="">'.$item->text.'</span><br />';
                    }
                        echo '</p>';
                }
        ?>

                <label for="open_discussion"><?= $lingual->get_text(1176); ?></label>
                <textarea id="open_discussion" name="open_discussion"><?= (isset($fields['open_discussion']) ? htmlentities($fields['open_discussion']) : '') ?></textarea><br />
                <label for="summary"><?= $lingual->get_text(1177); ?></label>
                <textarea id="summary" name="summary"><?= (isset($fields['summary']) ? htmlentities($fields['summary']) : '') ?></textarea><br />

                <label><?= $lingual->get_text(1178); ?></label>
                <!-- <input id="rating" name="rating" type="range" name="test" min="-2" max="2" step="1" value="<?= $fields['rating'] ?>" /> -->
                <div id="slider"></div>
                <span id="chosen"><?= $rating[$fields['rating']+2] ?></span><br />
        <script type="text/javascript">
        $(document).ready(function() {
            var is_changed = false;
            var nav_str = "You have unsaved changes in this document. Cancel now, then 'Save' to save them. Or else continue to discard them.";
            var arr = new Array(<?= '"'.$lingual->get_text(1259).'","'.$lingual->get_text(1260).'","'.$lingual->get_text(1261).'","'.$lingual->get_text(1262).'","'.$lingual->get_text(1263).'"' ?>);

            // $("#rating").change(function() {
            //      $("#chosen").html(arr[parseInt(this.value)+2]);
            //      if(!is_changed) { change_state(); }
            // });

            $( "#slider" ).slider({
                value:<?= (int)$fields['rating'] ?>,
                min: -2,
                max: 2,
                step: 1,
                slide: function( event, ui ) {
                    $( "#chosen" ).html(arr[parseInt(ui.value)+2]);
                    if(!is_changed) { change_state(); }
                }
            });
            // Hover states on the static widgets
            $( "#dialog-link, #icons li" ).hover(
                function() {
                    $( this ).addClass( "ui-state-hover" );
                },
                function() {
                    $( this ).removeClass( "ui-state-hover" );
                }
            );

            $("textarea, #message").change(function() {
                if(!is_changed) { change_state(); }
            });
            $("select").change(function() {
                location = this.options[this.selectedIndex].value;
            });
            function change_state() {
                is_changed = true;
                window.onbeforeunload = function(){ return nav_str }
            }
            $("#save_and_stay").click(function() {
                $("#msg").html("<img src='<?= SITEURL."img/ajax-loader.gif" ?>' alt='ajax-loader' />");
                $("#msg").attr("class", "");
                $("#msg").fadeIn("fast");
                window.onbeforeunload = null;
                var values = $("#link_form").serializeArray();
                values.push({name: "rating", value: $( "#slider" ).slider( "value" )});
                values.push({name: "jssend", value: "1"});
                $.ajax({
                    url: window.location,
                    type: "post",
                    data: values,
                    success: function(){
                        $("#msg").html("Saved");
                        $("#msg").fadeOut("slow", function() { $("#msg").attr("class", "hide"); });
                        is_changed = false;
                    }, error:function(){
                        alert("Something went wrong");
                        $("#msg").html("Failed");
                        $("#msg").fadeOut("slow", function() { $("#msg").attr("class", "hide"); });
                    }
                });

                return false;
            });

            $("#link_form").submit(function(){
                window.onbeforeunload = null;
            });
        });
        </script>

                <label for="message"><?= $lingual->get_text(1175); ?></label>
                <input type="text" id="message" name="message" value="<?= (isset($fields['message']) ? htmlentities($fields['message']) : '') ?>" /><br />
                <input type="hidden" name="save" value="1" />
                <input type="button" id="save_and_stay" value="<?= $lingual->get_text(1183); ?>" />
                <!-- <input type="submit" id="save_and_go" value="<?= $lingual->get_text(1183); ?>" /> -->
            </form>
        <?php
              echo '</div></div>';
        } else {
            //this should be logged
            echo '<h1>Page not found</h1>';
            echo '<p>The page you are looking for does not exist; it may have been moved, or removed altogether.</p>';

            echo '</div></div>';
        }
    } else if ($foundit){
        echo '<h1>'.$lingual->get_text(1355).'</h1>';
        echo '<p>'.$lingual->get_text(1356).'</p>';
        echo '</div></div>';
    } else{
        //Should show 404.php instead
        echo '<h1>Page not found</h1>';
        echo '<p>The page you are looking for does not exist; it may have been moved, or removed altogether.</p>';
        echo '</div></div>';
    }
}

echo '</div>';

//Require the html foot
require(SITEHTML."comp/html-foot.php");
?>
