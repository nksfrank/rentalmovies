<?php

class CMovies Extends CEdit {
    private $IMG_REL_TABLE;
    private $IMG_TABLE_K1;
    private $IMG_TABLE_K2;

    function __construct($registry) {
        $this->registry = $registry;
        $this->textFilter = new CTextFilter();

        $this->TABLE = "movie";
        $this->REL_TABLE = "movie_rel_genre";
        $this->REL_TABLE_FK_1 = "movie";
        $this->REL_TABLE_FK_2 = "genre";

        $this->IMG_REL_TABLE = "movie_image";
        $this->IMG_TABLE_K1 = "movie";
        $this->IMG_TABLE_K2 = "image";

        $this->MOVIE_REL_TABLE = "movie_trailer";
        $this->MOVIE_TABLE_K1 = "movie";
        $this->MOVIE_TABLE_K2 = "link";
    }

    public function getPost($id) {
        $this->validateInputs(null, $id);

        $sql = "SELECT * FROM {$this->TABLE} WHERE id = {$id}";
        $res = $this->registry->db->QueryAndFetch($sql);

        $res->title = htmlentities($res->title, null, 'UTF-8');
        $res->data = htmlentities($res->data, null, 'UTF-8');

        return $res;
    }

    public function getAllPosts() {
        $sql = "SELECT *, (posted <= NOW()) AS available FROM {$this->TABLE};";
        $res = $this->registry->db->QueryAndFetchAll($sql);
        foreach($res as $key => $val) {
            $res[$key]->title = htmlentities($val->title, null, 'UTF-8');
            $res[$key]->data = htmlentities($val->data, null, 'UTF-8');
        }
        return $res;
    }

    public function searchMovies($search, $order, $orderby, $count) {
        $sql = "SELECT movie.id, title, price, movie_image.image FROM {$this->TABLE} WHERE title = {$search}";

        if(isseT($orderby)) {
            $sql .= " ORDERBY {$orderby}";
        }
        if(isset($order)) {
            $sql .= " ORDER DESC";
        }
    }

    public function getRelGenre($id) {
        $this->validateInputs(null, $id);
        $sql = "SELECT genre, genre.name FROM " . $this->REL_TABLE . " INNER JOIN genre ON " . $this->REL_TABLE .".genre = genre.id WHERE " . $this->REL_TABLE_FK_1 . " = {$id}";
        return $this->registry->db->QueryAndFetchAll($sql);
    }

    public function getRelImage($movie) {
        $this->validateInputs(null, $movie);
        $sql = "SELECT id, image FROM " . $this->IMG_REL_TABLE . " WHERE " . $this->REL_TABLE_FK_1 . " = {$movie}";
        
        return $this->registry->db->QueryAndFetchAll($sql);
    }

    public function getRelTrailer($movie) {
        $this->validateInputs(null, $movie);
        $sql = "SELECT id, link FROM " . $this->MOVIE_REL_TABLE . " WHERE " . $this->REL_TABLE_FK_1 . " = {$movie}";
        
        return $this->registry->db->QueryAndFetchAll($sql);
    }

    public function insert($values) {
        $params = $this->validateInputs($values);
        $slug = $this->slugify($params['title']);
        $sql = "INSERT INTO " . $this->TABLE . " SET
                title=\"{$params['title']}\",
                data=\"{$params['data']}\",
                price=\"{$params['price']}\",
                released=\"{$params['released']}\",
                posted=\"{$params['posted']}\",
                imdb=\"{$params['imdb']}\"";
        echo $sql;
        return $this->registry->db->Query($sql);
    }

    public function update($id, $values) {
        $params = $this->validateInputs($values, $id);
        $slug = $this->slugify($params['title']);
        $sql = "UPDATE " . $this->TABLE . " SET
                title=\"{$params['title']}\",
                data=\"{$params['data']}\",
                price=\"{$params['price']}\",
                released=\"{$params['released']}\",
                posted=\"{$params['posted']}\",
                imdb=\"{$params['imdb']}\"
                WHERE id = {$id}";
        return $this->registry->db->Query($sql);
    }


    public function insertRelImage($values, $movie) {
        $values = $this->validateInputs($values, $movie);
        $sql = "INSERT INTO {$this->IMG_REL_TABLE}($this->IMG_TABLE_K1, $this->IMG_TABLE_K2) VALUES";
        foreach($values as $val) {
            $sql .= "($movie, \"$val\"),";
        }
        $sql = rtrim($sql, ',').";";
        return $this->registry->db->Query($sql);
    }

    public function updateRelImage($values, $id) {
        $values = $this->validateInputs($values, $movie);

        $sql = "UPDATE {$this->IMG_REL_TABLE} SET
                image=\"{$values['image']}\"
                movie=\"{$values['movie']}\"
                WHERE id = {$id};";
        return $this->registry->db->Query($sql);
    }

    public function deleteRelImage($id) {
        $values = $this->validateInputs(null, $id);
        $sql = "DELETE FROM {$this->IMG_REL_TABLE} WHERE id = {$id}";
        return $this->registry->db->Query($sql);
    }

