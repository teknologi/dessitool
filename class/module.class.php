<?php
abstract class Module  {
    public $db;
    public $lingual;
    private $iso;
    private $user_id;
    private $project_id;
    private $step;
    private $module_id;
    private $contfrom_id;
    private $header;
    private $txt;

    public function module($db, $lingual, $iso, $user_id, $project_id, $step, $module_id, $contfrom_id) {
        $this->db = $db;
        $this->lingual = $lingual;
        $this->iso = $iso;
        $this->user_id = $user_id;
        $this->project_id = $project_id;
        $this->step = $step;
        $this->module_id = $module_id;
        $this->contfrom_id = $contfrom_id;

        $qry = 'SELECT header, paragraph FROM steps WHERE template_id = 1 AND id='.$this->get_step().';';
        $res = $this->db->query($qry);
        if($res && $res->num_rows == 1) {
            $fetched = $res->fetch_object();
            $this->header = $this->lingual->get_text($fetched->header);
            $this->paragraph = $this->lingual->get_text($fetched->paragraph);
        }
    }

    public function add_log($msg) {
        $qry = sprintf("INSERT INTO log(user_id, project_id, step_id, msg) VALUES ('%s','%s','%s','%s');",
            (int) $this->user_id,
            (int) $this->get_project_id(),
            (int) $this->get_step(),
            $this->db->real_escape_string($msg));

        return $this->db->query($qry);
    }

    public function print_msg($msg = "saved", $color = "green", $speed="slow", $incjstag=true) {
        echo ($incjstag ? "<script type='text/javascript'>" : '');
        echo 'document.write(\'<p style="color:'.$color.'" class="msg hide">'.$msg.'</p>\');';
        echo "$(document).ready(function() {";
        echo "$('p.msg').fadeIn('".$speed."', function() { ;";
        echo "$('p.msg').fadeOut('".$speed."', function() { $('p.msg').attr('class', 'msg hide'); });";
        echo "});  });";
        echo ($incjstag ? '</script>' : '');
    }

    public function get_header() {
        return $this->header;
    }

    public function get_paragraph() {
        return $this->paragraph;
    }

    public function get_iso() {
        return $this->iso;
    }

    public function get_user_id() {
        return $this->user_id;
    }

    public function get_project_id() {
        return $this->project_id;
    }

    public function get_step() {
        return $this->step;
    }

    public function get_var1() {
        if (isset($_GET["var1"]))
            return (int)$_GET["var1"];
        else
            return null;
    }

    public function get_module_id() {
        return $this->module_id;
    }

    public function get_contfrom_id() {
        return $this->contfrom_id;
    }

    abstract public function load();
    abstract public function report();

    public function set_status($status) {
        $qry = 'SELECT id FROM step_status WHERE project_id = '.$this->get_project_id().' AND step_id = '.$this->get_step().';';
        $res = $this->db->query($qry);

        if($res && $res->num_rows==1) {
            $qry = sprintf("UPDATE step_status SET status = '%d' WHERE project_id = '%d' AND step_id = '%d';",
                (int) $status,
                (int) $this->get_project_id(),
                (int) $this->get_step());
        } else {
            $qry = sprintf("INSERT INTO step_status (project_id, step_id, status) VALUES ('%d', '%d', '%d');",
                (int) $this->get_project_id(),
                (int) $this->get_step(),
                (int) $status);
        }
        $this->db->query($qry);
    }
}
