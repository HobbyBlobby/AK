<?php
include_once("./class_AK.php");
include_once("./class_Slot.php");
class Table {
    var $AKs = array();
    var $AKPositions = array();
    var $slots = array();
    var $conditions = array();
    var $bestSolution = NULL;
    var $bestSolutionVal = -1;
    var $maxAKs = 2;
    var $allCombos = array();
    var $unresolved = array();
    var $unusedAKs = array();
    function Table() {
        for($i = 1; $i <= 5; ++$i)
            $this->slots[$i] = new Slot($i);
    }
    function draw() {
        if(empty($this->AKPositions)) return;
        echo '
<table>
        ';
        echo '<tr><td colspan=6><h3>AK-Aufteilung</h3></td><tr>';
        echo '<th /><th>Slot 1</th><th>Slot 2</th><th>Slot 3</th><th>Slot 4</th><th>Slot 5</th>';
        for($row = 0; $row < 5; ++$row) {
            echo '<tr style="height:100px"><th> Raum '.($row + 1).'</th>';
            for($col = 0; $col < 5; ++$col) {
                echo '<td style="width:100px;text-align:center;'.(($col%2==0) ? 'background:#DDFFDD' : 'background:#AAFFAA').'">';
                if(!empty($this->AKPositions[$row][$col])) $this->AKPositions[$row][$col]->draw();
                echo '</td>' . "\n";
            }
            echo '</tr>';
        }
        echo '<tr style="height:60px;"><td colspan=6><h3>Nicht gesetzte AKs</h3></td><tr>';
        $counter = 0;
        foreach($this->unusedAKs as $ak) {
            if($counter % 5 == 0) {
                echo '<tr><th>Backup-Slot</th>';
            }
            echo '<td style="border:solid;width:100px;text-align:center">';
                $ak->draw();
            echo '</td>' . "\n";
            if($counter % 5 == 4) {
                echo '</tr>';
            }
        }
        if($counter % 5 != 4) {
            echo '</tr>';
        }
        echo '
</table>';
    }
    function addAK($name) {
        $ak = new AK();
        $ak->name = $name;
        array_push($this->AKs, $ak);
        return $ak;
    }
    function optimiseAKs() {
        $aks = $this->AKs;
        $count = 0;
        $this->unresolved = array();
        while(!empty($aks) && $count < 25) {
            $ak = $this->nextAK($aks);
            if(!$this->remove($aks, $ak))
                return;
            $slot = $this->bestSlotForAK($ak);
            if($slot === NULL)
                continue;
            $this->AKPositions[count($slot->AKs)][$slot->number - 1] = $ak;
            $slot->addAK($ak);
            $ak->slot = $slot;
            ++$count;
        }
        $this->unusedAKs = array();
        foreach($aks as $ak) {
            $this->unusedAKs[] = $ak;
        }
        $this->unresolvedDependencies();
        foreach($this->unresolved as $ak)
            $ak->status = 'Warning';
    }
    function nextAK($aks) {
        $countConds = array();
        $nextAKs;
        $lowest = 5;
        foreach($aks as $ak) {
            $count = count($ak->possibleSlots);
            if($count < $lowest) {
                $lowest = $count;
                $nextAKs = array($ak);
            } elseif($count == $lowest) {
                $nextAKs[] = $ak;
            }
        }
        $aks = $nextAKs;
        foreach($this->conditions as $cond) {
            if(isset($countConds[$cond[0]]))
                ++$countConds[$cond[0]];
            else
                $countConds[$cond[0]] = 1;
            if(isset($countConds[$cond[1]]))
                ++$countConds[$cond[1]];
            else
                $countConds[$cond[1]] = 1;
        }
        if(!empty($countConds)) {
            rsort($countConds);
            foreach($countConds as $cond=>$count) {
                $ak = $this->akFromName($cond, $aks);
                if($ak !== NULL) {
                    return $ak;
                }
            }
        }
        $ak = NULL;
        foreach($aks as $testak) {
            if($ak === NULL || $ak->partWishes < $testak->partWishes)
                $ak = $testak;
        }
        return $ak;
    }
    function bestSlotForAK($ak, $useOnlyPossibleSlots = true) {
        $slot = NULL;
        $tryLater = array();
        $slots = $this->slots;
        if($useOnlyPossibleSlots && count($ak->possibleSlots) > 0) {
            $slots = array();
            foreach($ak->possibleSlots as $number) {
                $slots[$number] = $this->slots[$number];
            }
        }
        foreach($slots as $testslot) {
            if(count($testslot->AKs) >= 5) continue;
            if(!$this->akIsPossible($ak, $testslot)) {
                $tryLater[] = $testslot;
                continue;
            }
            if($slot === NULL || $slot->wishSum > $testslot->wishSum) {
                $slot = $testslot;
            }
        }
        if($slot === NULL && !empty($tryLater)) {
            foreach($slots as $testslot) {
                if(count($testslot->AKs) >= 5) continue;
                if($slot === NULL || $slot->wishSum > $testslot->wishSum) {
                    $slot = $testslot;
                }
            }
        }
        if($slot === NULL && $useOnlyPossibleSlots) {
            $slot = $this->bestSlotForAK($ak, false); // rerun with all slots
        }
        return $slot;
    }
    function unresolvedDependencies() {
        foreach($this->conditions as $cond) {
            $ak1 = $this->akFromName($cond[0]);
            $ak2 = $this->akFromName($cond[1]);
            if($ak1 === NULL || $ak1->slot === NULL || $ak2 === NULL || $ak2->slot === NULL)
                continue;
            if($ak1->slot == $ak2->slot) {
                $this->unresolved[$ak1->name] = $ak1;
                $this->unresolved[$ak2->name] = $ak2;
            }
        }
    }
    
