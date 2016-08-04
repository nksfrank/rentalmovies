<?php
/**
 * Created by PhpStorm.
 * User: Niklas
 * Date: 2014-09-11
 * Time: 14:27
 */

class menu {
    private $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function GenerateMenu($menu, $class) {
        $items = (isset($menu['callback'])) ? call_user_func($menu['callback'], $menu['items']) : $items = $menu['items'];
        $html = "";
        foreach($items as $item) {
            if(isset($item['text'])) {
                $html .= "<a href='{$item['url']}'><div class='{$item['class']} {$class}'>{$item['text']}</div></a>";
            }
            else if(isset($item['search'])) {
                $html .= "<div class='header-search'><div class='header-search-box'><form action='browse.php' method='get'><input type='search' result='5' name='q' placeholder='SEARCH'></form></div></div>";
            }
        }
        return $html;
    }
}