<?php

class CContent {
    protected $registry;
    protected $textFilter;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function HandleRequest() {
        $page = isset($_GET['p']) ? $_GET['p'] : null;
        switch($page) {
            case "movie":
                return $this->MovieRequest();
                break;
            case "news":
                return $this->NewsRequest();
                break;
            case "genre":
                return $this->GenreRequest();
                break;
            case "cat":
                return $this->CategoryRequest();
                break;
            default:
                break;
        }
    }

    private function MovieRequest() {
        $movies = new CMovies($this->registry);

        $contents = $movies->getAllPosts();
        $i = 0;
        $main = "<div class='content-row'><ul>"; 

        foreach($contents as $content) {
            $class = $i++ % 2 == 0 ? "white" : "cloud"; $i = $i >= 2 ? 0 : $i;
            $available = $content->available ? "Posted : $content->posted" : "Not Posted Yet";
            $data = (strlen($content->data) > 30) ? mb_substr($content->data, 0, 10) . "..." : $content->data;
            $edit = $this->registry->cuser->IsAuthenticated() ? "<a href='content_edit.php?p=movie&id={$content->id}'>edit</a>, <a href='content_edit.php?p=movie&id={$content->id}&delete'>delete</a>, " : null;
            $main .= "<li class='{$class}' style='display:list-item;'>{$content->title} ({$available}) : {$data}</a> ({$edit}<a href='movie.php?id={$content->id}'>show</a>)</li>";
        }
        $main .= "</ul>";
        $main .= $this->registry->cuser->IsAuthenticated() ? "<a href='content_edit.php?p=movie'>Add new movie</a> | " : null;
        $main .= "<a href='news.php'>Show all movies</a></div>";
        return $main;
    }

    private function NewsRequest() {
        $news = new CNews($this->registry);

        $contents = $news->getAllPosts();
        $i = 0;
        $main = "<div class='content-row'><ul>"; 
        
        foreach($contents as $content) {
            $class = $i++ % 2 == 0 ? "white" : "cloud"; $i = $i >= 2 ? 0 : $i;
            $available = $content->deleted ? "Deleted : {$content->deleted}" : ($content->available ? "Published : $content->published" : "Not Published");
            $data = (strlen($content->data) > 30) ? mb_substr($content->data, 0, 10) . "..." : $content->data;
            $edit = $this->registry->cuser->IsAuthenticated() ? "<a href='content_edit.php?p=news&id={$content->id}'>edit</a>, <a href='content_edit.php?p=news&id={$content->id}&delete'>delete</a>, " : null;
            $main .= "<li class='{$class}' style='display:list-item;'>{$content->title} ({$available}) : {$data}</a> ({$edit}<a href='news.php?slug={$content->slug}'>show</a>)</li>";
        }
        $main .= "</ul>";
        $main .= $this->registry->cuser->IsAuthenticated() ? "<a href='content_edit.php?p=news'>Create new post</a> | " : null;
        $main .= "<a href='news.php'>Show all news</a></div>";
        return $main;
    }

    private function GenreRequest() {
        $genre = new CGenre($this->registry);

        $contents = $genre->getAllPosts();
        $i = 0;
        $main = "<div class='content-row'><ul>";

        foreach($contents as $c) {
            $class = $i++ % 2 == 0 ? "white" : "cloud"; $i = $i >= 2 ? 0 : $i;
            $edit = $this->registry->cuser->IsAuthenticated() ? "(<a href='content_edit.php?p=genre&id={$c->id}'>edit</a>, <a href='content_edit.php?p=genre&id={$c->id}&delete'>delete</a>)" : null;
            $main .= "<li class='{$class}' style='display:list-item;'>{$c->name}</a> {$edit}</li>";
        }
        $main .= "</ul>";
        $main .= $this->registry->cuser->IsAuthenticated() ? "<a href='content_edit.php?p=genre'>Add new genre</a></div>" : "</div>";
        return $main;
    }

