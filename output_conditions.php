<?php
    $output = '
        <table>
            <th style="text-align:right">AK 1</th>
            <th></th>
            <th style="text-align:left">AK 2</th>';
    if(isset($conditions) && $conditions !== NULL) {
        foreach($conditions as $cond) {
            $class = 'Ok';
            if(isset($table->unresolved[$cond[0]]) && isset($table->unresolved[$cond[1]]))
                $class = 'Warning';
            $output .= '
            <tr>
                <td class="'.$class.'" style="text-align:right">'.$cond[0].'</td>
                <td class="'.$class.'"><a href="writeData.php?deleteCondition&AK1='.$cond[0].'&AK2='.$cond[1].'"><img src="application-exit.png" style="width:15px;margin:3px"/></a></td>
                <td class="'.$class.'">'.$cond[1].'</td>
            </tr>';
        }
    }
    $output .= '</table>';
    echo $output;
?>