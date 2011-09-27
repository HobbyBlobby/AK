<?php
session_start();

if(!isset($_SESSION["username"])) {
    header("Location: ./login.php");
}

function replaceOldName(&$conditions, &$slots, $oldName, $name) {
    foreach($conditions as $key=>$cond) {
        if($cond[0] == $oldName)
            $conditions[$key][0] = $name;
        if($cond[1] == $oldName)
            $conditions[$key][1] = $name;
    }
    if(isset($slots[$oldName])) {
        $slots[$name] = $slots[$oldName];
        unset($slots[$oldName]);
    }
}

if(isset($_REQUEST["saveEntries"])) {
    $condtions = $newCond = array();
    if(file_exists("AK.data")) include("AK.data"); // import the conditions data
    $AKonSlots = array();
    $akData = array(); // rewrite all akData entries
    foreach($_REQUEST as $key=>$value) {
        if(preg_match('/^AKName_/', $key)) {
            $name = preg_replace('/^AKName_/', '', $key);
            $ak = $_REQUEST["AKName_$name"];
            if(isset($_REQUEST["AK_$name"]) && !empty($value)) {
                if(empty($_REQUEST["AK_$name"])) {
                    $_REQUEST["AK_$name"] = 0;
                }
                $akData[$ak] = $_REQUEST["AK_$name"];
                $oldName = preg_replace('/_/', ' ', $name);
                if($oldName != $ak) {
                    replaceOldName($conditions, $AKonSlots, $oldName, $ak);
                }
            }
        }
        if(preg_match('/^AKcond_/', $key)) {
            $name = preg_replace('/^AKcond_/', '', $key);
            $ak = $_REQUEST["AKName_$name"];
            $newCond[] = $ak;
        }
        if(preg_match('/^AKslot_/', $key)) {
            $name = preg_replace('/^AKslot_[0-9]*_/', '', $key);
            $ak = $_REQUEST["AKName_$name"];
            $entries = explode("_", $key);
            $slot = $entries[1];
            $AKonSlots[$ak][$slot] = $slot;
        }
    }
    while(count($newCond) > 1) {
        $cond = array_pop($newCond);
        foreach($newCond as $c) {
            $conditions[] = array($cond, $c);
        }
    }
    writeData($akData, $conditions, $AKonSlots);
    header("Location: index.php");
}   

if(isset($_REQUEST["deleteEntry"])) {
    if(file_exists("AK.data")) {
        $akData = $conditions = $AKonSlots = array();
        include("AK.data");
        $name = preg_replace('/^AK_/', '', $_REQUEST["deleteEntry"]);
        if(isset($akData[$name])) {
            unset($akData[$name]);
        }
        foreach($conditions as $key=>$cond) {
            if($cond[0] == $name || $cond[1] == $name) unset($conditions[$key]);
        }
        writeData($akData, $conditions, $AKonSlots);
        header("Location: index.php");
    }
}

if(isset($_REQUEST["deleteCondition"])) {
    $akData = $conditions = $AKonSlots = array();
    include("AK.data");
    if(isset($_REQUEST["AK1"]) && !empty($_REQUEST["AK1"]) && isset($_REQUEST["AK2"]) && !empty($_REQUEST["AK2"])) {
        foreach($conditions as $key=>$cond) {
            if(($cond[0] == $_REQUEST["AK1"] && $cond[1] == $_REQUEST["AK2"]) || ($cond[1] == $_REQUEST["AK1"] && $cond[0] == $_REQUEST["AK2"])) {
                unset($conditions[$key]);
            }
        }
        writeData($akData, $conditions, $AKonSlots);
        header("Location: index.php");
    }
}

function writeData($akData, $conditions, $AKonSlots) {
    $file = fopen("AK.data", "w");
    fwrite($file, '<?php' . "\n");
    fwrite($file, '$akData = ' . var_export($akData, true) .  ';' . "\n");
    fwrite($file, '$conditions = ' . var_export($conditions, true) .  ';' . "\n");
    fwrite($file, '$AKonSlots = ' . var_export($AKonSlots, true) .  ';' . "\n");
    fwrite($file, '?>' . "\n");
    fclose($file);
}

?>
