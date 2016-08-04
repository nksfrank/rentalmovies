<?php

class CFrontPage {
    private $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function getGenreTiles() {
        $sql = "SELECT * FROM genre";
        $genres = $this->registry->db->QueryAndFetchAll($sql);

        $html = NULL;
        if(!empty($genres)) {
            $html = "<ol id='genre-grid'>";
            foreach($genres as $g) {
                $html .= "<li><a href='browse.php?g=".strtolower($g->name)."&p=1&q='><div class='genre-item'>{$g->name}</div></a></li>";

            }
            $html .= "</ol>";
        }
        return $html;
    }

    public function getMovieTiles($orderby = null, $order = null, $count = null) {
        $sql ="SELECT movie.id, title, price, movie_image.image FROM movie JOIN movie_image ON movie_image.movie = movie.id WHERE posted <= NOW()";
        if(isset($orderby))
          $sql .= " ORDER BY {$orderby}";
        if(isset($order))
            $sql .= " {$order}";
        if(isset($count))
            $sql .= " LIMIT {$count}";
        $sql .= ";";
        $movies = $this->registry->db->QueryAndFetchAll($sql);

        $html = NULL;
        if(!empty($movies)) {
            $html = "<ul id='movie-container'>";
            foreach($movies as $m) {
                $html .= "<li><a href='movie.php?id={$m->id}'><div class='movie-item turquoise'><div class='movie-item image'><img src='img.php?src=movie/{$m->image}&crop-to-fit&width=250&height=300'></div><div class='movie-item button'><h2>{$m->title}</h2></div></div></a></li>";
            }
            $html .= "</ul>";
        }

        return $html;
    }

    public function getNewsTiles($orderby = null, $order = null, $count = null) {
        $sql = "SELECT id, user, slug, title, LEFT(data, 300) as data, published, filter FROM news WHERE published <= NOW()";
        if(isset($orderby))
            $sql .= " ORDER BY {$orderby}";
        if(isset($order))
            $sql .= " {$order}";
        if(isset($count))
            $sql .= " LIMIT {$count}";
        $sql .= ";";

        $news = $this->registry->db->QueryAndFetchAll($sql);

        $html = NULL;
        if(!empty($news)) {
            $html = "<ul id='news-grid'>";
            $textFilter = new CTextFilter();
            foreach($news as $n) {
                $n->data = $textFilter->doFilter(htmlentities($n->data, null, 'UTF-8'), $n->filter);
                $data = strlen($n->data) >= 297 ? substr($n->data, 0, $this->registry->helper->strrpos_arr($n->data, ['?',',','!','.'], 0)) . "...<div style='float:right'><a href='news.php?slug={$n->slug}'>Read More &raquo;</a></div>" : $n->data;
                $html .= "<li class='news-item'><span class='title'><a href='news.php?slug={$n->slug}'>{$n->title}</a></span><br><span class='details'>author: {$n->user}, published: {$n->published}</span><p>{$data}</p></li>";
            }
            $html .= "</ul>";
        }

        return $html;
    }
}