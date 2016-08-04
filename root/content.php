<?php
ob_start();
include_once(__DIR__ . "/config.class.php");
if(!$registry->cuser->IsAuthenticated()) {
    header("Location: ../root/index.php");
}

$content = new CContent($registry);
$result = $content->HandleRequest();

$registry->template->admin_main = $result;

$a_menu = array(
    'items' => array(
        'overview' => array('text'=>'OVERVIEW', 'url' => '../root/content.php', 'class' => "fl"),
        'movies' => array('text'=>'MOVIES', 'url' => '../root/content.php?p=movie', 'class' => "fl"),
        'news' => array('text'=>'NEWS', 'url' => '../root/content.php?p=news', 'class' => "fl"),
        'genres' => array('text'=>'GENRES', 'url' => '../root/content.php?p=genre', 'class' => "fl"),
        'categories' => array('text'=>'CATEGORIES', 'url' => '../root/content.php?p=cat', 'class' => "fl"),
        'logout' => array('text' => 'Logout', 'url' => '../root/login.php?logout', 'class' => 'fr'),
    )
);
$registry->template->admin_menu = $registry->menu->GenerateMenu($a_menu, 'navbar');

$registry->template->title = "Rental Movies";
$registry->template->background = "./img.php?src=bg/big_search_logo.jpg&gray";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');

$registry->template->render("admin");