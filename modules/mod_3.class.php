<?php
class mod_3 extends Module {

    private $saved_data = array();
    private $questions = array();

    public function load() {

        echo '<h1>'.$this->lingual->get_text(1192).'</h1>';
        echo '<p>'.$this->lingual->get_text(1313).'</p>';
        $safe["var1"] = (int)$this->get_var1();
        $is_invest = false;

        if(isset($_POST["rm"]) && $_POST["rm"] == $safe["var1"]) {
            $this->remove_investment();
        }

        $qry = "SELECT id, name, description FROM dessi_investments WHERE project_id=".$this->get_project_id()." ORDER BY priority";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            while($invest_res = $res->fetch_object()) {
                if($safe["var1"] == $invest_res->id) {
                    echo '<span class="investment_text">'.$invest_res->name.'</span>';
                    $is_invest = true;
                    $current_desc = $invest_res->description;
                } else {
                    echo '<a href="'.SITEISO.'project/'.$this->get_project_id().'/steps/'.$this->get_step().'/'.$invest_res->id.'/" class="investment_text" title="'.$invest_res->description.'">'.$invest_res->name.'</a>';
                }
            }
        }

        echo '<a href="#" id="newinv_link" class="onright">'.$this->lingual->get_text(1191).'</a>';
        echo '<form method="post" id="newinv_form" class="newinv hide">'.
            '<h3>'.$this->lingual->get_text(1196).'</h3>'.
            '<label for="invest_name">'.$this->lingual->get_text(1197).'</label><input type="text" id="invest_name" name="invest_name" /><br />'.
            '<label for="invest_description">'.$this->lingual->get_text(1198).'</label><br /><textarea id="invest_description" name="invest_description"></textarea><br />'.
            '<input type="button" value="'.$this->lingual->get_text(1199).'">'.
            '<input type="submit" name="save" value="'.$this->lingual->get_text(1200).'">'.
            '</form>';
        echo '<script type="text/javascript">'."\n".'$(document).ready(function() {'."\n";
        echo '$("#newinv_link").click(function() {'."\n";
        echo '$("#newinv_form").attr('."'class', 'newinv'".');'."\n";
        echo 'return false;';
        echo '});';
        echo '$("input[type='."'button'".']").click(function() {'."\n";
        echo '$(this).parent().attr('."'class', 'dimcrit hide'".');'."\n";
        echo '});';

        echo 'var is_changed = false;';
        echo 'var nav_str = "You have unsaved changes in this document. Cancel now, then \'Save\' to save them. Or continue to discard them.";';
        echo '$("#textarea, input[type=radio], input[type=checkbox]").change(function() { if(!is_changed) { change_state(); } });';
        echo 'function change_state() { is_changed = true; window.onbeforeunload = function(){ return nav_str }}';
        echo '$("#qform").submit(function(){ window.onbeforeunload = null; });';
        echo '});'."\n";
        echo '</script>';

        if ($is_invest) {

            echo '<p class="invest_desc">'.$this->lingual->get_text(1202).': <span>'.$current_desc.'</span></p>';
            //<a href="#" id="rem_inv" class="btn btnred onright" onClick="if (!confirm('."'Are you sure you want to remove the investment? All the data in the investment will be lost.'".')) { return false; }">Remove</a>

            echo '<form method="post">';
            echo '<input type="hidden" name="rm" value="'.$safe["var1"].'" />';
            echo '<input type="submit" class="btnred isright" value="Remove"  onClick="if (!confirm('."'Are you sure you want to remove the investment ? '".')) { return false; }" />';
            echo '</form>';
            echo '<br /><br />';

            /* Start of form */
            echo '<form id="qform" method="post" class="dresearch stanform"><table><tr><th class="tabox">'.$this->lingual->get_text(1193).' <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1239).'</span></a></th><th class="dabox">'.$this->lingual->get_text(1194).' <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1240).'</span></a></th><th class="inbox">'.$this->lingual->get_text(1195).' <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1241).'</span></a></th></tr>';

