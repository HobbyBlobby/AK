<?php
    $output = '
        <form name="wishes" method="post" action="./writeData.php">
        <table>
            <th></th>
            <th>Name des AK</th>
            <th>Anzahl der<br />Teilnehmer</th>
            <th>Be-<br />ding-<br />ung</th>
            <th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th>
            ';
    for($i = 0; $i < count($akData); ++$i) {
        $keys = array_keys($akData);
        $output .= '
            <tr>
                <td><a href="writeData.php?deleteEntry=AK_'.$keys[$i].'"><img src="application-exit.png" style="width:20px" /></a></td>
                <td><input type="text" name="AKName_'.$keys[$i].'" value="'.$keys[$i].'" style="background:#FFF" /></td>
                <td><input type="text" name="AK_'.$keys[$i].'" value="'.$akData[$keys[$i]].'" /></td>
                <td><input type="checkbox" name="AKcond_'.$keys[$i].'" value="'.$keys[$i].'" /></td>';
                for($j = 1; $j <= 5; ++$j) {
                    $checked = '';
                    $style = '';
                    if(isset($table->akFromName($keys[$i])->slot))
                        $slot = $table->akFromName($keys[$i])->slot->number;
                    if(isset($table->akFromName($keys[$i])->possibleSlots[$j])) {
                        $checked = 'checked="checked"';
                    }
                    if($slot == $j && !empty($checked)) {
                        $style .= 'background:#ADA;';
                    } elseif($slot == $j) {
                        $style .= 'background:#DAA;';
                    }
                    $output .=  '
                <td style="'.$style.'"><input type="checkbox" name="AKslot_'.$j.'_'.$keys[$i].'" value="'.$keys[$i].'" '.$checked.'/></td>';
                }
            $output .= '
            </tr>';
    }
    $output .= '
            <tr>
                <td></td><td><input type="text" name="AKName_TMP" value="" /></td><td><input type="text" name="AK_TMP" value="" /></td>
            </tr><tr>
                <td></td>
                <td style="text-align:center;" colspan="2"><input type="submit" name="saveEntries" value="Speichern" ></td>
            </tr>
        </table>
        </form>';
    echo $output;
?>