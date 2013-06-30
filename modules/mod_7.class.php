<?php
class mod_7 extends Module {
    public function load() {

        echo '<h2>'.$this->get_header().'</h2>';
        echo '<p>'.$this->get_paragraph().'</p><br />';

        echo '<input id="refresher" type="checkbox" name="refresher" checked /><label for="refresher">'.$this->lingual->get_text(1264).'</label><br />';
        $qry = 'SELECT step1.id, step1.contfrom_id, step1.pid FROM steps step1 JOIN steps step2 ON step1.pid = step2.pid AND step1.num <= step2.num AND step2.id = '.$this->get_step().' WHERE step1.module_id=7 AND step1.template_id=1 ORDER BY step1.num';

        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            $i = 1;
            while($comp_round = $res->fetch_object()) {
                if($res->num_rows > 1) {
                    echo '<input id="compare'.$i.'" type="radio" name="compare" value="'.$comp_round->contfrom_id.'"'.($this->get_step()==$comp_round->id ? ' checked' : '').' /><label for="compare'.$i.'">'.$this->lingual->get_text(1333).' '.($i++).'</label><br />';
                } else {
                    $contfrom = $comp_round->contfrom_id;
                }
            }
        } else {
            exit("Could not find step data...");
        }

        echo '<a href="#" title="Go fullscreen" id="fullscreen">Fullscreen</a>';
        echo '<div id="bigtable">';
        echo $this->ajax();
        echo '</div>';

        echo '<script type="text/javascript">'."\n".'$(document).ready(function() {';
        echo 'setInterval(function(){ if ($("#refresher").attr('."'checked'".') == '."'checked'".') {';
        echo 'updatemonster();';
        echo '}} ,10000);';

        echo 'function requestFullScreen() { var elem = document.getElementById("bigtable"); var requestMethod = elem.requestFullScreen || elem.webkitRequestFullScreen || elem.mozRequestFullScreen || elem.msRequestFullScreen; if (requestMethod) { requestMethod.call(elem); } }';

        echo '$("#fullscreen").click(function() { requestFullScreen(); return false; });';
        echo '$("input[name=compare]").click(function() { updatemonster(); });';

        echo 'function updatemonster() {';
        echo '$.post("", { content: "only", assessid: ($("input[name=compare]").length ? $("input[name=compare]:checked").val() : '.(int)$contfrom.')'.' }, function(data){ $("#bigtable").html(data); });';
        echo '}';

