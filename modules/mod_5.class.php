<?php
class mod_5 extends Module {
    public function load() {

        echo '<h2>'.$this->get_header().'</h2>';
        echo '<p>'.$this->get_paragraph().'</p><br />';

        /* Start of form */
        echo '<form method="post"><input type="hidden" name="save" /><span>Status:</span><span style="display:inline-block; width:380px; text-align:right; margin-right:10px;';
        $qry = "SELECT expire FROM mod4_status WHERE project_id=".$this->get_project_id()." AND step_id=".$this->get_step().";";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            $expire = $res->fetch_object()->expire;
            if(time() < strtotime($expire)) {
                //active
                echo ' color:green;">Active until '.date("F j, Y, H:i",strtotime($expire)).'</span><input type="submit" name="act" value="Deactivate" />';
            } else {
                //expired
                echo ' color:grey;"> Inactive since '.date("F j, Y, H:i",strtotime($expire)).'</span><input type="submit" name="act" value="Reactivate" />';
            }
        } else {
            echo 'color:grey;"> Inactive</span><input type="submit" name="act" value="'.($this->get_contfrom_id()?"Import data and activate":"Activate").'" />';
            echo '</form>';
            return "";
        }
        echo '</form>';
        echo '<br />';
        $qry = "SELECT status.id, mod4_group.link, mod6_dim.dimension, count(mod6_crit.id) num_dims FROM mod4_status status LEFT JOIN mod4_group ON status.id = mod4_group.mod4_id LEFT JOIN mod6_dim ON mod4_group.dimension_id = mod6_dim.id LEFT JOIN mod6_crit ON mod6_dim.id = mod6_crit.dimension_id WHERE status.project_id=".$this->get_project_id()." AND status.step_id=".$this->get_step()." GROUP BY mod6_dim.id HAVING num_dims > 0;";

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            echo '<table style="width:100%"><tr><th>'.$this->lingual->get_text(1326).'</th><th>'.$this->lingual->get_text(1327).'</th><th>'.$this->lingual->get_text(1328).'</th></tr>';

