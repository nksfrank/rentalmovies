<?php
ob_start();
include_once(__DIR__ . "/config.class.php");

$output = "";

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $l = $registry->cuser->Login($username, $password);
    if($l == 1) header('Location: ../root/content.php');
    else $output = "Inloggning misslyckades";
}
else if(isset($_POST['logout']) || isset($_GET['logout'])) {
    $registry->cuser->Logout();
    header('Location: '. (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "../root/index.php"));
}

$registry->template->title = "Rental Movies";
$rand = rand(1, 2);
$registry->template->background = "./img.php?src=bg/big_search_logo_{$rand}.jpg&gray";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');

$registry->template->render("login");