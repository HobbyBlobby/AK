<?php 
    session_start();

    include_once("./class_Table.php");
    include_once("./class_AK.php");

    $table = new Table();
    
    $akData = array();
    $fileName = "AK.data";
    if(file_exists($fileName)) {
        $akData = $conditions = array();
        include($fileName);
        
        $fillSlots = false;
        if(empty($AKonSlots)) {
            $fillSlots = true;
        }
        
        for($i = 0; $i < count($akData); ++$i) {
            $keys = array_keys($akData);
            $ak = $table->addAK($keys[$i]);
            if($fillSlots || !isset($AKonSlots[$keys[$i]])) {
                for($j = 1; $j <=5; ++$j) {
                    $AKonSlots[$keys[$i]][$j] = $j;
                    $ak->possibleSlots[$j] = $j;
                }
            } else {
                $ak->possibleSlots = $AKonSlots[$keys[$i]];
            }
            $ak->partWishes = $akData[$keys[$i]];
        }
        $table->conditions = $conditions;
        if(isset($_REQUEST["exactCalculation"])) $table->testAllPossibilities();
        $table->optimiseAKs();
    }
    
?>

<HEAD>
<style type="text/css">
    .Warning {background:#F55;}
</style>
</HEAD>

<BODY>
<?php
    if(isset($_REQUEST["logout"]) && isset($_SESSION["username"])) {
        unset($_SESSION["username"]);
    }
    include("./account.php");
    if((isset($_REQUEST['passphrase']) && $_REQUEST['passphrase'] == $_PASSPHRASE) && (isset($_REQUEST['username']) && $_REQUEST['username'] == $_USERNAME)) {
        $_SESSION["username"] = $_USERNAME;
    }
?>
<div style="position:fixed;top:-0px;left:-0px;background-color:#10DA18; width:100%; height:30px;"></div>
<div style="margin:40px;text-align:center;float:left">
    <?php $table->draw(); ?>
</div>
<?php
    if(isset($_SESSION["username"])) {
        echo '<div style="width:400px;float:left;margin:40px 50px">';
            include("./output_inputForm.php");
        echo '</div>';
        echo '
            <div style="top:2px;position:fixed">
                <form action="./index.php" method="post">
                    <input name="logout" value="Logout" type="submit">
                </form>
            </div><br clear="all"/>
        ';
        echo '<div style="width:50%;float:left;margin: 40px 25%">';
            include("./output_conditions.php");
        echo '</div>';
    } else {
        echo '<div style="width:500px;;float:left;margin-left:10px";text-align:center;>';
            include("./output_loginForm.php");
        echo '</div>';
    }
    echo '
        <div style="top:2px;right:50px;position:fixed">
			Welcome to the AK tool
        </div>
    ';
//             AKs im Wiki: <a href="https://vmp.ethz.ch/zapfwiki/index.php/SoSe11_Arbeitskreise">https://vmp.ethz.ch/zapfwiki/index.php/SoSe11_Arbeitskreise</a>
?>
<br clear="all">
<div style="position:fixed;bottom:-0px;left:-0px;background-color:#10DA18; width:100%; height:30px;"></div>
</BODY>