        echo '});</script>';
    }

    public function ajax() {
        $gethtml = "";

        $qry = "SELECT id, name, description FROM dessi_investments WHERE enabled=1 AND project_id=".$this->get_project_id()." ORDER BY priority";
        $res = $this->db->query($qry);

        if($res && $res->num_rows) {
            $invest_amount = $res->num_rows;
            echo '<table class="monster"><tr><th colspan=2 rowspan=2 class="col1">'.$this->lingual->get_text(1265).'</th><th colspan='.$res->num_rows.'>'.$this->lingual->get_text(1266).'</th></tr><tr>';
            while($invest_res = $res->fetch_object()) {
                echo '<th>'.$invest_res->name.'</th>';
            }
            echo '</tr>';

            $qry = "SELECT stat.id, mod4_group.link, mod6_dim.dimension, mod6_dim.id as dimension_id FROM mod4_status stat LEFT JOIN mod4_group ON stat.id = mod4_group.mod4_id LEFT JOIN mod6_dim ON mod4_group.dimension_id = mod6_dim.id WHERE stat.project_id=".$this->get_project_id()." AND stat.step_id=".(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).';';

            $res = $this->db->query($qry);
            $invest_score = array();
            for($b=0;$b<$invest_amount;$b++) {
                $invest_score[] = 0;
            }

            if($res && $res->num_rows) {
                while($dimension = $res->fetch_object()) {
                    $prelink=SITEISO.'link/'.$dimension->id.'-'.$dimension->link;
                    $qry = 'SELECT crit.num, crit.title, invest.priority, invest.name, invest.id invest_id, input.rating, input.criteria, input.investment, input.message FROM mod6_crit AS crit LEFT JOIN dessi_investments AS invest ON invest.project_id = crit.project_id AND invest.enabled =1 LEFT JOIN mod4_status AS status ON status.project_id = crit.project_id AND status.project_id = invest.project_id AND status.step_id ='.(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).' LEFT JOIN mod4_group_input AS input ON input.mod4_id = status.id AND crit.dimension_id = input.dimension_id AND input.criteria = crit.num AND input.investment = invest.id WHERE invest.project_id ='.$this->get_project_id().' AND crit.dimension_id = '.$dimension->dimension_id.' ORDER BY crit.num, invest.priority;';


//                    $qry = 'SELECT crit.num, crit.title, invest.priority, invest.name, invest.id, input.rating, input.criteria, input.investment, input.message FROM mod6_crit AS crit LEFT JOIN dessi_investments AS invest ON invest.project_id = crit.project_id LEFT JOIN mod4_status AS status ON status.project_id = crit.project_id AND status.project_id = invest.project_id AND status.step_id = '.(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).' LEFT JOIN mod4_group_input AS input ON input.mod4_id = status.id AND crit.dimension_id = input.dimension_id AND input.criteria = crit.num AND input.investment = invest.id WHERE crit.dimension_id = '.$dimension->dimension_id.' ORDER BY crit.num, invest.priority;';

                    $result = $this->db->query($qry);
                    if($result && $result->num_rows) {
                        echo '<tr><td rowspan="'.($result->num_rows/$invest_amount).'" class="col1">'.$dimension->dimension.'</td>';
                        $dim_input = array();
                        $first = true;


                        $index = 0;
                        while($data_input = $result->fetch_object()) {
                            $index++;

                            if($index == 1) {
                                if(!$first) echo '<tr>';
                                echo '<td class="col2">'.$data_input->title.'</td>';
                            }

  //                            echo '<td><a class="rate'.(is_null($data_input->rating) ? '' : ($data_input->rating + 3)).'" href="'.$prelink.'/'.$data_input->num.'/'.$data_input->priority.'/" title="'.(is_null($data_input->message) ? 'Nothing entered' : $data_input->message).'" target="_blank">'.(is_null($data_input->rating)||($data_input->rating == -2) ? '&nbsp;' : $data_input->rating)."</a></td>";

                            echo '<td><a class="rate'.(is_null($data_input->rating) ? '' : ($data_input->rating + 3)).'" href="'.$prelink.'/'.$data_input->num.'/'.$data_input->invest_id.'/" title="'.(is_null($data_input->message) ? 'Nothing entered' : htmlentities($data_input->message)).'" target="_blank">'.'&nbsp;'."</a></td>";

                            if(!is_null($data_input->rating) && $data_input->rating != -2)
                                $invest_score[$index-1] += $data_input->rating;

                            if($index == $invest_amount)
                            echo '</tr>';

                            $first = false;
                            $index %= $invest_amount;
                        }

                    }
                }

                echo '<tr class="sum"><td colspan="2" class="col1">'.$this->lingual->get_text(1267).'</td>';
                for($b=0;$b<$invest_amount;$b++) {
                    echo '<td>'.$invest_score[$b].'</td>';
                }
                echo '</tr>';
                echo "</table>";
            }
        }
    }

    public function save() {
        if(isset($_POST["act"])) {
            if($_POST["act"]=="Activate") {
                $qry = sprintf("INSERT INTO mod4_status (project_id, step_id, expire) VALUES ('%s', '%s', '%s');",
                    $this->get_project_id(),
                    $this->get_step(),
                    date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +1 day")));
                $res = $this->db->query($qry);

                if($res) {
                    $this->generate_new_links( $this->db->insert_id);
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
        } else if(isset($_POST["generate"])) {
                $qry = sprintf("SELECT id FROM mod4_status WHERE project_id='%s' AND step_id='%s';",
                    $this->get_project_id(),
                    $this->get_step());
                $res = $this->db->query($qry);
                if($res && $res->num_rows) {
                    $this->generate_new_links($res->fetch_object()->id);
                } else {
                    //something is wrong, this should be logged.
                }
        }
    }
    public function generate_new_links($id) {
        $qry = "SELECT mod4_id FROM mod4_group WHERE mod4_id=".$id.";";
        $res = $this->db->query($qry);
        $exist = ($res && $res->num_rows);

        $qry = "SELECT id FROM dessi_dimensions;";
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

        $qry = "SELECT id, name, description FROM dessi_investments WHERE project_id=".$this->get_project_id()." AND enabled=1 ORDER BY priority";
        $res = $this->db->query($qry);
        if($res && $res->num_rows) {
            $invest_amount = $res->num_rows;
            echo '<table class="monster"><tr><th colspan=2 rowspan=2 class="col1">'.$this->lingual->get_text(1265).'</th><th colspan='.$res->num_rows.'>'.$this->lingual->get_text(1266).'</th></tr><tr>';
            while($invest_res = $res->fetch_object()) {
                echo '<th>'.$invest_res->name.'</th>';
            }
            echo '</tr>';

            $qry = "SELECT status.id, mod4_group.link, mod6_dim.dimension, mod6_dim.id as dimension_id FROM mod4_status status LEFT JOIN mod4_group ON status.id = mod4_group.mod4_id LEFT JOIN mod6_dim ON mod4_group.dimension_id = mod6_dim.id WHERE status.project_id=".$this->get_project_id()." AND status.step_id=".(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).';';

            $res = $this->db->query($qry);
            $invest_score = array();
            for($b=0;$b<$invest_amount;$b++) {
                $invest_score[] = 0;
            }

            if($res && $res->num_rows) {
                while($dimension = $res->fetch_object()) {
                    $prelink=SITEISO.'link/'.$dimension->id.'-'.$dimension->link;
                    $qry = 'SELECT crit.num, crit.title, invest.priority, invest.name, invest.id invest_id, input.rating, input.criteria, input.investment, input.message FROM mod6_crit AS crit LEFT JOIN dessi_investments AS invest ON invest.project_id = crit.project_id AND invest.enabled =1 LEFT JOIN mod4_status AS status ON status.project_id = crit.project_id AND status.project_id = invest.project_id AND status.step_id ='.(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).' LEFT JOIN mod4_group_input AS input ON input.mod4_id = status.id AND crit.dimension_id = input.dimension_id AND input.criteria = crit.num AND input.investment = invest.id WHERE invest.project_id ='.$this->get_project_id().' AND crit.dimension_id = '.$dimension->dimension_id.' ORDER BY crit.num, invest.priority;';

//                    $qry = 'SELECT crit.num, crit.title, invest.priority, invest.name, invest.id invest_id, input.rating, input.criteria, input.investment, input.message FROM mod6_crit AS crit LEFT JOIN dessi_investments AS invest ON invest.project_id = crit.project_id AND invest.enabled =1 LEFT JOIN mod4_status AS status ON status.project_id = crit.project_id AND status.project_id = invest.project_id AND status.step_id ='.(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).' LEFT JOIN mod4_group_input AS input ON input.mod4_id = status.id AND crit.dimension_id = input.dimension_id AND input.criteria = crit.num AND input.investment = invest.id WHERE invest.project_id =3 AND crit.dimension_id = '.$dimension->dimension_id.' ORDER BY crit.num, invest.priority;';
//                    $qry = 'SELECT crit.num, crit.title, invest.priority, invest.name, input.rating, input.criteria, input.investment, input.message FROM mod6_crit AS crit LEFT JOIN dessi_investments AS invest ON invest.project_id = crit.project_id LEFT JOIN mod4_status AS status ON status.project_id = crit.project_id AND status.project_id = invest.project_id AND status.step_id = '.(isset($_POST["assessid"]) ? (int)$_POST["assessid"] : $this->get_contfrom_id()).' LEFT JOIN mod4_group_input AS input ON input.mod4_id = status.id AND crit.dimension_id = input.dimension_id AND input.criteria = crit.num AND input.investment = invest.priority WHERE crit.dimension_id = '.$dimension->dimension_id.' ORDER BY crit.num, invest.priority;';

                    $result = $this->db->query($qry);
                    if($result && $result->num_rows) {
                        echo '<tr><td rowspan="'.($result->num_rows/$invest_amount).'" class="col1">'.$dimension->dimension.'</td>';
                        $dim_input = array();
                        $first = true;


                        $index = 0;
                        while($data_input = $result->fetch_object()) {
                            $index++;

                            if($index == 1) {
                                if(!$first) echo '<tr>';
                                echo '<td class="col2">'.$data_input->title.'</td>';
                            }

                            echo '<td class="rate'.(is_null($data_input->rating) ? '' : ($data_input->rating + 3)).'">&nbsp;</td>';

                            if(!is_null($data_input->rating) && $data_input->rating != -2)
                                $invest_score[$index-1] += $data_input->rating;

                            if($index == $invest_amount)
                            echo '</tr>';

                            $first = false;
                            $index %= $invest_amount;
                        }

                    }
                }

                echo '<tr class="sum"><td colspan="2" class="col1">'.$this->lingual->get_text(1267).'</td>';
                for($b=0;$b<$invest_amount;$b++) {
                    echo '<td>'.$invest_score[$b].'</td>';
                }
                echo '</tr>';
                echo "</table>";
            }
        }
    }
}
