<?php
class mod_2 extends Module {
    private $saved_data = array();
    private $questions = array();

    public function load() {

        /* Start of form */
        echo '<h2>'.$this->get_header().'</h2>';
        echo '<p>'.$this->get_paragraph().'</p><br />';

        if (isset($_POST["import"])) {
            $this->reset_step();
            $qry = "SELECT mod2.id, mod2.type, saved.id as saved_id, saved.comment FROM mod2 LEFT JOIN mod2_answers_save saved ON saved.question_id=mod2.id AND saved.project_id=".$this->get_project_id()." AND saved.step_id=".$this->get_contfrom_id()." WHERE saved.id > 0 AND mod2.step_id=".$this->get_contfrom_id()." ORDER BY mod2.num";

            $res = $this->db->query($qry);
            if($res && $res->num_rows) while($stored = $res->fetch_object()) {
                $inserted_id = $this->ins_mod2_answers_save($stored->id, $stored->comment );
                if($inserted_id) {
                    if($stored->type == "text") {
                        $qry = sprintf("INSERT INTO mod2_answers_str (answer_id, answer) (SELECT '%d' answer_id, answer FROM mod2_answers_str WHERE answer_id = '%d')",
                            (int) $inserted_id,
                            (int) $stored->saved_id);
                    } else {
                        $qry = sprintf("INSERT INTO mod2_answers_int (answer_id, answer) (SELECT '%d' answer_id, answer FROM mod2_answers_int WHERE answer_id = '%d')", (int) $inserted_id, (int) $stored->saved_id);
                    }

                    $ins = $this->db->query($qry);
                }
            }
        }


        if ((int)$this->get_contfrom_id() > 0) {
            echo '<form id="sbmimport" method="post" class="centralize">';
            $res = $this->db->query('SELECT id FROM mod2_answers_save WHERE project_id='.$this->get_project_id().' AND step_id = '.$this->get_contfrom_id().' LIMIT 1');
            if($res && $res->num_rows) {
                $res1 = $this->db->query('SELECT id FROM mod2_answers_save WHERE project_id='.$this->get_project_id().' AND step_id = '.$this->get_step().' LIMIT 1');
                echo '<input type="submit" name="import" value="Import"'.($res1 && $res1->num_rows ? ' onClick="if (!confirm('."'If you import after having saved, you will loose what you have saved in \'Review of Researh\'. Do you want to continue ?'".')) { return false; }"' : '').' />';
            } else {
                echo '<input type="submit" name="import" value="Import" class="deactive" disabled />';
            }
            echo '</form><br />';
        }
        echo '<form id="qform" method="post" class="dresearch stanform"><table><tr><th class="tabox">'.$this->lingual->get_text(2482).' <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1225).'</span></a></th><th class="dabox">'.$this->lingual->get_text(2483).' <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1226).'</span></a></th><th class="inbox">'.$this->lingual->get_text(2484).'  <a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1227).'</span></a></th></tr>';

        $qry = 'SELECT mod2.id, mod2.num, mod2.category, mod2.type, mod2.question, mod2.help, mod2.show_if_x, mod2.and_x_contains, (CASE WHEN saved.id > 0 THEN 1 ELSE 0 END) as exist, saved.id as saved_id, saved.comment FROM mod2 LEFT JOIN mod2_answers_save saved ON saved.question_id=mod2.id AND saved.project_id='.$this->get_project_id().' AND saved.step_id = '.$this->get_step().' WHERE mod2.step_id='.($this->get_contfrom_id() ? $this->get_contfrom_id() : $this->get_step()).' ORDER BY mod2.num';

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            $showtree = array();
            $checktree = array();
            while($quest = $res->fetch_object()) {
                /* Set visibility */
                $visible = ($quest->show_if_x == NULL);
                if (!$visible) {
                    $showtree[$quest->show_if_x][$quest->and_x_contains][] = $quest->id;
                    if(isset($checktree[$quest->show_if_x]))
                        $visible = in_array($quest->and_x_contains,$checktree[$quest->show_if_x]);

                }

                /* Load data */
                $choice_arr = array();
                $answer = "";
                if($quest->exist) {
                    if($quest->type == "checkbox" || $quest->type == "radio") {
                        /* if checkbox or radio */
                        $qry = "SELECT answer FROM mod2_answers_int WHERE answer_id = ".$quest->saved_id.";";
                        $result = $this->db->query($qry);
                        if($result && $result->num_rows) while($get = $result->fetch_object())
                            $choice_arr[] = $get->answer;
                        if ($quest->show_if_x == NULL)
                            $checktree[$quest->id] = $choice_arr;
                    } else {
                        /* it must be a textfield */
                        $answer = "";
                        $qry = "SELECT answer FROM mod2_answers_str WHERE answer_id = ".$quest->saved_id.";";
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
                    $qry = "SELECT id, num, answer FROM mod2_answers_load WHERE question_id=".$quest->id." ORDER BY num;";
                    $res2 = $this->db->query($qry);
                    if($res2 && $res2->num_rows) {
                        while($ans = $res2->fetch_object()) {
                            echo '<label><input type="'.$quest->type.'" name="a'.$quest->id.($quest->type=="checkbox"?"[]":"").'" '.(($quest->exist&&in_array($ans->id, $choice_arr))?' checked="checked"':'').' value="'.$ans->id.'" />'.$this->lingual->get_text($ans->answer).'</label>';
                        }
                    }
                } else {
                    /* text */
                    echo '<textarea name="a'.$quest->id.'" placeholder="'.$this->lingual->get_text(1357).'">'.$answer.'</textarea>';
                }
                echo '</td><td class="inbox"><textarea name="c'.$quest->id.'" placeholder="'.$this->lingual->get_text(1358).'">'.($quest->exist?$quest->comment:"").'</textarea></td></tr>';
            }
        } else {
            echo 'Could not find data...';
        }

