<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="rentalMovies">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php foreach($stylesheets as $stylesheet): ?>
        <link rel="stylesheet" type="text/css" href="<?=$stylesheet?>"/>
    <?php endforeach; ?>
    <?php foreach($scripts as $script): ?>
        <script src="<?=$script?>"></script>
    <?php endforeach; ?>
<title><?=$title?></title>
</head>

<body>
<div id="wrapper">
    <div class="header">
        <div class="header content-box">
            <?=$menu?><?=$login?>
        </div>
    </div>