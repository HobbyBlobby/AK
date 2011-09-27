<?php
class Slot {
    var $AKs = array();
    var $wishSum = 0;
    var $number;
    var $possibleSlots = array();
    function Slot($number) {
        $this->number = $number;
    }
    function addAK($ak) {
        array_push($this->AKs, $ak);
        $this->wishSum += $ak->partWishes;
    }
}
?>