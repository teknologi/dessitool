<?php
/* scenario */
class mod_8 extends Module {

    public function load() {

        echo '<h2>'.$this->get_header().'</h2>';
        echo '<p>'.$this->get_paragraph().'</p><br />';

        /* Start of form */
        $qry = "SELECT * FROM scenario WHERE project_id=".$this->get_project_id()." AND step_id=".$this->get_step().";";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            echo '<table id="wisetable"><tr><th class="thname">'.$this->lingual->get_text(1345).'</th><th>'.$this->lingual->get_text(1346).'</th><th class="th25"></th></tr>';

            while($scenario = $res->fetch_object()) {
                /* Set visibility */
                echo '<tr><td><label for="active'.$scenario->id.'"> '.$scenario->title.'</label></td><td>'.$scenario->text.'</td><td><a href="#'.$scenario->id.'" class="rmwise deleteicon">&nbsp</a></td></tr>';
            }
            echo "</table>";
        }

        echo '<br /><a href="#" id="newscene" class="btn btngreen">'.$this->lingual->get_text(1345).'</a>';

        echo '<div id="divscene" class="winbox wisebox hide"><a href="#" class="close">&nbsp</a><form method="post" class="dresearch stanform"><h2>Add new scenario</h2><label>Title</label><input type="text" name="title" /><br /><label>Text</label><textarea name="text"></textarea><br /><input type="submit" name="save" value="Save" /></form></div>';

        echo "\n".'<script type="text/javascript">'."\n".'$(document).ready(function() {'."\n";
        echo '$(".rmwise").click(function() {';
        echo 'if (confirm('."'".$this->lingual->get_text(1348)."'".')) {';

        echo '$('."'".'<form method="POST">'."'".' +
          '."'".'<input type="hidden" name="save" value="remove" />'."'".' +
          '."'".'<input type="hidden" name="rmwise" value="'."'".' + $(this).attr("href") + '."'".'" />'."'".' +
          '."'".'</form>'."'".').appendTo("z-body").submit();';
        echo 'alert("out");';
        echo '}';
        echo 'alert("end");';
        echo 'return false;';
        echo '});';

        echo "\n".'});'."\n".'</script>'."\n";
    }

    public function reset_step() {

    }

    public function save() {
        if($_POST["save"] == "Save") {
            $this->set_status(1);

            if(isset($_POST["title"]) && !empty($_POST["title"]) && isset($_POST["text"]) && !empty($_POST["text"])) {
                $qry = sprintf("INSERT INTO scenario (project_id, step_id, enable, title, text) VALUES ('%d', '%d', 1, '%s','%s');",
                    $this->get_project_id(),
                    $this->get_step(),
                    $this->db->real_escape_string($_POST["title"]),
                    $this->db->real_escape_string($_POST["text"]));

                $res = $this->db->query($qry);
            }
        } else if($_POST["save"] == "remove") {
            $qry = sprintf("DELETE FROM scenario WHERE id = '%d' && project_id = '%d' && step_id = '%d';",
                           (int)(ltrim ($_POST["rmwise"],'#')),
                           $this->get_project_id(),
                           $this->get_step());
            $res = $this->db->query($qry);
        }
    }
    public function report(){
        return "";
    }
    public function generate_links() {

    }
    public function send_email() {

    }
}
