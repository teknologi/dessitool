<?php
class mod_1 extends Module {
    public function load() {
        $refs = array();
        $qry = "SELECT title, html FROM mod1 WHERE step_id=".$this->get_step().";"; //and check user

        $res = $this->db->query($qry);

        if($res && $res->num_rows) {
            $data = $res->fetch_object();
            if (isset($data->title)) echo "<h1>".$this->lingual->get_text($data->title)."</h1>";
            if (isset($data->title)) echo "<p>".$this->lingual->get_text($data->html)."</p>";
        } else {
            echo 'Could not find data...';
        }
    }
    public function save() {
    }

    public function report(){
        return "";
    }
}