    public function insertRelTrailer($value, $movie) {
        $values = $this->validateInputs($value, $movie);
        $sql = "INSERT INTO {$this->MOVIE_REL_TABLE} SET
                movie=\"{$movie}\",
                link=\"{$value}\";";
        return $this->registry->db->Query($sql);
    }

    public function updateRelTrailer($values, $id) {
        $values = $this->validateInputs($values, $id);
        $sql = "UPDATE {$this->MOVIE_REL_TABLE} SET
                link=\"{$values['trailer']}\"
                movie=\"{$values['movie']}\"
                WHERE id = {$id}";
        return $this->registry->db->Query($sql);
    }

    public function deleteRelTrailer($id) {
        $sql = "DELETE FROM {$this->MOVIE_REL_TABLE} WHERE id = {$id}";
        return $this->registry->db->Query($sql);
    }

    public function getForm($id = null) {
        $action = "create";
        $title = NULL;
        $data = NULL;
        $price = NULL;
        $released = NULL;
        $posted = date("Y-m-d H:i:s");
        $imdb = NULL;
        $deletelink = null;

        if(isset($id)) {
            $this->validateInputs(null, $id);
            $post = $this->getPost($id);

            $relGen = $this->getRelGenre($id);
            if(!empty($relGen)) {
                $r = array();
                foreach($relGen as $val) {
                    $r[] = $val->genre;
                }
                $relGen = $r;
            }

            $relTrailer = $this->getRelTrailer($id);
            $images = $this->getRelImage($id);
            $action = "update";
            $title = isset($post->title) ? $post->title : NULL;
            $data = isset($post->data) ? $post->data : NULL;
            $price = isset($post->price) ? $post->price : NULL;
            $released = isset($post->released) ? $post->released : NULL;
            $posted = isset($post->posted) ? $post->posted : NULL;
            $imdb = isset($post->imdb) ? $post->imdb : NULL;
            $deletelink = "<code><a href='content_edit.php?p=movie&id={$id}&delete'>Delete</a></code>";
        }

        //Fetching genres
        $html = NULL;
        $gen = new CGenre($this->registry);
        $genres = $gen->getAllPosts();
        $html = "<div style='margin:0px auto; width:400px;'><ul>";
        foreach($genres as $g) {
            if(!empty($relGen) && in_array($g->id, $relGen))
                $html .= "<li><input type='checkbox' name='gen[]' value='{$g->id}' checked>{$g->name} </li>";
            else
                $html .= "<li><input type='checkbox' name='gen[]' value='{$g->id}'>{$g->name} </li>";
        }
        $html .= "</ul></div>";

        //Fetching images
        $image = NULL;
        if(isset($images)) {
            $image = "<ul>";
            foreach($images as $img) {
                $image .= "<li style='margin: 4px;'><a href='content_edit.php?p=movie&id={$id}&t=image&img={$img->id}&delete'><img src='img.php?src=movie/{$img->image}&height=64'></a></li>";
            }
            $image .= "</ul><br>";
        }

        //Fetching trailers
        $trailer = NULL;
        if(isset($id)) {
            $i = 0;
            $trailer = "<ul>";
            foreach($relTrailer as $t) {
                $class = $i++ % 2 == 0 ? "white" : "cloud"; $i = $i >= 2 ? 0 : $i;
                $trailer .= "<li class='{$class}' style='padding-left:8px; padding-right: 8px; display:list-item;'><a href='content_edit.php?p=movie&id={$id}&t=trailer&trailer={$t->id}&delete'>{$t->link}</a></li>";
            }
            $trailer .= "</ul>";
        }

        return "<div class='content-row'>
            <div class='row-innards'>
                <div class='row-words'>
                <form action='' method='post' enctype='multipart/form-data'>
                    <button type='submit'>Save</button> $deletelink<br>
                    <ul style='display:inline-flex;'>
                        <li style='margin-right:8px;'>
                            <input type='hidden' name='id' value='$id'>
                            <input type='text' size='49' name='title' placeholder='Titel' value='$title'><br>
                            <textarea cols='50' rows='10' name='data' placeholder='Movie Information'>$data</textarea><br>
                            <input type='text' size='49' name='price' placeholder='Price' value='$price'><br>
                            <input type='text' size='49' name='released' placeholder='Release Date' value='$released'><br>
                            <input type='text' size='49' name='imdb' placeholder='Imdb Link' value='$imdb'><br>
                            <input type='datetime' size='49' name='posted' placeholder='Publiseringsdatum' value='$posted'><br>
                            <input type='hidden' name='{$action}' value='true'/>
                        </li>
                        <li>
                            <div>
                                {$html}
                                <hr>
                                <label for='file'>Movie image: </label><input type='file' name='image[]' multiple><br>
                                {$image}
                                <hr>
                                <input type='text' size='39' name='trailer' placeholder='Trailer link' style='margin-right:4px;'><button name='trailer_add' type='submit'>Add</button><br>
                                {$trailer}
                            </div>
                        </li>
                    </ul><br>
                    <button type='submit'>Save</button> $deletelink
                </form>
                </div>
            </div>
        <div>";
    }
}