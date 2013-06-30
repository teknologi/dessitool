<?php
class lingual{
    private $db;
    private $iso;

    public function lingual($db, $iso) {
        $this->db=$db;
        $this->iso=$iso;
    }

    public function set_text($id, $iso, $text) {
        //Create INSERT query
        $text = $this->db->real_escape_string($text);
        $iso = $this->db->real_escape_string($iso);
        $qry = sprintf("INSERT INTO lingual(id, iso, text) VALUES(%d, '%s', '%s')", $id, $iso, $text);
        return $this->db->query($qry);
    }

    public function get_text($id, $iso = '') {
        if (empty($iso))
            $iso = $this->iso;
        if($id==NULL)
            return "";

        $qry = "SELECT text FROM lingual WHERE id='".$id."' AND iso='".$iso."';";
        $res = $this->db->query($qry);

        if($res && $res->num_rows)
            return $res->fetch_object()->text;
        else
            return 'String not found...';
    }

    public function change_text($id, $comment) {

    }
}
