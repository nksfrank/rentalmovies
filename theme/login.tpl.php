<?php include("header.php"); ?>

    <div class="big-search">
        <div class="big-search background" style="background-image:url(<?=$background?>)">
            <div class="fade">
                <div class="content-box">
                <div class="container box" style="margin:150px 0">
                        <ul id="news-grid">
                            <li class="news-item">
                                <span class="title">LOGIN</span>
                                <span><form method="post">
                                <input type="text" name="username"><br/>
                                <input type="password" name="password"><br>
                                <input type="submit" name="login" value="LOGIN">
                                </form></span>
                            </li>
                            <li class="news-item">
                                <span class="title">REGISTER</span><br>
                                <span class="details">Sign up for you own free account.<br>Start renting movies, today!</span><br>
                                <span>
                                    <input type="text" name="acronym" placeholder="USERNAME"><br>
                                    <input type="password" name="password" placeholder="PASSWORD"><br>
                                    <input type="password" name="confirm" placeholder="CONFIRM PASSWORD"><br>
                                    <input type="submit" value="REGISTER">
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    <div style="height:200px;"></div>

<?php include("footer.php"); ?>