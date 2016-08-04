<?php

class CBrowse {
    private $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function searchMovies() {
        $query = isset($_GET['q']) ? strip_tags($_GET['q']) : "";
        $genre = isset($_GET['g']) ? strip_tags($_GET['g']) : "";
        $order = isset($_GET['o']) ? $_GET['o'] : "ASC"; // == "ASC" ? "DESC" : "ASC" : "DESC"; 
        $orderby = isset($_GET['ob']) ? strip_tags($_GET['ob']) : "title";
        $page = isset($_GET['p']) ? strip_tags($_GET['p']) : 1;
        $hits = isset($_GET['h']) ? strip_tags($_GET['h']) : 8;
        $json = isset($_GET['json']) ? strip_tags($_GET['json']) : 0;

        if($json == 1) {
            return $this->json_search($query, $genre, $order, $orderby, $page, $hits);
        }

        $controls = $this->getControls($query, $genre, $orderby, $order, $hits, $page);
        $html = <<<EOD
                <div class="container box">
                    <div class="container head"><h2>SEARCH RESULTS</h2></div>
                    <div class="container body cloud" ng-controller="MovieCtrl">
                        {$controls}
                        <div style='display:inline-flex;'>
                            <div style='width:100%;'><ul id='movie-container'>
                            <li ng-repeat="m in movies">
                                <a href='movie.php?id={{m.id}}'><div class='movie-item turquoise'><div class='movie-item image'><img ng-src='img.php?src=movie/{{m.image}}&crop-to-fit&width=250&height=300'></div><div class='movie-item button'><h2>{{m.title}}</h2></div></div></a>
                            </li>
                            </ul></div>
                        </div>
                    </div>
                </div>
EOD;
        return $html;

    }

    private function json_search($search = "", $genre = "", $order = "ASC", $orderby = "price", $page = 1, $hits = 8) {
        if(isset($genre) && !empty($genre))
            $sql = "SELECT movie.id, title, movie_image.image FROM genre INNER JOIN movie_rel_genre ON genre = genre.id INNER JOIN movie ON movie.id = movie_rel_genre.movie JOIN movie_image ON movie_image.movie = movie.id WHERE genre.name = \"{$genre}\" AND posted <= NOW()";
        else
            $sql = "SELECT movie.id, title, movie_image.image FROM movie JOIN movie_image ON movie_image.movie = movie.id WHERE posted <= NOW()";

        if(isset($search) && !empty($search))
            $sql .= " AND title LIKE \"%{$search}%\"";
        if(isset($orderby) && !empty($orderby)) {
            $sql .= " ORDER BY {$orderby}";
            if(isset($order))
                $sql .= " {$order}";
        }
        if(!empty($hits)) {
            $sql .= " LIMIT {$hits}";
        }
        $offset = ($page-1)*$hits;
        $sql .= " OFFSET {$offset}";
        $sql .= ";";
        $results = $this->registry->db->QueryAndFetchAll($sql);
        foreach($results as $r) {
                $r->title = (strlen($r->title) > 20) ? substr($r->title, 0, 17) . "..." : $r->title;
        }
        header("HTTP/1.1 200");
        header("Content-type: application/json");
        echo json_encode($results);
        exit();
    }

    private function getControls($query, $genre, $orderby, $order, $hits, $page) {
        //order ASC DESC, orderby title price release, count, page
        $options = array('title', 'price', 'released');
        $html = "<div class='clearfix'><form ng-submit='fetchMovies()' id='movieform' style='float:left;'>";
        $html .= "<input type='search' name='q' value='{$query}' id='search' ng-model='query.q' ng-change='fetchMovies()'> ";
        $html .= "<select id='orderby' name='ob' ng-model='query.ob'>";
        foreach($options as $o) {
            if($o == $orderby)
                $html .= "<option value='{$o}' selected>{$o}</option>";
            else
                $html .= "<option value='{$o}'>{$o}</option>";
        }
        $html .= "</select> ";

        $sql = "SELECT name FROM genre";
        $genres = $this->registry->db->QueryAndFetchAll($sql);
        $html .= "<select id='genre' name='g' ng-model='query.g'><option value='' selected>None</option>";
        foreach($genres as $g) {
            if($g->name == $genre)
                $html .= "<option value='{$g->name}' selected>{$g->name}</option>";
            else
                $html .= "<option value='{$g->name}'>{$g->name}</option>";
        }
        $html .= "</select> ";

        $options = array('ASC', 'DESC');
        $html .= "<select id='order' name='o' ng-model='query.o'>";
        foreach($options as $o) {
            if($o == $order)
                $html .= "<option value='{$o}' selected>{$o}</option>";
            else
                $html .= "<option value='{$o}'>{$o}</option>";
        }
        $html .= "</select> ";
        $html .= "<label for='count'>Count: </label>{$this->createHitsPerPage(array(4,8,12))}";
        $html .= "<input type='hidden' name='h' ng-model='query.h' value='{$hits}'>";
        $html .= "<input type='hidden' name='p' ng-model='query.p' value='{$page}'>";
        $html .= "<input type='submit' value='submit' ng-click='fetchMovies()'></form>";
        $rowcount = $this->getRowCount($query);
        $html .= $this->buildPaging($page, $rowcount, $hits);
        $html .= "</div>";
        return $html;
    }

