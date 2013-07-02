<?php
/* report */
class mod_9 extends Module {

    public function load() {

        if(isset($_POST["generate"])) {

            $qry = sprintf('SELECT id FROM step_status WHERE project_id = %d AND step_id = %d AND status = 1',
                           (int)$this->get_project_id(),
                           (int)$this->get_step());
            $res = $this->db->query($qry);
            if($res && !$res->num_rows) {
                $this->set_status(1);
                echo '<div id="divscene" class="msgbox">'.
                '<h2>Phase completed</h2>'.
                '<p>You have now completed a Dessi phase</p>'.
                '<a id = "msgboxok" href="#" class="close btn btngreen">Ok</a>'.
                '</div>';
            }
            echo '<div id="summary">';
            echo '<h1>Generated summary</h1>';
            $arr = array();
            if(is_array($_POST["inreport"])) {
                $arr = $_POST["inreport"];
            }

            $qry = 'SELECT steps.id, lingual.text title FROM steps LEFT JOIN lingual ON lingual.id = steps.header WHERE steps.id IN ('.implode(",", array_map('intval', $arr)).') AND lingual.iso = "'.LANG_ISO.'";';
            $res = $this->db->query($qry);
            if($res && $res->num_rows) {
                echo '<h2>Contents</h2>';
                echo '<ul class="tableofcontents">';

                while($row = $res->fetch_object()) {
                    echo '<li><a href="#subsection'.$row->id.'" title="Goto '.$row->title.'">'.$row->title.'</a></li>';
                }
                echo '</ul>';
            }

            $looppro = new Project($this->db, LANG_ISO, $this->get_user_id());
            $looppro->set_current($this->get_project_id());
            for($i = 0; $i < count($arr); $i++) {
;
                echo '<div class="subsection">';
                $loopmod = $looppro->load_module($arr[$i]);
                $loopmod->report();
                echo '</div>';

            }
            echo '</div>';
        } else {

            echo '<h2>'.$this->get_header().'</h2>';
            echo '<p>'.$this->get_paragraph().'</p><br />';

            /* Start of form */
            echo '<form method="post">';
            echo '<ul>';

            /* make list */
            $sip = array();
            $qry = 'SELECT contfrom_id FROM steps WHERE id = '.((int)$this->get_step()).' Limit 1;';

            $res1 = $this->db->query($qry);
            $parent_id = 0;
            if($res1 && $res1->num_rows) {
                $parent_id = $res1->fetch_object()->contfrom_id;
            }

            if($parent_id) {
                $qry = 'SELECT * FROM steps WHERE pid = '.$parent_id.' AND summarizable = 1 ORDER BY num';
            } else {
                $qry = 'SELECT * FROM steps WHERE summarizable = 1 ORDER BY pid, num';
            }
            $res = $this->db->query($qry);

            if($res && $res->num_rows) {
                echo '<p>Include these things in the report:</p>';
                echo '<ul>'."\n";

                while($row = $res->fetch_assoc()){
                    echo '<li>';
                    echo '<input type="checkbox" name="inreport[]" value="'.$row["id"].'" id="rep'.$row["id"].'" />';
                    echo '<label for="rep'.$row["id"].'">'.$this->lingual->get_text($row["name"]).'</label"</li>';
                }
                echo '</ul><br />'."\n";

            } else {

            }
            echo '<input type="hidden" name="save" value="Save"/>';
            echo '<input type="submit" name="generate" value="Generate summary"/>';
            echo "</form>";
        }
    }

    public function reset_step() {

    }

    public function save() {

    }

    public function report(){
        return "";
    }
}