            while($dimension = $res->fetch_object()) {
                /* Set visibility */
                echo '<tr><td>'.($dimension->dimension).'</td><td><a href="'.SITEISO.'link/'.$dimension->id.'-'.$dimension->link.'/" target="_blank">Direct link</a></td><td><a href="mailto:?subject=Dessi login&body=Dessi link: '.SITEISO.'link/'.$dimension->id.'-'.$dimension->link.'/">E-mail</a></td></tr>';

            }
            echo "</table>";
            echo '<p class="assessmentdescr">'.$this->lingual->get_text(1331).'</p>';
            echo '<form method="post">';
            echo '<input type="hidden" name="save" value="generate" />';
            echo '<input type="submit" name="generate" onClick="if (!confirm('."'Are you sure you want to regenerate links? All previous links will be replaced.'".')) { return false; }" value="Generate new links" />';
            echo '</form>';

        }
        echo '<p class="assessmentdescr">'.$this->lingual->get_text(1332).'</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="save" value="reset" />';
        echo '<input type="submit" onClick="if (!confirm('."'Are you sure you want to reset? All data concerning Assessessment round 1 will be lost.'".')) { return false; }" value="Reset" />';
        echo '</form>';

    }
    public function save() {
        if(isset($_POST["act"])) {
            $this->set_status(1);

            if($_POST["act"]=="Activate" || $_POST["act"]=="Import data and activate") {
                $qry = sprintf("INSERT INTO mod4_status (project_id, step_id, expire) VALUES ('%s', '%s', '%s');",
                    $this->get_project_id(),
                    $this->get_step(),
                    date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +1 day")));
                $res = $this->db->query($qry);

                if($res) {
                    $ins_id = $this->db->insert_id;
                    $this->generate_new_links($ins_id);

                    if ($_POST["act"]=="Import data and activate") {
                        $qry = sprintf("INSERT INTO mod4_group_input (mod4_id, dimension_id, criteria, investment, message, open_discussion, summary, rating) (SELECT '%d', dimension_id, criteria, investment, message, open_discussion, summary, rating FROM mod4_group_input as input LEFT JOIN mod4_status status ON status.id = input.mod4_id WHERE status.project_id = '%d' AND status.step_id = '%d');",
                            $ins_id,
                            $this->get_project_id(),
                            $this->get_contfrom_id());

                        if (!$this->db->query($qry)){
                            echo 'empty?';//$qry;
                        }
                    }

                }
            } else if($_POST["act"]=="Reactivate") {
                $qry = sprintf("UPDATE mod4_status SET expire='%s' WHERE project_id=%s AND step_id=%s;",
                    date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +1 day")),
                    $this->get_project_id(),
                    $this->get_step());
                $res = $this->db->query($qry);
            } else if($_POST["act"]=="Deactivate") {
                $qry = sprintf("UPDATE mod4_status SET expire='%s' WHERE project_id=%s AND step_id=%s;",
                    date('Y-m-d H:i:s', time()),
                    $this->get_project_id(),
                    $this->get_step());
                $res = $this->db->query($qry);
            }



        } else if($_POST["save"]=="generate") {
            $qry = sprintf("SELECT id FROM mod4_status WHERE project_id='%s' AND step_id='%s';",
                $this->get_project_id(),
                $this->get_step());
            $res = $this->db->query($qry);
            if($res && $res->num_rows) {
                $this->generate_new_links($res->fetch_object()->id);
            } else {
                //something is wrong, this should be logged.
            }
        } else if($_POST["save"]=="reset") {
            $this->reset_step();
        }
    }

    public function reset_step() {
        //remove from...a
        $qry = 'SELECT id FROM mod4_status WHERE project_id = '.$this->get_project_id().' AND step_id = '.$this->get_step().";";
        $res = $this->db->query($qry);
        if ($res && $res->num_rows == 1) {
            $mod4_id = $res->fetch_object()->id;
            $this->db->query("DELETE FROM mod4_status WHERE mod4_status.id = ".$mod4_id." LIMIT 1;");
            $this->db->query("DELETE FROM mod4_group WHERE mod4_group.mod4_id = ".$mod4_id.";");
            $this->db->query("DELETE FROM mod4_group_input WHERE mod4_group_input.mod4_id = ".$mod4_id.";");
        }
    }

    public function generate_new_links($id) {
        $qry = "SELECT mod4_id FROM mod4_group WHERE mod4_id=".$id.";";
        $res = $this->db->query($qry);
        $exist = ($res && $res->num_rows);

        $qry = sprintf("SELECT id FROM mod6_dim WHERE project_id = '%d' AND step_id = '16';", $this->get_project_id());
        $result = $this->db->query($qry);
        if($result && $result->num_rows) {
            while($dimension = $result->fetch_object()) {
                if($exist) {
                    $qry = sprintf("UPDATE mod4_group SET link='%s' WHERE mod4_id='%s' AND dimension_id='%s';",
                        md5($this->get_project_id().$this->get_step().$dimension->id.time()),
                        $id,
                        $dimension->id);
                    $res = $this->db->query($qry);
                } else {
                    $qry = sprintf("INSERT INTO mod4_group (mod4_id, dimension_id, link) VALUES ('%s', '%s', '%s');",
                        $id,
                        $dimension->id,
                        md5($this->get_project_id().$this->get_step().$dimension->id.time()));
                    $res = $this->db->query($qry);
                }
            }
        }
    }

    public function report(){

        echo '<h2 id="subsection'.$this->get_step().'">'.$this->get_header().'</h2>';

/*
$id=(int)$_GET["id"];
$link=$db->real_escape_string($_GET["ln"]);
$criterion=(int) $_GET["criterion"];
$investment=(int) $_GET["investment"];
*/
		$rating = array($this->lingual->get_text(1259),$this->lingual->get_text(1260),$this->lingual->get_text(1261),$this->lingual->get_text(1262),$this->lingual->get_text(1263));

        $qry = 'SELECT dim.dimension dimension, crit.title criteria, crit.question question, invest.name investment, input.message, input.open_discussion discussion, input.summary summary, input.rating rating FROM mod4_group_input input LEFT JOIN mod4_status status ON input.mod4_id = status.id LEFT JOIN mod6_dim dim ON dim.id = input.dimension_id LEFT JOIN mod6_crit crit ON crit.dimension_id = dim.id AND crit.num = input.criteria LEFT JOIN dessi_investments invest ON invest.project_id = status.project_id AND invest.id = input.investment WHERE status.project_id = '.$this->get_project_id().' AND status.step_id = '.$this->get_step().' AND invest.enabled=1 ORDER BY crit.dimension_id, crit.num';

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            $tmp_dim = $tmp_crit = "";
            while($output = $res->fetch_object()) {
                if ($tmp_dim != $output->dimension) {
                    $tmp_dim = $output->dimension;
                    echo '<h3><span class="smallheader">Dimension:</span><span class="smallmsg">'.$output->dimension.'</span></h3>';
                }
                if ($tmp_crit != $output->criteria) {
                    $tmp_crit = $output->criteria;
                    echo '<br /><h4><span class="smallheader">Criteria:</span><span class="smallmsg">'.$output->criteria.'</span></h4>';
                    echo '<p><span class="smallheader">Question:</span><span class="smallmsg">'.$output->question.'</span></p>';
                }
                echo '<p><span class="smallheader">Investment:</span><span class="smallmsg">'.$output->investment.'</span></p>';
                echo '<p><span class="smallheader">Open discussion:</span><span class="smallmsg">'.$output->discussion.'</span></p>';
                echo '<p><span class="smallheader">Summary:</span><span class="smallmsg">'.$output->summary.'</span></p>';
                echo '<p><span class="smallheader">Message:</span><span class="smallmsg">'.$output->message.'</span></p>';
                echo '<p><span class="smallheader">Rating:</span><span class="smallmsg">'.$rating[$output->rating+2].'</span></p><br />';
            }
        }
    }
}