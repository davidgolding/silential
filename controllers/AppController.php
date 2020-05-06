<?php

namespace controllers;

class AppController {
    
    public static function index($args = []) {
        return $args['response']->render('Hello World');
    }
}

?>