            /* Get data, and existing */
            $qry = 'SELECT mod3.id, mod3.num, mod3.category, mod3.type, mod3.question, mod3.help, mod3.show_if_x, mod3.and_x_contains, (CASE WHEN saved.id > 0 THEN 1 ELSE 0 END) as exist, saved.id as saved_id, saved.comment FROM mod3 LEFT JOIN mod3_answers_save saved ON saved.question_id=mod3.id AND saved.project_id='.$this->get_project_id().' AND saved.investment_id ='.$safe["var1"].' ORDER BY mod3.num';
            $res = $this->db->query($qry);
            if($res && $res->num_rows) {
                while($quest = $res->fetch_object()) {
                    /* Set visibility */
                    $visible = ($quest->show_if_x == NULL);
                    if (!$visible) {
                        $qry = "SELECT (CASE WHEN save.id > 0 THEN 1 ELSE 0 END) as visible FROM mod3_answers_save save LEFT JOIN mod3_answers_int box ON box.answer_id = save.id AND save.project_id=".$this->get_project_id()." LEFT JOIN mod3 ON mod3.id = save.question_id WHERE mod3.num = ".$quest->show_if_x." AND box.answer = ".$quest->and_x_contains.";";
                        $result = $this->db->query($qry);
                        $visible = ($result && $result->num_rows);
                    }

                    /* Load data */
                    $choice_arr = array();
                    $answer = "";
                    if($quest->exist) {
                        if($quest->type == "checkbox" || $quest->type == "radio") {
                            /* if checkbox or radio */
                            $qry = "SELECT answer FROM mod3_answers_int WHERE answer_id = ".$quest->saved_id.";";
                            $result = $this->db->query($qry);
                            if($result && $result->num_rows) while($get = $result->fetch_object())
                                                                 $choice_arr[] = $get->answer;
                        } else {
                            /* it must be a textfield */
                            $answer = "";
                            $qry = "SELECT answer FROM mod3_answers_str WHERE answer_id = ".$quest->saved_id.";";
                            $result = $this->db->query($qry);
                            if($result && $result->num_rows) while($get = $result->fetch_object())
                                                                 $answer = $get->answer;
                        }
                    }

                    echo '<tr id="q'.$quest->id.'" class="question'.($visible?'':' hide').'">';
                    echo '<td class="tabox">';
                    if($quest->category > 0)
                        echo "<p>".$this->lingual->get_text($quest->category)."</p>";
                    echo $this->lingual->get_text($quest->question).' <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text($quest->help).'</span></a></td>';

                    echo '<td class="dabox">';

                    if($quest->type == "checkbox" || $quest->type == "radio") {
                        $qry = "SELECT id, num, answer FROM mod3_answers_load WHERE question_id=".$quest->id." ORDER BY num;";
                        $res2 = $this->db->query($qry);
                        if($res2 && $res2->num_rows) {
                            while($ans = $res2->fetch_object()) {
                                echo '<label><input type="'.$quest->type.'" name="a'.$quest->id.($quest->type=="checkbox"?"[]":"").'" '.(($quest->exist&&in_array($ans->id, $choice_arr))?' checked="checked"':'').' value="'.$ans->id.'" />'.$this->lingual->get_text($ans->answer).'</label>';
                            }
                        }
                    } else {
                        /* text */
                        echo '<textarea name="a'.$quest->id.'">'.$answer.'</textarea>';
                    }

                    echo '</td><td class="inbox"><textarea name="c'.$quest->id.'">'.($quest->exist?$quest->comment:"").'</textarea></td></tr>';
                }
            } else {
                echo 'Could not find data...';
            }