    function first(&$array) {
        if(empty($array)) return NULL;
        $keys = array_keys($array);
        return $array[$key[0]];
    }
    function remove(&$array, $elem) {
        if(empty($array) || empty($elem)) return FALSE;
        $key = array_search($elem, $array);
        if($key === FALSE) return FALSE;
        unset($array[$key]);
        return TRUE;
    }
    
    function akFromName($name, $aks = NULL) {
        if($aks === NULL) $aks = $this->AKs;
        foreach($aks as $ak) {
            if($ak->name == $name) return $ak;
        }
        return NULL;
    }
    
    function findHighestAK($aks, $slot) {
        $ak = NULL;
        foreach($aks as $a) {
            if(($ak === NULL || $a->partWishes > $ak->partWishes) && $this->akIsPossible($a, $slot)) {
                $ak = $a;
            }
        }
        return $ak;
    }
    function findLowestSlot($slots) {
        $slot = NULL;
        $somethingfound = FALSE;
        foreach($slots as $s) {
            if(count($s->AKs) <= 5 && ($slot == NULL || $s->wishSum <= $slot->wishSum)) {
                $slot = $s;
                $somethingfound = TRUE; 
            }
        }
        if($somethingfound)
            return $slot;
        else
            return NULL;
    }
    function akIsPossible($ak, $slot) {
        if($this->conditions === NULL) return true;
        $conds = array();
        $AKsAsName = array();
        foreach($slot->AKs as $a) {
            $AKsAsName[] = $a->name;
        }
        foreach($this->conditions as $condition) {
            if($condition[0] == $ak->name)
                $conds[] = $condition[1];
            if($condition[1] == $ak->name)
                $conds[] = $condition[0];
        }
        foreach($conds as $c) {
            if(array_search($c, $AKsAsName) !== FALSE) {
                return FALSE;
            }
        }
        return TRUE;
    }
    
    function testAllPossibilities() {
        $usedPlaces = min(25, count($this->AKs));
        //create Startconfig:
        $config = array();
        for($i = 0; $i < $usedPlaces; ++$i) {
            $config[$i] = $usedPlaces - 1 - $i;
        }
        $startConfig = $config;
        do {
            $this->nextConfig($config, count($this->AKs) -1);
            $this->evalSolution($config);
        } while ($startConfig != $config);
    }
    
    function nextConfig(&$config, $maxVal) {
        $pos = array();
        do { // at least one step is nessecary
            $pos = array_merge($pos, $this->stepConfig($config, count($config) - 1, $maxVal)); //step last entry
        } while($this->hasDouble($config, $pos));
    }
    function stepConfig(&$config, $pos, $maxVal) {
        $changedPositions = array($pos);
        if($config[$pos] == $maxVal) {
            $config[$pos] = 0;
            if($pos != 0)
                $changedPositions = array_merge($changedPositions, $this->stepConfig($config, $pos - 1, $maxVal));
        } else
            ++$config[$pos];
        return $changedPositions;
    }
    function hasDouble(&$config, $pos) {
        foreach($pos as $position) {
            foreach($config as $key=>$val) {
                if($key != $position && $val == $config[$position]) {
                    return true;
                }
            }
        }
        return false;
    }
    function findCombinations() {
        $combo = array();
        for($i = 0; $i < count($this->slots); ++$i) {
            $combo[] = $i;
        }
        $startCombo = $combo;
        $counter = 0;
        $allCombos[$counter] = $combo;
        if(isset($_REQUEST["debug"])) {
            $this->allCombinations($startCombo, 0, 1, 25, $counter);
            echo "Gefunde: " . $counter;
            die;
        }
    }
    function allCombinations(&$config, $pos, $minVal, $maxVal, &$counter = 0) {
        if($minVal > $maxVal) return false;
        for($i = $minVal; $i <= $maxVal; ++$i) {
            $config[$pos] = $i;
            if($pos < count($config) - 1) {
                if(!$this->allCombinations($config, $pos + 1, $i + 1, $maxVal, $counter))
                    continue;
            } else {
//                 $this->allCombos[$counter] = $config;
                ++$counter;
            }
        }
        return true;
    }
    
    function evalSolution($sol) {
        $solution = array();
        foreach($sol as $key=>$val) {
            $row = intval($key / 5);
            $col = $key % 5;
            $solution[$row][$col] = $this->AKs[$val];
        }
        $sumOfRows;
        for($col = 0; $col < 5; ++$col) {
            $sumOfRows[$col] = 0;
            for($row = 0; $row < count($solution); ++$row)
                $sumOfRows[$col] += $solution[$row][$col]->partWishes;
        }
        $sum = 0;
        $count = 0;
        foreach($sumOfRows as $rowSum) {
            if($rowSum != 0) {
                $sum += $rowSum; 
                ++$count;
            }
        }
        $middle = $sum / $count;
        $sum = 0;
        $count = 0;
        foreach($sumOfRows as $rowSum) {
            if($rowSum != 0) {
                $sum += ($rowSum - $middle) * ($rowSum - $middle);
                ++$count;
            }
        }
        $deviation = sqrt($sum) / $count;
        foreach($this->conditions as $cond) {
            for($col = 0; $col < 5; ++$col) {
                $found = 0;
                for($row = 0; $row < count($solution); ++$row) {
                    if(isset($solution[$row][$col]) && ($solution[$row][$col]->name == $cond[0] || $solution[$row][$col]->name == $cond[1]))
                        ++$found;
                }
                if($found > 1) $deviation += 2;
            }
        }
        $value = intval(1000*$deviation);
        if($this->bestSolutionVal == -1 || $value < $this->bestSolutionVal) {
            $this->bestSolution = $solution;
            $this->bestSolutionVal = $value;
        }
    }
}
?>
