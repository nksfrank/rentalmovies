<?php
include_once(__DIR__ . "/config.class.php");

$detail = new CDetail($registry);

$registry->template->title = "Rental Movies";
$registry->template->background = "./img.php?src=/bg/big_search_logo.jpg";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');

$registry->template->main = $detail->start();

$registry->template->render("movies");