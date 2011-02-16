<?php

class Phpoton_Twitter {

    function __call($name, $args) {
        print "TWITTER CALL: $name ".print_r($args,true)."<br>\n";
        return null;    
    }
}