            echo '</table><input type="submit" name="save" value="'.$this->lingual->get_text(1201).'" /></form>';

//Javascript should be dynamic also
?>
<script type='text/javascript'>
$(document).ready(function() {
    $("#q3 input[type='checkbox']").change(function() {
        if($(this).val()==7) {
            if($(this).attr('checked'))
                $("#q6").attr('class', 'question');
            else
                $("#q6").attr('class', 'question hide');
        } else if($(this).val()==8) {
            if($(this).attr('checked'))
                $("#q7").attr('class', 'question');
            else
                $("#q7").attr('class', 'question hide');
        } else if($(this).val()==9) {
            if($(this).attr('checked'))
                $("#q8").attr('class', 'question');
            else
                $("#q8").attr('class', 'question hide');
        } else if($(this).val()==10) {
            if($(this).attr('checked'))
                $("#q9").attr('class', 'question');
            else
                $("#q9").attr('class', 'question hide');
        }
        $(this).closest("label").toggleClass("someClass");

        $('.dresearch.stanform tr textarea').each(function(){
            $(this).height($(this).parent().height()-8);
        });

    });

    $("#q2 input[type='radio']").change(function() {
        if($(this).val()==3) {
            $("#q3").attr('class', 'question');
            $("#q4").attr('class', 'question');
            $("#q5").attr('class', 'question');
        }else {
            $("#q23 input[type='checkbox']").removeAttr('checked');
            $("#q3").attr('class', 'question hide');
            $("#q4").attr('class', 'question hide');
            $("#q5").attr('class', 'question hide');
            $("#q6").attr('class', 'question hide');
            $("#q7").attr('class', 'question hide');
            $("#q8").attr('class', 'question hide');
            $("#q9").attr('class', 'question hide');
        }
        $(this).closest("label").toggleClass("someClass");
    });
});
</script>
    <?php
    }
    }

    private function ins_mod3_answers_save($question_id, $comment_str) {
        $this->add_log("Inserted");
        $qry = sprintf("INSERT INTO mod3_answers_save (question_id, project_id, investment_id, comment) VALUES ('%d','%d','%d','%s');",
                       (int) $question_id,
                       (int) $this->get_project_id(),
                       (int) $this->get_var1(),
                       $this->db->real_escape_string($comment_str));

        $insterted = $this->db->query($qry);
        if ($insterted)
            return $this->db->insert_id;
        return 0;
    }

    private function upd_mod3_answers_save($question_id, $comment_str) {
        $this->add_log("Updated");
        $qry = sprintf("UPDATE mod3_answers_save SET comment='%s' WHERE question_id=%d AND project_id=%d AND investment_id=%d;",
                       $this->db->real_escape_string($comment_str),
                       (int) $question_id,
                       (int) $this->get_project_id(),
                       (int) $this->get_var1());

        return $this->db->query($qry);
    }


    public function save() {

        if(isset($_POST["invest_name"]) && !empty($_POST["invest_name"])) {
            $this->set_status(1);

            $qry = "SELECT COUNT(id) as amount FROM dessi_investments WHERE project_id=".$this->get_project_id().";";
            $res = $this->db->query($qry);
            $priority = 1;
            if($res && $res->num_rows) {
                $priority += $res->fetch_object()->amount;
            }


            $qry = sprintf('INSERT INTO dessi_investments (project_id, enabled, priority, name, description) VALUES ("%d", "%d", "%d", "%s", "%s");',
                           $this->get_project_id(),
                           1,
                           $priority,
                           $this->db->real_escape_string($_POST["invest_name"]),
                           $this->db->real_escape_string($_POST["invest_description"]));


            if ($this->db->query($qry)) {
                $goto = SITEISO.'project/'.$this->get_project_id().'/steps/'.$this->get_step().'/'.$this->db->insert_id.'/';
                echo '<script language="javascript" type="text/javascript">window.location.href="'.$goto.'";</script>';
                echo '<p>Redirecting...</p>';
                exit();
            }
        } else {

            if($this->get_var1() == null){
                echo "some error occured...";
                return;
            }

            //check whether all questions are answered
            $qry = "SELECT mod3.id, mod3.type, mod3.show_if_x, mod3.and_x_contains as contains FROM mod3 ORDER BY mod3.num";
            $res = $this->db->query($qry);
            $checklist = array();
            if($res && $res->num_rows) {
                while($map = $res->fetch_object()) {
                    /* Could use OR operator, but for the sake of maintainability we will use 'else if'.
                     * This should and will be reviewed
                     */
                    if (is_null($map->show_if_x))
                        $checklist[] = $map->id;
                    else if (in_array($map->show_if_x, $checklist) && isset($_POST["a".$map->show_if_x])) {
                        if (is_array($_POST["a".$map->show_if_x])) {
                            if (in_array($map->contains, $_POST["a".$map->show_if_x]))
                                $checklist[] = $map->id;
                        } else if($map->contains == $_POST["a".$map->show_if_x]) {
                            $checklist[] = $map->id;
                        }
                    }
                }
            }

            $qry = "SELECT mod3.id, mod3.type, (saved.id > 0) as exist, saved.id as saved_id FROM mod3 LEFT JOIN mod3_answers_save saved ON saved.question_id=mod3.id AND saved.project_id=".$this->get_project_id()." AND saved.investment_id=".$this->get_var1()." ORDER BY mod3.num";

            $res = $this->db->query($qry);
            if($res && $res->num_rows) {
                while($posts = $res->fetch_object()) {
                    $comment_str = $_POST["c".$posts->id];
                    $isvalue = false;

                    if ($posts->type == "checkbox" && isset($_POST["a".$posts->id])) {
                        /* set checkbox value */
                        $ans_lst = $_POST["a".$posts->id];
                        $isvalue =  (count($ans_lst)>0);
                    } else if ($posts->type == "radio" && isset($_POST["a".$posts->id])){
                        /* set radiobox value */
                        $ans_int = $_POST["a".$posts->id];
                        $isvalue = !empty($ans_int);
                    } else if ($posts->type == "text" && isset($_POST["a".$posts->id])){
                        /* set text value */
                        $ans_str = $_POST["a".$posts->id];
                        $isvalue = !empty($ans_str);
                    }

                    if ((empty($comment_str) && !$isvalue) || !in_array($posts->id, $checklist)) {
                        /* if answer or comment exist in db and not in the current form, delete it from db */
                        if ($posts->exist) {
                            $qry = "DELETE FROM mod3_answers_save WHERE question_id = ".$posts->id." AND project_id = ".$this->get_project_id().";";
                            $ins = $this->db->query($qry);
                            $qry = "DELETE FROM ".(($posts->type == "text")?"mod3_answers_str":"mod3_answers_int")." WHERE answer_id = ".$posts->saved_id.";";
                            $ins = $this->db->query($qry);
                        }
                    } else {
                        if ($posts->exist) {
                            $upd = $this->upd_mod3_answers_save($posts->id, $comment_str);

                            if ($posts->type == "checkbox" && $isvalue) {
                                /* checkbox */
                                /* Will be replaced by update if changed, instead of delete and then insert */
                                $qry = "DELETE FROM mod3_answers_int WHERE answer_id = ".$posts->saved_id.";";
                                $ins = $this->db->query($qry);
                                for($i = 0; $i < count($ans_lst); $i++) {
                                    $qry = sprintf("INSERT INTO mod3_answers_int (answer_id, answer) VALUES ('%s', '%s');",
                                                   (int) $posts->saved_id,
                                                   (int) $ans_lst[$i]);
                                    $ins = $this->db->query($qry);
                                }
                            } else if ($posts->type == "radio" && $isvalue) {
                                /* Will be replaced by update if changed, instead of delete and then insert */
                                $qry = "DELETE FROM mod3_answers_int WHERE answer_id = ".$posts->saved_id.";";
                                $ins = $this->db->query($qry);
                                $qry = sprintf("INSERT INTO mod3_answers_int (answer_id, answer) VALUES ('%s', '%s');",
                                               (int) $posts->saved_id,
                                               (int) $ans_int);
                                $ins = $this->db->query($qry);
                            } else if ($posts->type == "text" && $isvalue) {
                                /* Will be replaced by update if changed, instead of delete and then insert */
                                $qry = "DELETE FROM mod3_answers_int WHERE answer_id = ".$posts->saved_id.";";
                                $ins = $this->db->query($qry);

                                $qry = sprintf("INSERT INTO mod3_answers_str (answer_id, answer) VALUES ('%s', '%s');",
                                               (int) $posts->saved_id,
                                               $this->db->real_escape_string($ans_str));
                                $ins = $this->db->query($qry);
                            }
                        } else {
                            /* Insert */
                            $inserted_id = $this->ins_mod3_answers_save($posts->id, $comment_str);

                            if ($posts->type == "checkbox" && $isvalue) {
                                for($i = 0; $i < count($ans_lst); $i++) {
                                    $qry = sprintf("INSERT INTO mod3_answers_int (answer_id, answer) VALUES ('%s', '%s');",
                                                   (int) $inserted_id,
                                                   (int) $ans_lst[$i]);
                                    $ins = $this->db->query($qry);
                                }
                            } else if ($posts->type == "radio" && $isvalue) {
                                $qry = sprintf("INSERT INTO mod3_answers_int (answer_id, answer) VALUES ('%s', '%s');",
                                               (int) $inserted_id,
                                               (int) $ans_int);
                                $ins = $this->db->query($qry);
                            } else if ($posts->type == "text" && $isvalue) {
                                $qry = sprintf("INSERT INTO mod3_answers_str (answer_id, answer) VALUES ('%s', '%s');",
                                               (int) $inserted_id,
                                               $this->db->real_escape_string($ans_str));
                                $ins = $this->db->query($qry);
                            }
                        }
                    }
                }
                $this->print_msg();
            }
        }
    }

    public function remove_investment(){

        $qry = 'SELECT saved.id saved_id, type FROM mod3 LEFT JOIN mod3_answers_save saved ON saved.question_id = mod3.id AND saved.project_id='.$this->get_project_id().' WHERE saved.investment_id = '.$this->get_var1().';';

        $res = $this->db->query($qry);
        $rem_obj = array("int" => array(), "str" => array());
        if($res && $res->num_rows){
            while($remove_info = $res->fetch_object()) {
                if(isset($remove_info->saved_id) && ($remove_info->saved_id > 0)) {
                    if($remove_info->type == "radio" || $remove_info->type == "checkbox") {
                        $rem_obj["int"][] = $remove_info->saved_id;
                    } else if($remove_info->type == "text") {
                        $rem_obj["str"][] = $remove_info->saved_id;
                    }
                }
            }
        }
        $res = $this->db->query('DELETE FROM mod3_answers_int WHERE answer_id in ('.implode(", ", $rem_obj["int"]).')');
        $res = $this->db->query('DELETE FROM mod3_answers_str WHERE answer_id in ('.implode(",", $rem_obj["str"]).')');
        $res = $this->db->query('DELETE FROM mod3_answers_save WHERE project_id = '.$this->get_project_id());
        $res = $this->db->query("DELETE FROM dessi_investments WHERE project_id=".$this->get_project_id()." AND id=".$this->get_var1().";");
    }

    public function reset_step() {
        $qry = 'SELECT saved.id saved_id, type FROM mod3 LEFT JOIN mod3_answers_save saved ON saved.question_id = mod3.id AND saved.project_id='.$this->get_project_id().';';

        $res = $this->db->query($qry);
        $rem_obj = array("int" => array(), "str" => array());
        if($res && $res->num_rows){
            while($remove_info = $res->fetch_object()) {
                if(isset($remove_info->saved_id) && ($remove_info->saved_id > 0)) {
                    if($remove_info->type == "radio" || $remove_info->type == "checkbox") {
                        $rem_obj["int"][] = $remove_info->saved_id;
                    } else if($remove_info->type == "text") {
                        $rem_obj["str"][] = $remove_info->saved_id;
                    }
                }
            }
        }
        $res = $this->db->query('DELETE FROM mod3_answers_int WHERE answer_id in ('.implode(", ", $rem_obj["int"]).')');
        $res = $this->db->query('DELETE FROM mod3_answers_str WHERE answer_id in ('.implode(",", $rem_obj["str"]).')');
        $res = $this->db->query('DELETE FROM mod3_answers_save WHERE project_id = '.$this->get_project_id());
        $res = $this->db->query("DELETE FROM dessi_investments WHERE project_id=".$this->get_project_id());
        $res = $this->db->query('DELETE FROM step_status WHERE project_id = '.$this->get_project_id().' AND step_id = '.$this->get_step());

    }

    public function report() {

        echo '<h2 id="subsection'.$this->get_step().'">'.$this->get_header().'</h2>';

        $safe["var1"] = (int)$this->get_var1();
        $is_invest = false;

        if(isset($_POST["rm"]) && $_POST["rm"] == $safe["var1"]) {
            $this->remove_investment();
        }

        $qry = "SELECT id, name, description FROM dessi_investments WHERE project_id=".$this->get_project_id()." AND enabled=1 ORDER BY priority";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            while($invest_res = $res->fetch_object()) {

                echo '<h3>'.$invest_res->name.'</h3>';
                echo '<p class="description">'.$this->lingual->get_text(1202).': <span>'.$invest_res->description.'</span></p>';

            /* Get data, and existing */
                $qry = 'SELECT mod3.id, mod3.num, mod3.category, mod3.type, mod3.question, mod3.help, mod3.show_if_x, mod3.and_x_contains, (CASE WHEN saved.id > 0 THEN 1 ELSE 0 END) as exist, saved.id as saved_id, saved.comment FROM mod3 LEFT JOIN mod3_answers_save saved ON saved.question_id=mod3.id AND saved.project_id='.$this->get_project_id().' AND saved.investment_id ='.$invest_res->id.' WHERE saved.id > 0 ORDER BY mod3.num';

                $resu = $this->db->query($qry);

                if($resu && $resu->num_rows) {
                    echo '<table><tr><th class="tabox">'.$this->lingual->get_text(1193).'</th><th class="dabox">'.$this->lingual->get_text(1194).'</th><th class="inbox">'.$this->lingual->get_text(1195).'</th></tr>';

                    while($quest = $resu->fetch_object()) {

                        /* Set visibility */
                        $visible = ($quest->show_if_x == NULL);
                        if (!$visible) {
                            $qry = "SELECT (CASE WHEN save.id > 0 THEN 1 ELSE 0 END) as visible FROM mod3_answers_save save LEFT JOIN mod3_answers_int box ON box.answer_id = save.id AND save.project_id=".$this->get_project_id()." LEFT JOIN mod3 ON mod3.id = save.question_id WHERE mod3.num = ".$quest->show_if_x." AND box.answer = ".$quest->and_x_contains.";";
                            $result = $this->db->query($qry);
                            $visible = ($result && $result->num_rows);
                        }

                        /* Load data */
                        $choice_arr = array();
                        $answer = "";

                        if($quest->type == "checkbox" || $quest->type == "radio") {
                            /* if checkbox or radio */
                            $qry = "SELECT answer FROM mod3_answers_int WHERE answer_id = ".$quest->saved_id.";";
                            $result = $this->db->query($qry);
                            if($result && $result->num_rows) {
                                while($get = $result->fetch_object()) {
                                    $choice_arr[] = $get->answer;
                                }
                            }
                        } else {
                            /* it must be a textfield */
                            $answer = "";
                            $qry = "SELECT answer FROM mod3_answers_str WHERE answer_id = ".$quest->saved_id.";";
                            $result = $this->db->query($qry);
                            if($result && $result->num_rows) {
                                while($get = $result->fetch_object()) {
                                    $answer = $get->answer;
                                }
                            }
                        }

                        echo '<tr id="q'.$quest->id.'" class="question'.($visible?'':' hide').'">';
                        echo '<td class="tabox">';
                        if($quest->category > 0) {
                            echo "<p>".$this->lingual->get_text($quest->category)."</p>";
                        }

                        echo $this->lingual->get_text($quest->question).'</td>';

                        echo '<td class="dabox">';

                        if($quest->type == "checkbox" || $quest->type == "radio") {
                            $qry = "SELECT id, num, answer FROM mod3_answers_load WHERE question_id=".$quest->id." ORDER BY num;";
                            $res2 = $this->db->query($qry);
                            if($res2 && $res2->num_rows) {
                                while($ans = $res2->fetch_object()) {
                                    echo '<p>'.(in_array($ans->id, $choice_arr) ? $this->lingual->get_text($ans->answer)."</br>" : "").'</p>';
                                }
                            }
                        } else {
                            /* text */
                            echo '<p name="a'.$quest->id.'">'.$answer.'</p>';
                        }
                        echo '</td><td class="inbox"><p name="c'.$quest->id.'">'.($quest->exist?$quest->comment:"").'</p></td></tr>';
                    }
                    echo '</table>';
                } else {
                    echo 'Could not find table data...';
                }
            }
        }
    }
}