        echo '</table><input type="submit" name="save" value="'.$this->lingual->get_text(2485).'" /></form>';


//Javascript should be dynamic also
echo '<script type="text/javascript">'."\n".'$(document).ready(function() {'."\n";

    echo 'var is_changed = false;';
    echo 'var nav_str = "You have unsaved changes in this document. Cancel now, then \'Save\' to save them. Or continue to discard them.";';
    echo '$("textarea, input[type=radio], input[type=checkbox]").change(function() { if(!is_changed) { change_state(); } });';
    echo 'function change_state() { is_changed = true; window.onbeforeunload = function(){ return nav_str }}';
    echo '$("#qform, #sbmimport").submit(function(){ window.onbeforeunload = null; });';

    foreach ($showtree as $x => $arr){
        echo '$("#q'.$x.' input[type='."'radio'".']").change(function() {'."\n";
        foreach ($arr as $contains => $items) {

            echo 'if($(this).val()=='.$contains.') {'."\n";
            if (count($items)) {
                $subitems = "";
                for($i=0;$i<count($items);$i++) {
                    $subitems .= '#q'.$items[$i];
                    if ($i != count($items)-1)
                        $subitems .= ',';
                }
                echo '$("'.$subitems.'").attr('."'class', 'question'".');'."\n";
                echo '} else {'."\n";
                echo '$("'.$subitems.'").attr('."'class', 'question hide'".');'."\n";
//              echo "if(!$(this).attr('checked')) ".'$("'.$subitems.'").attr('."'class', 'question hide'".');'."\n";
                echo '}'."\n";
            }
        }
        echo '});'."\n";
    }


    foreach ($showtree as $x => $arr){

        echo '$("#q'.$x.' input[type='."'checkbox'".']").change(function() {'."\n";

        foreach ($arr as $contains => $items) {

            echo 'if($(this).val()=='.$contains.') {'."\n";
            if (count($items)) {
                $subitems = "";
                for($i=0;$i<count($items);$i++) {
                    $subitems .= '#q'.$items[$i];
                    if ($i != count($items)-1)
                        $subitems .= ',';
                }
                echo 'if ($(this).attr('."'checked'".') == '."'checked'".') {';
                echo '$("'.$subitems.'").attr('."'class', 'question'".');'."\n";
                echo '} else {'."\n";
                echo '$("'.$subitems.'").attr('."'class', 'question hide'".');'."\n";
                echo '}';
                echo '}'."\n";
            }
        }
        echo '});'."\n";
    }

