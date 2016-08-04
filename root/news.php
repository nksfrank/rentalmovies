<?php
include_once(__DIR__ . "/config.class.php");


$registry->template->title = "Rental Movies";
$registry->template->background = "./img.php?src=/bg/big_search_logo.jpg";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');

if(isset($_GET['slug'])) {
    $detail = new CDetail($registry);
    $registry->template->main = $detail->start();
}
else {
    $browse = new CBrowse($registry);
    $registry->template->main = $browse->searchNews();
}

$registry->template->render("main");