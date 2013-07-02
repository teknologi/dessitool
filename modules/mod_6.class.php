<?php
class mod_6 extends Module {
    private $saved_data = array();
    private $questions = array();

    public function load() {
        echo '<h2>'.$this->get_header().'</h2>';
        echo '<p>'.$this->get_paragraph().'</p><br />';

        /* Start of form */
        if (isset($_GET["var1"]))
            $safe["var1"] = (int)$_GET["var1"];
        if (isset($_GET["var2"]))
            $safe["var2"] = (int)$_GET["var2"];
        $dimcrit = array();

        $qry = sprintf('SELECT dim.id, dim.dimension, crit.id as crit_id, crit.title, crit.question, crit.explanation, crit.description FROM mod6_dim AS dim LEFT JOIN mod6_crit AS crit ON dim.id = crit.dimension_id WHERE dim.project_id = "%1$d" AND dim.step_id = "%2$d"',
            $this->get_project_id(),
            $this->get_step());
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            while($dim = $res->fetch_object()) {
                /* Set visibility */
                if(!isset($dimcrit[$dim->id][0]))
                    $dimcrit[$dim->id][0] = $dim->dimension;
                $dimcrit[$dim->id][1][$dim->crit_id] = array('title' => $dim->title, 'question' => $dim->question, 'explanation' => $dim->explanation, 'description' => $dim->description);
            }
        }

        $found_element = ((!isset($safe["var1"]) && !isset($safe["var2"])) ||
                (isset($safe["var1"]) && !isset($safe["var2"]) && isset($dimcrit[$safe["var1"]][0])) ||
                (isset($safe["var1"]) && isset($safe["var2"]) && isset($dimcrit[$safe["var1"]][1][$safe["var2"]])));

        echo '<form method="post" class="dimension">';
        echo '<p>'.$this->lingual->get_text(1013).'<a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1329).'</span></a></p>';
        echo '<select size="10" ONCHANGE="location = this.options[this.selectedIndex].value;" name="dimension">';
        foreach($dimcrit as $dim_id => $dimcontent) {
            $dim_url = SITEISO.'project/'.$this->get_project_id().'/steps/'.$this->get_step().'/'.$dim_id.'/';
            echo '<option value="'.$dim_url.'"'.((isset($safe["var1"]) && $safe["var1"]==$dim_id)?' selected="selected"':'').'>'.$dimcontent[0].'</option>';
        }
        echo '</select>';
        echo '<input type="hidden" name="save">';

        if(isset($safe["var1"]) && $found_element) {
            echo '<a href="#" class="remove">remove</a>';
        }
        echo '</form>';
        if(count($dimcrit)==0) {
            echo '<br /><br /><form method="post"><input type="submit" name="save" value="Import default" style="width:320px;" /><input type="hidden" name="import" /></form>';
        }

        if(isset($safe["var1"]) && $found_element) {
            echo '<form method="post" class="criteria" action="../">';
            echo '<p>'.$this->lingual->get_text(1014).'<a href="#" class="questext"><span class="hide">'.$this->lingual->get_text(1330).'</span></a></p>';
            echo '<select size="10" ONCHANGE="location = this.options[this.selectedIndex].value;" name="criterion">';
            if(isset($safe["var1"]) && isset($dimcrit[$safe["var1"]][1])) {
                foreach($dimcrit[$safe["var1"]][1] as $crit_id => $critcontent) {
                    echo '<option value="'.SITEISO.'project/'.$this->get_project_id().'/steps/'.$this->get_step().'/'.$safe["var1"].'/'.$crit_id.'/"'.((isset($safe["var2"]) && $safe["var2"]==$crit_id)?' selected="selected"':'').'>'.$critcontent["title"].'</option>';
                }
            }
            echo '</select>';
            echo '<input type="hidden" name="save">';

            if (isset($safe["var2"]))
                echo '<a href="#" class="remove">remove</a>';
            echo '</form>';
        }


//Javascript should be dynamic also
        echo '<script type="text/javascript">'."\n".'$(document).ready(function() {'."\n";
        echo '$(".remove").click(function() {'."\n";
            echo 'if (confirm('."'Are you sure you want to remove the item ?'".')) { $(this).parent().submit(); } else { return false }';
        echo '});';
        echo '});'."\n".'</script>';
    }

    public function save() {

        $this->set_status(1);

        if(isset($_POST["dimension"])) {
            $qry = sprintf('DELETE FROM mod6_crit WHERE project_id = "%d" AND step_id = "%d" AND dimension_id = "%d";',
                $this->get_project_id(),
                $this->get_step(),
                (int)$_GET["var1"]);
            $this->db->query($qry);

            $qry = sprintf('DELETE FROM mod6_dim WHERE project_id = "%d" AND step_id = "%d" AND id = "%d" LIMIT 1;',
                $this->get_project_id(),
                $this->get_step(),
                (int)$_GET["var1"]);
            $this->db->query($qry);
        } else if(isset($_POST["criterion"])) {
            $crtid = explode("/",$_POST["criterion"]);
            $crtid = (int)$crtid[count($crtid)-2];
            if($crtid > 0) {
                $qry = sprintf('DELETE FROM mod6_crit WHERE project_id = "%d" AND step_id = "%d" AND dimension_id = "%d" AND id = "%d";',
                    $this->get_project_id(),
                    $this->get_step(),
                    (int)$_GET["var1"],
                    (int)$crtid);
                $this->db->query($qry);

                $qry = sprintf('UPDATE mod6_crit SET num = (num - 1) WHERE project_id = "%d" AND step_id = "%d"  AND dimension_id = "%d" AND id > "%d";',
                    $this->get_project_id(),
                    $this->get_step(),
                    (int)$_GET["var1"],
                    $crtid);
                $this->db->query($qry);
            }

        } else if(isset($_POST["import"])) {

            $qry = sprintf('SELECT dessi_dimensions.id, lingual.text FROM dessi_dimensions LEFT JOIN lingual ON dessi_dimensions.dimension = lingual.id WHERE lingual.iso = "%s"', LANG_ISO);
            $res = $this->db->query($qry);
            if($res && $res->num_rows) {
                while($tmp = $res->fetch_object()) {
                    $qry = sprintf("INSERT INTO mod6_dim (project_id, step_id, dimension) VALUES('%d', '%d', '%s');",
                        $this->get_project_id(),
                        $this->get_step(),
                        $tmp->text);
                    $this->db->query($qry);
                    $dim_id = $this->db->insert_id;
                    $qry = sprintf("SELECT title, question, explanation, description FROM dessi_criteria WHERE dimension_id = '%d';", $tmp->id);
                    $num = 1;
                    $result = $this->db->query($qry);
                    while($cri = $result->fetch_object()) {
                        $qry = sprintf("INSERT INTO mod6_crit (project_id, step_id, dimension_id, num, title, question, explanation, description) VALUES('%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s');",
                                $this->get_project_id(),
                                $this->get_step(),
                                $dim_id,
                                $num++,
                                $this->db->real_escape_string($this->lingual->get_text($cri->title)),
                                $this->db->real_escape_string($this->lingual->get_text($cri->question)),
                                $this->db->real_escape_string($this->lingual->get_text($cri->explanation)),
                                $this->db->real_escape_string($this->lingual->get_text($cri->description)));
                        $this->db->query($qry);
                    }
                }
            }
        }
    }

    public function reset_step() {

    }

    public function report(){
        return "";
    }
}
