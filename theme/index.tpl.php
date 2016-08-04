<?php include("header.php"); ?>

    <div class="big-search">
        <div class="big-search background" style="background-image:url(<?=$background?>)">
            <div class="big-search-box">
                <div class="big-search-box content">
                    <form method="get" action="browse.php">
                        <input type="search" result=5 name="q" placeholder="SEARCH">
                    </form>
                    <a href="../root/login.php"><div class="register-button fr teal">REGISTER</div></a>
                </div>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="content-row alt-row">
            <div class="content-box">
            <?=$genres?>
            </div>
        </div>
        <div class="content-row alt-row">
            <div class="content-box">
                <div class="container box">
                    <div class="container head"><h2>LATEST MOVIES</h2></div>
                    <div class="container body cloud">
                        <?=$movies?>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-row alt-row">
            <div class="content-box">
                <div class="container box">
                    <div class="container head"><h2>NEWS</h2></div>
                        <?=$news?>
                </div>
            </div>
        </div>
    </div>

<?php include("footer.php"); ?>