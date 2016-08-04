<?php
/**
 * Created by PhpStorm.
 * User: Niklas
 * Date: 2014-09-09
 * Time: 17:46
 */

class registry {
    private $vars = array();

    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }

    public function __get($index) {
        return $this->vars[$index];
    }
}