<?php
class Project{
    private $db;
    private $lingual;
    private $user_id;
    private $current;
    private $iso;
    private $steps;
    private $bottomsteps;

    public function project($db, $iso, $user_id) {
        $this->db=$db;
        $this->user_id=$user_id;
        $this->iso=$iso;
        $this->lingual = new Lingual($db,$this->iso);
    }

    public function new_project($project_name) {
        //Create INSERT query
        $project_name = $this->db->real_escape_string(trim($project_name));
        $qry = sprintf("INSERT INTO projects(template, owner, created, name) VALUES(1, %d, NOW(), '%s')", $this->user_id, $project_name);

        return $this->db->query($qry);
    }

    public function new_project_webform($errmsg) {
        echo '<form method="post" id="newproject" class="airform stanform">'.
        "<fieldset><legend>".$this->lingual->get_text(2422)."</legend>".
        "<label for='name'>".$this->lingual->get_text(2423)."</label>".
        "<input type='text' id='name' name='name' value='".((isset($_POST["name"]))?$_POST["name"]:'')."' />".
        (($errmsg != '') ? "<p class='err'>".$errmsg."</p>" : '').
        "</fieldset><input type='submit' name='save' value='".$this->lingual->get_text(2424)."' /></form>";
    }

    public function get_projects() {
        $qry = "SELECT id, name, description  FROM projects WHERE owner='".$this->user_id."';";
        $arr = array();

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            while($tmp = $res->fetch_object())
                $arr[] = array( "id" => $tmp->id, "name" => $tmp->name, "description" => $tmp->description );
        }