    private function CategoryRequest() {
        $category = new CCategories($this->registry);

        $contents = $category->getAllPosts();
        $i = 0;
        $main = "<div class='content-row'><ul>";

        foreach($contents as $c) {
            $class = $i++ % 2 == 0 ? "white" : "cloud"; $i = $i >= 2 ? 0 : $i;
            $edit = $this->registry->cuser->IsAuthenticated() ? "(<a href='content_edit.php?p=cat&id={$c->id}'>edit</a>, <a href='content_edit.php?p=cat&id={$c->id}&delete'>delete</a>)" : null;
            $main .= "<li class='{$class}' style='display:list-item;'>{$c->name}</a> {$edit}</li>";
        }
        $main .= "</ul>";
        $main .= $this->registry->cuser->IsAuthenticated() ? "<a href='content_edit.php?p=cat'>Add new category</a></div>" : "</div>";
        return $main;
    }

    public function HandleEditRequest() {
        $page = isset($_GET['p']) ? $_GET['p'] : NULL;
        $id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $del = isset($_GET['delete']) ? $_GET['delete'] : NULL;

        if(isset($page)) {
            switch ($page) {
                case 'movie':
                    $movie = new CMovies($this->registry);
                    
                    if(isset($_POST['update']) || isset($_POST['create'])) {
                        $this->MovieUpdateRequest();
                    }
                    else if(isset($_GET['t'])) {
                        if($_GET['t'] == "image")
                            $this->MovieDeleteImageRequest($_GET['img']);
                        else if($_GET['t'] == "trailer") {
                            $this->MovieDeleteTrailerRequest($_GET['trailer']);
                        }
                    }
                    else if(isset($del, $id)) {
                        $this->MovieDeleteRequest($id);
                    }

                    return isset($id) ? $movie->getForm((int)$id) : $movie->getForm();

                case 'cat':
                    $category = new CCategories($this->registry);
                    
                    if(isset($_POST['update']) || isset($_POST['create']))
                        $this->CategoryUpdateRequest();
                    else if(isset($del, $id))
                        $this->CategoryDeleteRequest($id);

                    return isset($id) ? $category->getForm((int)$id) : $category->getForm();

                case 'news':
                    $news = new CNews($this->registry);
                    
                    if(isset($_POST['update']) || isset($_POST['create']))
                        $this->NewsUpdateRequest();
                    else if(isset($del, $id))
                        $this->NewsDeleteRequest($id);

                    return isset($id) ? $news->getForm((int)$id) : $news->getForm();

                case 'genre':
                    $genre = new CGenre($this->registry);
                    
                    if(isset($_POST['update']) || isset($_POST['create']))
                        $this->GenreUpdateRequest();
                    else if(isset($del, $id))
                        $this->GenreDeleteRequest($id);

                    return isset($id) ? $genre->getForm((int)$id) : $genre->getForm();

                default:
                    # code...
                    break;
            }
        }
    }

    private function MovieUpdateRequest() {
        $movies = new CMovies($this->registry);

        $id = (int)$_POST['id'];
        $content['title'] = $_POST['title'];
        $content['data'] = $_POST['data'];
        $content['price'] = $_POST['price'];
        $content['released'] = $_POST['released'];
        $content['posted'] = $_POST['posted'];
        $content['imdb'] = $_POST['imdb'];

        $b = 0;
        if(isset($_POST['update'])) {
            $b = $movies->update($id, $content);
        }
        else {
            $b = $movies->insert($content);
            $id = $this->registry->db->LastInsertId();
        }

        if(isset($_POST['gen'])) {
            $movies->updateRel($_POST['gen'], $id);
        }
        else if(isset($id)){
            $movies->deleteRel($id);
        }

        if(isset($_POST['trailer']) && !empty($_POST['trailer'])) {
            $movies->insertRelTrailer($_POST['trailer'], $id);
        }

        if(!empty($_FILES['image']['size'][0])) {
            $movies->insertRelImage($_FILES['image']['name'], $id);
            $this->registry->helper->arrangeFilesArray($_FILES['image']);
            $img = new CImage(__IMG_PATH, __CACHE_PATH);
            $img->uploadImage($_FILES['image'], "movie");
        }

        /*if(isset($_POST['trailer_add'])) {
            ob_end_clean();
            $link = http_build_query($_GET);
            header("Location: content_edit.php?".$link);
        }
        else if($b == 1) {
            ob_end_clean();
            $link = http_build_query($_GET);
            header("Location: content.php?".$link."&id={$id}");
        }*/
        ob_end_clean();
        $link = http_build_query($_GET);
        header("Location: content_edit.php?".$link);
    }

