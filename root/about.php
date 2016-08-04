<?php
include(__DIR__ . "/config.class.php");

$fp = new CFrontPage($registry);

$registry->template->title = "Rental Movies";
$r = rand(1,2);
$registry->template->background = "./img.php?src=bg/big_search_logo_{$r}.jpg";

$registry->template->menu = $registry->menu->GenerateMenu($menu, 'header-item');


$registry->template->main = <<<EOD
<div style="display:flex;width:100%;">
    <div class="movie-detail-content" style="margin:20px auto 20px auto;max-width:800px;">
        <center><h1>About Us</h1></center>
        <p>Rental Movies is small movie rental company, seated in the lower part of the Andromeda Galaxy. We currently employ lite over 4 billion people to help you pick the movie you need to watch.</p>
        <p>With our small armada of 60 Galactic Planet Crackers are we able to supply our customers with the recent movies, anywhere in the known universe, within 160 seconds.</p>
        <p><small>Small disclamer:<span style="font-size:8px;"> We, the company and its employees, are not responsible for any imminent planet destruction caused by any gravity well created by our Galactic Planet Crackers upon arival to your system. The responsibility falls upon the planet, and its population under intergalactic law.</span></small></p>
    </div>
</div>
EOD;

$registry->template->render("main");