<?php

require 'libraries/Core.php';

class Config {
    
    public static $configs = [];
    
    public static function get($name = null) {
        return static::$configs[$name];
    }
}

?>
