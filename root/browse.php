<?php
include_once(__DIR__ . "/config.class.php");

$browse = new CBrowse($registry);

$registry->template->title = "Rental Movies";
$registry->template->background = "./img.php?src=/bg/big_search_logo.jpg";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');

$registry->template->scripts[] = "js/movie.js";
$registry->template->main = $browse->searchMovies();

$registry->template->render("main");