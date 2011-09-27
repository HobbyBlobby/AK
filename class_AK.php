<?php
class AK {
    var $name;
    var $partWishes = 0;
    var $status = 'Ok';
    var $slot = NULL;
    function Slot() {
        
    }
    function draw() {
        echo '<div style="word-wrap:break-word;width:100px;">' . $this->name . '</div><br />' . $this->partWishes;
    }
}
?>