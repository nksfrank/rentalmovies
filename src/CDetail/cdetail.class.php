<?php

class CDetail {
    private $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function start() {
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $slug = isset($_GET['slug']) ? $_GET['slug'] : NULL;

        if(isset($id)) {
            return $this->getMoviePost($id);
        }
        if(isset($slug)) {
            return $this->getNewsPost($slug);
        }
    }

    public function getMoiveGenreTiles($id) {
        $movie = new CMovies($this->registry);
        $m_gen = $movie->getRelGenre($id);
        $html = "";
        if(!empty($m_gen)) {
            $html = "<ul id='genre-grid' style='display:flex;'>";
            foreach($m_gen as $g) {
                $html .= "<li><a href='browse.php?g=".strtolower($g->name)."&p=1&q='><div class='genre-item'>{$g->name}</div></a></li>";

            }
            $html .= "</ul>";
        }
        return $html;
    }

    public function  getMoviePost($id) {
        $movie = new CMovies($this->registry);

        $m = $movie->getPost($id);
        $m_img = $movie->getRelImage($id);
        $m_trailer = $movie->getRelTrailer($id);

        $imdb = !empty($m->imdb) ? "<div class='genre-item'><a href='$m->imdb'>IMDB</a></div>" : NULL;
        $youtube = NULL;
        if(!empty($m_trailer)) {
            foreach($m_trailer as $t) {
                $youtube .= "<div class='genre-item'><a href='{$t->link}'>Trailer</a></div>";
            }
        }
        $genreTiles = $this->getMoiveGenreTiles($id);
        $html = <<<EOD
                <div class="container box">
                    <div class="container body cloud">
                        <div style="width:900px; margin:0 auto;">
                            <div class="movie-detail-img">
                                <img src="img.php?src=movie/{$m_img[0]->image}&width=300">
                            </div>
                            <div style="display:table;">
                                <h1 style="margin:0 0 12px 0">{$m->title}</h1>
                                <p>Information</p>
                                <div class="genre-item">Price: {$m->price};-</div>
                                <div class="genre-item">{$m->released}</div>
                                {$imdb}{$youtube}
                            </div>
                            <div class="movie-detail-content">
                                <p>{$m->data}</p>
                            </div>
                            <div style="display:table;">
                                <p>Genre</p>
                                {$genreTiles}
                            </div>
                        </div>
                    </div>
                </div>
EOD;
        return $html;
    }

    public function  getNewsPost($slug) {
        $news = new CNews($this->registry);

        $n = $news->getPostBySlug($slug);
        $date = "<span style='font-size:14px; font-weight:normal; margin-bottom:8px;'>posted: " . $n->published;
        $date .= (isset($n->updated)) ? ", updated: " . $n->updated : "";
        $date .= "</span>";

        $html = <<<EOD
                <div class="container box">
                    <div class="container body cloud">
                        <div style="width:900px; margin:0 auto;">
                            <h1 style="margin:0 0 0 0;">{$n->title}</h1>
                            {$date}
                            <div class="genre-item" style="margin:12px 0 0 0">{$n->data}</div>
                        </div>
                    </div>
                </div>
EOD;
        return $html;
    }
}