    private function createHitsPerPage($hits) {
        $html = "";
        foreach($hits AS $val) {
            $n = array("h" => $val);
            $link = $this->registry->helper->appendUrl($n);
            $html .= "<a href=\"$link\">$val</a>&nbsp";
        }
        return $html;
    }

        private function buildPaging($page, $totalcount, $amount) {
        $nav = "<div class='right' style='float:right;'>";

        $total_pages = ceil($totalcount / $amount);

        $from = ($page * $amount) - $amount;
        if($from < 0) $from = 0;

        if($page > 1) {
            $prev = array("p" => $page - 1);
            $link = $this->registry->helper->appendUrl($prev);
            $nav .= "<a href='$link'><<</a> ";
        }
        $nav .= "{$page}";
        if($page < $total_pages) {
            $next = array("p" => $page + 1);
            $nextlinks = $this->registry->helper->appendUrl($next);
            $nav .= " <a href='$nextlinks'>>></a>";
        }
        $nav .= "</div>";

        return $nav;
    }

    private function getMovieTilesBySearch($search = "", $genre = "", $order = "DESC", $orderby = "title", $page = 1, $hits = 8) {
        if(isset($genre) && !empty($genre))
            $sql = "SELECT movie.id, title, movie_image.image FROM genre INNER JOIN movie_rel_genre ON genre = genre.id INNER JOIN movie ON movie.id = movie_rel_genre.movie JOIN movie_image ON movie_image.movie = movie.id WHERE genre.name = \"{$genre}\" AND posted <= NOW()";
        else
            $sql = "SELECT movie.id, title, movie_image.image FROM movie JOIN movie_image ON movie_image.movie = movie.id WHERE posted <= NOW()";

        if(isset($search) && !empty($search))
            $sql .= " AND title LIKE \"%{$search}%\"";
        if(isset($orderby) && !empty($orderby)) {
            $sql .= " ORDER BY {$orderby}";
            if(isset($order) && !empty($order))
                $sql .= " {$order}";
        }
        if(!empty($hits)) {
            $sql .= " LIMIT {$hits}";
        }
        $offset = ($page-1)*$hits;
        $sql .= " OFFSET {$offset}";
        $sql .= ";";
        $results = $this->registry->db->QueryAndFetchAll($sql);

        $html = NULL;
        if(!empty($results)) {
            $html = "<div style='width:100%;'><ul id='movie-container'>";
            foreach($results as $m) {
                $m->title = (strlen($m->title) > 20) ? substr($m->title, 0, 17) . "..." : $m->title;
                $html .= "<li><a href='movie.php?id={$m->id}'><div class='movie-item turquoise'><div class='movie-item image'><img src='img.php?src=movie/{$m->image}&crop-to-fit&width=250&height=300'></div><div class='movie-item button'><h2>{$m->title}</h2></div></div></a></li>";
            }
            $html .= "</ul></div>";
        }

        return $html;
    }

    public function getRowCount($query) {
        $sql = "SELECT count(*) as count FROM movie WHERE title LIKE \"%{$query}%\";";
        return $this->registry->db->QueryAndFetch($sql)->count;
    }

