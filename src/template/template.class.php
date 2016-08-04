<?php
/**
 * Created by PhpStorm.
 * User: Niklas
 * Date: 2014-09-09
 * Time: 17:55
 */

class template {
    private $registry;
    private $variables = array();

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function __set($index, $value) {
        $this->variables[$index] = $value;
    }

    public function &__get($index) {
        return $this->variables[$index];
    }

    public function render($file) {
        $this->registry->helper->response($this->fetch($file));
    }

    private function fetch($file) {
        $filePath = __SITE_PATH . "/theme/" . $file . ".tpl.php";

        if(!file_exists($filePath)) {
            $this->registry->helper->response("Template not found in $filePath", 404);
        }

        extract($this->variables);
        ob_start();
        include($filePath);
        $output = ob_get_contents();
        ob_end_clean();

        return !empty($output) ? $output : false;
    }
}