        return $arr;
    }

    public function del_project($id, $name) {
        $this->set_current($id);

        $qry = "SELECT id, module_id from steps WHERE template_id = 1 AND required = 1";
        $arr = array();

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            while($tmp = $res->fetch_object()) {
                $loopmod = $this->load_module($tmp->id);
                $loopmod->reset_step();
            }
        }

        $qry = sprintf('DELETE FROM projects WHERE owner = %d AND id = %d AND name = "%s";',
            0,
            $this->user_id,
            (int)$id,
            $this->db->real_escape_string($name));

        $res = $this->db->query($qry);
    }

    public function set_current($id) {
        $id = intval($id);
        $qry = "SELECT id, name, description FROM projects WHERE id=".$id." AND owner='".$this->user_id."';";
        $res = $this->db->query($qry);
        if($res && $res->num_rows==1) {
            $this->current = $res->fetch_object();
        } else { exit(); }
        $this->init_steps();
    }

    public function load_module($step_id) {
        $qry = "SELECT module_id, contfrom_id FROM steps WHERE id=".$step_id.";";
        $res = $this->db->query($qry);
        if($res && $res->num_rows==1) {
            $tmp_data = $res->fetch_object();
            $module_id = $tmp_data->module_id;
            $contfrom_id = $tmp_data->contfrom_id;
        } else {
            echo "error: Could not find step.";
            exit();
        }

        if ($module_id > 0) {

            $class_name = "mod_".$module_id;
            require_once(SITEHTML."class/module.class.php");
            require_once(SITEHTML."modules/".$class_name.".class.php");
            return new $class_name($this->db, $this->lingual, $this->iso, $this->user_id, $this->current->id, $step_id, $module_id, $contfrom_id);
        } else {
            echo "module not found";
            exit();
        }
    }

    public function get_log() {
        /* Get data, and existing */
        $qry = "SELECT users.username, steps.name, msg, stamp FROM log LEFT JOIN users ON users.id = log.user_id LEFT JOIN steps ON steps.id = log.step_id WHERE project_id = ".$this->current->id." ORDER BY stamp DESC";
        $res = $this->db->query($qry);
        $html = "";
        if($res && $res->num_rows) {
            $html .= '<table id="history"><tr><th>user</th><th>step</th><th>action</th><th>time</th></tr>';
            while($log = $res->fetch_object()) {
                $html .= "<tr><td>".$log->username."</td><td>".$this->lingual->get_text($log->name)."</td><td>".$log->msg."</td><td>".$log->stamp."</td></tr>";
            }
            $html .= '</table>';
        }
        return $html;
    }

    public function get_cur_id() {
        if(empty($this->current))
            return "Not set!";
        else
            return $this->current->id;
    }

    public function get_cur_name() {
        if(isset($this->current))
            return $this->current->name;
        else
            return "Not set!";
    }

    public function get_cur_description() {
        if(!empty($this->current))
            return $this->current->description;
        else
            return "Not set!";
    }

    //should use temlpalate id as input
    private function init_steps() {
        $refs = array();
        $qry = "SELECT steps.id, steps.pid, steps.num, steps.name FROM `steps` WHERE steps.template_id = 1 ORDER BY pid, num;";
        $res = $this->db->query($qry);

        if($res && $res->num_rows) {
            while($data = $res->fetch_object()) {
                $thisref = &$refs[ $data->id ];
                $thisref['id'] = $data->id;
                $thisref['pid'] = $data->pid;
                $thisref['name'] = $this->lingual->get_text($data->name);

                if ($data->pid == 0) {
                    $this->steps[ $data->id ] = &$thisref;
                } else {
                    $refs[$data->pid]['children'][ $data->id ] = &$thisref;
                }
            }
        } else {
            echo 'Error when constructing tree...';
        }
    }

    public function get_steps() {
        return $this->steps;
    }

    public function print_steps($arr) {
        $html = '<ol>';

        foreach ($arr as $v) {
            $html .= '<li><a href="'.SITEISO.'project/'.$this->get_cur_id().'/steps/'.$v['id'].'/" title="'.$v['name'].'">'.$v['name'].'</a>';
            if (array_key_exists('children', $v)){
                $html .= $this->print_steps($v['children']);
            }
            $html .= '</li>';
        }
        $html .= '</ol>';
        return $html;
    }

    public function get_step_info($uid) {
        $refs = array();
        $qry = "SELECT module_id, lingual.text as name FROM `steps` LEFT JOIN lingual ON lingual.id = steps.name WHERE steps.template_id = 1 AND iso='".$this->iso."' AND steps.id = '".$uid."' LIMIT 1;";
        $res = $this->db->query($qry);

        if($res && $res->num_rows)
            return $res->fetch_object();
        else
            return null;
    }

    public function bottomstep($arr = array(), $first = true) {
        if ($first && empty($arr)) {
            $qry = 'SELECT steps.id, steps.pid, steps.num, steps.name, step_status.status status, required FROM `steps` LEFT JOIN step_status ON step_status.step_id = steps.id AND step_status.project_id = '.$this->get_cur_id().' WHERE steps.template_id = 1 ORDER BY pid, num;';
            $res = $this->db->query($qry);
            $isdone = true;
            if($res && $res->num_rows) {
                while($row = $res->fetch_assoc()){
                    $arr[$row['id']] = $row;
                }

            } else {
                echo 'Error when constructing tree...';
            }

            $tree = array();
            foreach ($arr as $step_id => &$step) {
                if (!$step['pid'] || !array_key_exists($step['pid'], $arr)) {
                    $tree[] = &$step;
                } else {
                    $arr[$step['pid']]['children'][] = &$step;
                }
            }
            $arr = $tree;
        }
        $arrstep = array();
        foreach ($arr as $v) {
            $size=count($arrstep);
            $arrstep[$size]['id'] = $v['id'];
            $arrstep[$size]['name'] = $this->lingual->get_text($v['name']);
            $arrstep[$size]['url'] = SITEISO.'project/'.$this->get_cur_id().'/steps/'.$v['id'].'/';
            $arrstep[$size]['status'] = ($v['required'] == 1 ? $v["status"] : -1);

            if (array_key_exists('children', $v)){
                $arrstep = array_merge($arrstep, $this->bottomstep($v['children'], false));
            }

        }
        return $arrstep;
    }

    public function print_stepmenu($arr = array(), $first_time = true) {
        if($first_time) {
            if(count($arr) == 0)
                $arr = $this->get_steps();
        }
        $html = '<ul>';
        $i = 1;
        foreach ($arr as $v) {

            $html .= '<li>';
            if(!$first_time) {
                $html .= '<a href="'.SITEISO.'project/'.$this->get_cur_id().'/steps/'.$v['id'].'/" title="'.$v['name'].'">'.$v['name'].'</a>';
            }
            if (array_key_exists('children', $v)){
                $html .= $this->print_stepmenu($v['children'], false);
            }
            if($first_time) {
                $html .= '<a href="'.SITEISO.'project/'.$this->get_cur_id().'/steps/'.$v['id'].'/" title="'.$v['name'].'">'.$this->lingual->get_text(1311)." ".($i++).'</a>';
            }
            $html .= "</li>";
        }
        $html .= '</ul>';
        return $html;
    }

    public function get_step_module($uid) {
        $refs = array();
        $qry = "SELECT lingual.text as name FROM `steps` LEFT JOIN lingual ON lingual.id = steps.name WHERE steps.template_id = 1 AND iso='".$this->iso."' AND steps.uid = '".$uid."' LIMIT 1;";
        $res = $this->db->query($qry);

        if($res && $res->num_rows)
            return $res->fetch_object()->name;
        else
            return "Not found";
    }

    public function get_summary() {
        $refs = array();
        $qry = "SELECT steps.id, steps.pid, steps.num, steps.name, module_id FROM `steps` WHERE steps.template_id = 1 ORDER BY pid, num;";
        $res = $this->db->query($qry);

        if($res && $res->num_rows) {
            while($data = $res->fetch_object()) {
                echo $data->id;
            }
        }
    }

}
?>
