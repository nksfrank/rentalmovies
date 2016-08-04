<?php
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('output_buffering', 0);

define('__SITE_PATH', dirname(__DIR__));
define('__ROOT_PATH', __SITE_PATH . DIRECTORY_SEPARATOR . "root");
define('__SRC_PATH', __SITE_PATH . DIRECTORY_SEPARATOR . "src");
define('__THEME_PATH', __SITE_PATH . DIRECTORY_SEPARATOR . "theme");
define('__IMG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
define('__CACHE_PATH', __DIR__ . '/cache/');

require_once(__ROOT_PATH . "/registry.class.php");
require_once(__ROOT_PATH . "/helper.class.php");
require_once(__ROOT_PATH . "/bootstrap.class.php");

session_start();

$registry = new registry();
$registry->debug = false;

/**

 Database settings

*/
$registry->database = array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=rentalmovies',
    'username' => 'nks1111',
    'password' => '+%(-xE8LP~agv7K"',
    'driver_options' =>array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
    );

$registry->db = new CDatabase($registry);
$registry->cuser = new CUser($registry);
$registry->helper = new helper($registry);
$registry->template = new template($registry);


$menu = array(
    'items' => array(
        'home' => array('text'=>'RENTAL MOVIES', 'url' => '../root/', 'class' => "fl"),
        'movies' => array('text'=>'MOVIES', 'url' => '../root/browse.php', 'class' => "fl"),
        'news' => array('text'=>'NEWS', 'url' => '../root/news.php', 'class' => "fl"),
        'about' => array('text'=>'ABOUT US', 'url' => '../root/about.php', 'class' => "fl"),
        'search' => array('search' => true)
    )
);
$registry->menu = new menu($registry);
$registry->template->login = $registry->cuser->ShowForm();

/**
 * Site wide settings.
 */
$registry->template->lang = 'sv';
$registry->template->title_append = ' | River en webbtemplate';

/**
 * Theme related setting.
 */
$registry->template->scripts = array('js/angular.min.js','js/jquery-1.11.2.min.js', 'js/doT.min.js');
$registry->template->stylesheets = array('css/style.css');