    public function searchNews() {
        $query = isset($_GET['q']) ? strip_tags($_GET['q']) : NULL;
        $category = isset($_GET['cat']) ? strip_tags($_GET['cat']) : NULL;
        $order = isset($_GET['o']) ? $_GET['o'] : "DESC"; // == "ASC" ? "DESC" : "ASC" : "DESC"; 
        $orderby = isset($_GET['ob']) ? strip_tags($_GET['ob']) : "published";
        $page = isset($_GET['p']) ? strip_tags($_GET['p']) : 1;
        $hits = isset($_GET['h']) ? strip_tags($_GET['h']) : 8;

        $controls = $this->getNewsControls($query, $category, $orderby, $order, $hits, $page);

        $html = <<<EOD
        <div class="container box">
            <div class="container body cloud">
                {$controls}
                <div>
                    {$this->getNewsTilesBySearch($query, $category, $order, $orderby, $page, $hits)}
                </div>
            </div>
        </div>
EOD;

        return  $html;
    }

    private function getNewsControls($query, $category, $orderby, $order, $hits, $page) {
        //order ASC DESC, orderby title price release, count, page
        $options = array('title', 'price', 'released');
        $html = "<div class='clearfix'><form method='get' action='news.php' style='float:left;''>";
        $html .= "<input type='search' name='q' value='{$query}'> ";

        $sql = "SELECT name FROM category";
        $categories = $this->registry->db->QueryAndFetchAll($sql);
        $html .= "<select id='category' name='cat'><option value=''>All</option>";
        foreach($categories as $cat) {
            if($cat->name == $category)
                $html .= "<option value='{$cat->name}' selected>{$cat->name}</option>";
            else
                $html .= "<option value='{$cat->name}'>{$cat->name}</option>";
        }
        $html .= "</select> ";

        $options = array('ASC', 'DESC');
        $html .= "<select id='order' name='o'>";
        foreach($options as $o) {
            if($o == $order)
                $html .= "<option value='{$o}' selected>{$o}</option>";
            else
                $html .= "<option value='{$o}'>{$o}</option>";
        }
        $html .= "</select> ";
        $html .= "<label for='count'>Count: </label>{$this->createHitsPerPage(array(4,8,12))}";
        $html .= "<input type='hidden' name='h' value='{$hits}'>";
        $html .= "<input type='hidden' name='p' value='{$page}'>";
        $html .= "<button type='submit'>Sort</button></form>";
        $rowcount = $this->getRowCount($query);
        $html .= $this->buildPaging($page, $rowcount, $hits);
        $html .= "</div>";
        return $html;
    }

    public function getNewsTilesBySearch($query = null, $category = null, $order = null, $orderby = null, $page = 1, $hits = 8) {
        if(isset($category) && !empty($category)) {
            $sql = "SELECT news.id, slug, title, LEFT(data, 500) as data, filter, published, updated, category.name FROM news JOIN news_rel_category ON news_rel_category.news = news.id INNER JOIN category ON news_rel_category.category = category.id WHERE category.name = \"{$category}\" AND published <= NOW()";
        }
        else {
            $sql = "SELECT news.id, slug, title, LEFT(data, 500) as data, filter, published, updated, category.name FROM news JOIN news_rel_category ON news_rel_category.news = news.id INNER JOIN category ON news_rel_category.category = category.id WHERE published <= NOW()";
        }

        if(isset($query))
            $sql .= " AND news.slug LIKE \"%{$query}%\"";
        if(isset($orderby)) {
            $sql .= " ORDER BY {$orderby}";
            if(isset($order))
                $sql .= " {$order}";
        }
        if(isset($hits))
            $sql .= " LIMIT {$hits}";
        $offset = ($page-1)*$hits;
        $sql .= " OFFSET {$offset}";
        $sql .= ";";

        $res = $this->registry->db->QueryAndFetchAll($sql);

        $html = NULL;
        if(!empty($res)) {
            $html = "<ul id='news-grid-large'>";
            foreach($res as $n) {
                $data = strlen($n->data) > 497 ? substr($n->data, 0, strrpos($n->data, ".", 1)) . "...<div style='float:right'>Read More &raquo;</div>" : $n->data;
                $html .= "<li class='news-item'><a href='news.php?slug={$n->slug}'>
                            <div class='clearfix'>
                                <span class='title'>{$n->title}</span><br><span class='details'>published: {$n->published}</span>
                                <p>
                                {$data}
                                </p>
                            </div></a>
                        </li>";
            }
            $html .= "</ul>";
        }
        return $html;
    }
}