    private function MovieDeleteRequest($id) {
        $movies = new CMovies($this->registry);
        $r = $movies->delete((int)$id);
        if($r == 1) {
            unset($_GET['id'], $_GET['delete']);
            $link = http_build_query($_GET);
            ob_end_clean();
            header("Location: content.php?".$link);
        }
    }

    private function MovieDeleteImageRequest($imgID) {
        $movies = new CMovies($this->registry);
        $r = $movies->deleteRelImage((int)$imgID);
        if($r == 1) {
            unset($_GET['img'], $_GET['delete'], $_GET['t']);
            $link = http_build_query($_GET);
            ob_end_clean();
            header("Location: content_edit.php?".$link);
        }
    }

    private function MovieDeleteTrailerRequest($trailerID) {
        $movies = new CMovies($this->registry);
        $r = $movies->deleteRelTrailer((int)$trailerID);
        if($r == 1) {
            unset($_GET['trailer'], $_GET['delete'], $_GET['t']);
            $link = http_build_query($_GET);
            ob_end_clean();
            header("Location: content_edit.php?".$link);
        }
    }

    private function NewsUpdateRequest() {
        $news = new CNews($this->registry);

        $id = (int)$_POST['id'];
        $content[':title'] = $_POST['title'];
        $content[':data'] = $_POST['data'];
        $content[':published'] = $_POST['published'];
        $content[':filter'] = $_POST['filter'];

        $b = 0;
        if(isset($_POST['update'])) {
            $b = $news->update($id, $content);
        }
        else {
            $b = $news->insert($content);
            $id = $this->registry->db->LastInsertId();
        }

        if(isset($_POST['cat'])) {
            $news->updateRel($_POST['cat'], $id);
        }
        else if(isset($id)){
            $news->deleteRel($id);
        }

        if($b == 1) {
            ob_end_clean();
            $link = http_build_query($_GET);
            header("Location: content.php?".$link);
        }
    }

    private function NewsDeleteRequest($id) {
        $news = new CNews($this->registry);
        $r = $news->delete((int)$id);
        if($r == 1) {
            unset($_GET['id'], $_GET['delete']);
            $link = http_build_query($_GET);
            ob_end_clean();
            header("Location: content.php?".$link);
        }
    }

    private function CategoryUpdateRequest() {
        $category = new CCategories($this->registry);

        $id = (int)$_POST['id'];
        $content['name'] = $_POST['name'];

        $b = 0;
        if(isset($_POST['update']))
            $b = $category->update($id, $content);
        else
            $b = $category->insert($content);

        if($b == 1) {
            ob_end_clean();
            $link = http_build_query($_GET);
            header("Location: content.php?".$link);
        }
    }

    private function CategoryDeleteRequest($id) {
        $category = new CCategories($this->registry);
        $r = $category->delete((int)$id);
        if($r == 1) {
            unset($_GET['id'], $_GET['delete']);
            $link = http_build_query($_GET);
            ob_end_clean();
            header("Location: content.php?".$link);
        }
    }

    private function GenreUpdateRequest() {
        $genre = new CGenre($this->registry);

        $id = (int)$_POST['id'];
        $content['name'] = $_POST['name'];

        $b = 0;
        if(isset($_POST['update'])) {
            $b = $genre->update($id, $content);
        }
        else {
            $b = $genre->insert($content);
        }

        if($b == 1) {
            ob_end_clean();
            $link = http_build_query($_GET);
            header("Location: content.php?".$link);
        }
    }

    private function GenreDeleteRequest($id) {
        $genre = new CGenre($this->registry);
        $r = $genre->delete((int)$id);
        if($r == 1) {
            unset($_GET['id'], $_GET['delete']);
            $link = http_build_query($_GET);
            ob_end_clean();
            header("Location: content.php?".$link);
        }
    }
}