    echo '});'."\n".'</script>';
    }

    private function ins_mod2_answers_save($question_id, $comment_str) {
        $this->add_log("Inserted");
        $qry = sprintf("INSERT INTO mod2_answers_save (question_id, project_id, step_id, comment) VALUES ('%s','%s','%s','%s');",
            (int) $question_id,
            (int) $this->get_project_id(),
            (int) $this->get_step(),
            $this->db->real_escape_string($comment_str));
        $insterted = $this->db->query($qry);
        if ($insterted)
            return $this->db->insert_id;
        return 0;
    }

    private function upd_mod2_answers_save($question_id, $comment_str) {
        $this->add_log("Updated");
        $qry = sprintf("UPDATE mod2_answers_save SET comment='%s' WHERE question_id=%s AND project_id=%s AND step_id=%s;",
            $this->db->real_escape_string($comment_str),
            (int) $question_id,
            (int) $this->get_project_id(),
            (int) $this->get_step());
        return $this->db->query($qry);
    }


    public function save() {
        //check whether all questions are answered
        $qry = "SELECT mod2.id, mod2.type, mod2.show_if_x, mod2.and_x_contains as contains FROM mod2 WHERE mod2.step_id=".($this->get_contfrom_id() ? $this->get_contfrom_id() : $this->get_step())." ORDER BY mod2.num";
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

        $qry = "SELECT mod2.id, mod2.type, (saved.id > 0) as exist, saved.id as saved_id FROM mod2 LEFT JOIN mod2_answers_save saved ON saved.question_id=mod2.id AND saved.project_id=".$this->get_project_id()." AND saved.step_id=".$this->get_step()." WHERE mod2.step_id=".($this->get_contfrom_id() ? $this->get_contfrom_id() : $this->get_step())." ORDER BY mod2.num";
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
                        $qry = "DELETE FROM mod2_answers_save WHERE question_id = ".$posts->id." AND project_id = ".$this->get_project_id()." AND step_id = ".$this->get_step().";";
                        $ins = $this->db->query($qry);
                        $qry = "DELETE FROM ".(($posts->type == "text")?"mod2_answers_str":"mod2_answers_int")." WHERE answer_id = ".$posts->saved_id.";";
                        $ins = $this->db->query($qry);
                    }
                } else {
                    $this->set_status(1);

                    if ($posts->exist) {
                        $upd = $this->upd_mod2_answers_save($posts->id, $comment_str);

                        if ($posts->type == "checkbox" && $isvalue) {
                            /* checkbox */
                            /* Will be replaced by update if changed, instead of delete and then insert */
                            $qry = "DELETE FROM mod2_answers_int WHERE answer_id = ".$posts->saved_id.";";
                            $ins = $this->db->query($qry);
                            for($i = 0; $i < count($ans_lst); $i++) {
                                $qry = sprintf("INSERT INTO mod2_answers_int (answer_id, answer) VALUES ('%d', '%d');",
                                    (int) $posts->saved_id,
                                    (int) $ans_lst[$i]);

                                $ins = $this->db->query($qry);
                            }
                        } else if ($posts->type == "radio" && $isvalue) {
                            /* Will be replaced by update if changed, instead of delete and then insert */
                            $qry = "DELETE FROM mod2_answers_int WHERE answer_id = ".$posts->saved_id.";";
                            $ins = $this->db->query($qry);
                            $qry = sprintf("INSERT INTO mod2_answers_int (answer_id, answer) VALUES ('%d', '%d');",
                                (int) $posts->saved_id,
                                (int) $ans_int);
                            $ins = $this->db->query($qry);
                        } else if ($posts->type == "text" && $isvalue) {
                            $qry = sprintf("UPDATE mod2_answers_str SET answer = '%s' WHERE answer_id = %d;",
                                $this->db->real_escape_string($ans_str),
                                (int) $posts->saved_id);
                            $ins = $this->db->query($qry);
                        }
                    } else {
                        /* Insert */
                        $inserted_id = $this->ins_mod2_answers_save($posts->id, $comment_str);

                        if ($posts->type == "checkbox" && $isvalue) {
                            for($i = 0; $i < count($ans_lst); $i++) {
                                $qry = sprintf("INSERT INTO mod2_answers_int (answer_id, answer) VALUES ('%s', '%s');",
                                    (int) $inserted_id,
                                    (int) $ans_lst[$i]);
                                $ins = $this->db->query($qry);
                            }
                        } else if ($posts->type == "radio" && $isvalue) {
                            $qry = sprintf("INSERT INTO mod2_answers_int (answer_id, answer) VALUES ('%s', '%s');",
                                (int) $inserted_id,
                                (int) $ans_int);
                            $ins = $this->db->query($qry);
                        } else if ($posts->type == "text" && $isvalue) {
                            $qry = sprintf("INSERT INTO mod2_answers_str (answer_id, answer) VALUES ('%s', '%s');",
                                (int) $inserted_id,
                                $this->db->real_escape_string($ans_str));
                            $ins = $this->db->query($qry);
                        }
                    }
                }
            }
            if(isset($_POST["save"])) {
                $this->print_msg();
            }
        }

    }

    public function reset_step() {
        $qry = 'SELECT mod2.type, saved.id as saved_id FROM mod2 LEFT JOIN mod2_answers_save saved ON saved.question_id=mod2.id AND saved.project_id='.$this->get_project_id().' AND saved.step_id = '.$this->get_step().' WHERE mod2.step_id='.($this->get_contfrom_id() ? $this->get_contfrom_id() : $this->get_step()).' ORDER BY mod2.num';

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
        $res = $this->db->query('DELETE FROM mod2_answers_int WHERE answer_id in ('.implode(", ", $rem_obj["int"]).')');
        $res = $this->db->query('DELETE FROM mod2_answers_str WHERE answer_id in ('.implode(",", $rem_obj["str"]).')');
        $res = $this->db->query('DELETE FROM mod2_answers_save WHERE project_id = '.$this->get_project_id().' AND step_id = '.$this->get_step());
        $res = $this->db->query('DELETE FROM step_status WHERE project_id = '.$this->get_project_id().' AND step_id = '.$this->get_step());

    }

    public function report(){

        echo '<h2 id="subsection'.$this->get_step().'">'.$this->get_header().'</h2>';

        $qry = 'SELECT mod2.id, mod2.num, mod2.category, mod2.type, mod2.question, mod2.help, mod2.show_if_x, mod2.and_x_contains, (CASE WHEN saved.id > 0 THEN 1 ELSE 0 END) as exist, saved.id as saved_id, saved.comment FROM mod2 LEFT JOIN mod2_answers_save saved ON saved.question_id=mod2.id AND saved.project_id='.$this->get_project_id().' AND saved.step_id = '.$this->get_step().' WHERE mod2.step_id='.($this->get_contfrom_id() ? $this->get_contfrom_id() : $this->get_step()).' AND saved.id > 0 ORDER BY mod2.num';

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            $showtree = array();
            $checktree = array();

            echo '<table><tr><th class="tabox">'.$this->lingual->get_text(2482).'</th><th class="dabox">'.$this->lingual->get_text(2483).'</th><th class="inbox">'.$this->lingual->get_text(2484).'</th></tr>';

            while($quest = $res->fetch_object()) {
                /* Set visibility */
                $visible = ($quest->show_if_x == NULL);
                if (!$visible) {
                    $showtree[$quest->show_if_x][$quest->and_x_contains][] = $quest->id;
                    if(isset($checktree[$quest->show_if_x]))
                        $visible = in_array($quest->and_x_contains,$checktree[$quest->show_if_x]);

                }

                /* Load data */
                $choice_arr = array();
                $answer = "";

                if($quest->type == "checkbox" || $quest->type == "radio") {
                    /* if checkbox or radio */
                    $qry = "SELECT answer FROM mod2_answers_int WHERE answer_id = ".$quest->saved_id.";";
                    $result = $this->db->query($qry);
                    if($result && $result->num_rows) while($get = $result->fetch_object())
                        $choice_arr[] = $get->answer;
                    if ($quest->show_if_x == NULL)
                        $checktree[$quest->id] = $choice_arr;
                } else {
                    /* it must be a textfield */
                    $answer = "";
                    $qry = "SELECT answer FROM mod2_answers_str WHERE answer_id = ".$quest->saved_id.";";
                    $result = $this->db->query($qry);
                    if($result && $result->num_rows) while($get = $result->fetch_object())
                        $answer = $get->answer;
                }


                echo '<tr id="q'.$quest->id.'" class="question'.($visible?'':' hide').'">';
                echo '<td class="tabox">';
                if($quest->category > 0)
                    echo "<p>".$this->lingual->get_text($quest->category)."</p>";
                echo $this->lingual->get_text($quest->question).'</td>';

                echo '<td class="dabox">';
                if($quest->type == "checkbox" || $quest->type == "radio") {
                    $qry = "SELECT id, num, answer FROM mod2_answers_load WHERE question_id=".$quest->id." ORDER BY num;";
                    $res2 = $this->db->query($qry);
                    if($res2 && $res2->num_rows) {
                        while($ans = $res2->fetch_object()) {
                            echo '<p>'.($quest->exist && in_array($ans->id, $choice_arr) ? $this->lingual->get_text($ans->answer)."<br />" : "").'</p>';
                        }
                    }
                } else {
                    /* text */
                    echo '<p>'.$answer.'</p>';
                }

                echo '</td><td class="inbox"><p>'.$quest->comment.'</p></td></tr>';
            }
            echo '</table>';
        } else {
            echo 'No data found...';
        }


        /* $qry = 'SELECT mod2.id, mod2.num, mod2.category, mod2.type, mod2.question, mod2.help, mod2.show_if_x, mod2.and_x_contains, (CASE WHEN saved.id > 0 THEN 1 ELSE 0 END) as exist, saved.id as saved_id, saved.comment FROM mod2 LEFT JOIN mod2_answers_save saved ON saved.question_id=mod2.id AND saved.project_id='.$this->get_project_id().' AND saved.step_id = '..' WHERE mod2.step_id='.($this->get_contfrom_id() ? $this->get_contfrom_id() : $this->get_step()).' ORDER BY mod2.num'; */

        /* echo $qry; */
        /* $res = $this->db->query($qry); */
        /* if($res && $res->num_rows) { */
        /*      while($reporter = $res->fetch_object()) { */
        /*          echo $reporter->id."\n"; */
        /*      } */
        /* } */
    }
}
