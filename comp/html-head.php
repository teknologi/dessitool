<!DOCTYPE html>
<html lang='en' xmlns='http://www.w3.org/1999/xhtml' >
<head>
<?php
    echo '<meta charset="'.CHARSET.'" />'."\n";
    /* echo '<meta name="content-language" content="'.LANG_ISO.'" />'."\n"; */
    if (isset($info["description"])) echo '<meta name="description" content="'.$info["description"].'" />'."\n";
    if (isset($info["keywords"])) echo '<meta name="keywords" content="'.$info["keywords"].'" />'."\n";
    echo '<title>'.SITENAME.(isset($info["title"])?' - '.$info["title"]:'')."</title>\n";

?>
<meta name="robots" content="noindex, nofollow" />
<link rel='shortcut icon' href='<?= SITEURL?>favicon.ico' />
<link rel='apple-touch-icon' href='<?= SITEURL?>img/touch-icon.png' />

<link href='<?= SITEURL ?>css/general.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='<?= SITEURL?>script/jquery-1.8.3.min.js'></script>
<script type='text/javascript' src='<?= SITEURL?>script/general.js'></script>
<?php
if (isset($info["js"])) foreach($info["js"] as $js_src) {
    echo '<script type="text/javascript" src="'.$js_src.'"></script>';
}
if (isset($info["css"])) foreach($info["css"] as $css_src) {
    echo '<link rel="stylesheet" type="text/css" href="'.$css_src.'" />';
}
?>

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-33597113-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
</head>
<body>
<header class="head">
    <div class="lftcol"><div id="logo" class="lftcol"></div></div><div id="topcontent" class="bigcol">
        <div id="siteinfo">
            <h1><a href="<?= SITEISO?>" title="DESSI">DESSI</a></h1>
            <p><?= $lingual->get_text(2409) ?></p>
        </div><div id='topright'>
            <div id='corner'>
                <div id="usrname">
<?php
if(isset($user)) {
    echo '<a href="'.SITEISO.'">'.$user->username.'</a>';
} else {
    echo '<a href="'.SITEISO.'">'.$lingual->get_text(2446).'</a>';
}
?>
                </div>
                <span class='separator'></span>
                <!-- Flag icons provided by "http://www.gosquared.com/" - thank you gosquared -->
                <div>
<?php
    $qry = "SELECT iso, language, img_url FROM languages";
    $result = $db->query($qry);
    if($result && $result->num_rows) {
        $la = array();
        $la[0] = array("iso", "language", "img_url");
        while($get = $result->fetch_assoc()) {
            if(LANG_ISO == $get["iso"])
                $la[0] = $get;
            else
                $la[] = $get;
        }
    }
    $page_url = "http://";
    $page_url .= $_SERVER["SERVER_NAME"];
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= ":".$_SERVER["SERVER_PORT"];
    }
    $page_url .= $_SERVER["REQUEST_URI"];

echo '<a href="#" id="language"><img class="flag" src="'.SITEURL.'img/flags/'.strtoupper($la[0]["iso"]).'.png" alt="flag" />'.$la[0]["language"].'</a><ul id="lang" class="hide">';

    for($i = 1; $i < count($la); $i++) {
        echo '<li><a href="'.str_replace(SITEISO, SITEURL.$la[$i]["iso"].'/', $page_url).'" title="'.$la[$i]["language"].'"><img class="flag" src="'.SITEURL.'img/flags/'.strtoupper($la[$i]["iso"]).'.png" alt="flag" />'.$la[$i]["language"].'</a>'.'</li>';
    }

    echo '</ul>';
?>
                </div>
            </div>
        </div>
    </div>
<?php
      /* if(isset($breadcrumbs)) echo $breadcrumbs; */
?>
</header>
