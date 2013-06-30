<?php
class mod_4 extends Module {

    public function load() {

        if(isset($_POST["commited"])) {
            $this->set_status(1);
            if (isset($_POST["inv"]) && is_array($_POST["inv"])) {
                $ids = implode(',', $_POST["inv"]);
            } else {
                $ids = -1;
            }
            $qry = 'UPDATE dessi_investments SET enabled = CASE WHEN id IN ('.$this->db->real_escape_string($ids).') THEN 1 ELSE 0 END WHERE project_id = '.$this->get_project_id().';';
            $res = $this->db->query($qry);
        }


        /* Start of form */
        echo '<h1>'.$this->lingual->get_text(1274).'</h1>';
        echo '<p>'.$this->lingual->get_text(1275).'</p><br />';

        $qry = "SELECT id, name, enabled FROM dessi_investments WHERE project_id=".$this->get_project_id()." ORDER BY priority";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            echo '<form method="post" class="dresearch stanform"><table style="min-width:600px; margin:0px auto; border: #ccc solid 1px;"><tr><th style="width:40px;"></th><th>Investment</th></tr>';

            while($invest_info = $res->fetch_object()) {
                echo '<tr><td><input type="checkbox" name="inv[]" value="'.$invest_info->id.'" '.($invest_info->enabled ? 'checked' : '').'/></td><td>'.$invest_info->name.'</td></tr>';
            }
            echo '</table><br />';
            echo '<div style="text-align:center">';
            echo '<input type="submit" name="commited" value="Save" id="savebtn" />';
            echo "</div>";
            echo "</form>";
        } else {
            echo '<p>No investment found</p>';
        }
/*
        $qry = "SELECT dimension as name FROM dessi_dimensions;";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            while($dimension = $res->fetch_object()) {
                echo '<tr><td><input type="checkbox" name="active" /></td><td>Temp name</td></tr>';

            }
            echo "</table>";
//            echo '<div><span>with selcted </span><a href="#" id="deletemember"> delete </a> <a href="#" id="editmember"> edit </a></div>';
//            echo '<a href="#" id="newmember">Add new member</a>';

        }
*/

    }
    public function save() {

    }
    public function report(){
        return "";
    }
    public function generate_links() {

    }
    public function send_email() {

    }
}
