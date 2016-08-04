<?php
include(__DIR__ . "/config.class.php");

$fp = new CFrontPage($registry);

$registry->template->title = "Rental Movies";
$r = rand(1,2);
$registry->template->background = "./img.php?src=bg/big_search_logo_{$r}.jpg";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');


$registry->template->genres = $fp->getGenreTiles();

$registry->template->movies = $fp->getMovieTiles("posted", "DESC", 4);
$registry->template->news = $fp->getNewsTiles("published", "DESC", 4);

$registry->template->render("index");