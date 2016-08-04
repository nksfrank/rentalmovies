<?php

class CNews extends CEdit {

    function __construct($registry) {
        $this->registry = $registry;
        $this->textFilter = new CTextFilter();

        $this->TABLE = "news";
        $this->REL_TABLE = "news_rel_category";
        $this->REL_TABLE_FK_1 = "news";
        $this->REL_TABLE_FK_2 = "category";
    }

    public function getPostBySlug($slug) {
        $this->validateInputs($slug);

        $sql = "SELECT * FROM {$this->TABLE} WHERE {$this->TABLE}.slug = \"{$slug}\";";
        $res = $this->registry->db->QueryAndFetch($sql);
        $res->title = htmlentities($res->title, null, 'UTF-8');
        $res->data = $this->textFilter->doFilter(htmlentities($res->data, null, 'UTF-8'), $res->filter);
        return $res;
    }
    public function getPostByCategory($category) {
        $this->validateInputs(null, $category);
        $sql = "SELECT * FROM {$this->TABLE}, {$this->REL_TABLE} WHERE {$this->REL_TABLE}.category = $category";

        $res = $this->registry->db->QueryAndFetchAll($sql);

        foreach($res as $key => $val) {
            $res[$key]->title = htmlentities($val->title, null, 'UTF-8');
            $res[$key]->data = $this->textFilter->doFilter(htmlentities($val->data, null, 'UTF-8'), $val->filter);
        }
        return $res;
    }

    public function getRelCategory($id) {
        $this->validateInputs(null, $id);
        $sql = "SELECT category FROM " . $this->REL_TABLE . " WHERE " . $this->REL_TABLE_FK_1 . " = {$id}";

        $res = $this->registry->db->QueryAndFetchAll($sql);
        
        $r = array();
        foreach($res as $val) {
            $r[] = $val->category;
        }
        return $r;
    }

    public function delete($id) {
        $sql = "UPDATE " . $this->TABLE . " SET deleted=NOW() WHERE id = '$id'";
        return $this->registry->db->Query($sql);
    }

    public function getForm($id = null) {
        $action = "create";
        $title = NULL;
        $data = NULL;
        $filter = NULL;
        $published = date("Y-m-d H:i:s");
        $deletelink = null;

        if(isset($id)) {
            $this->validateInputs(null, $id);
            $post = $this->getPost($id);

            $relCat = $this->getRelCategory($id);
            $action = "update";
            $title = isset($post->title) ? $post->title : NULL;
            $data = isset($post->data) ? $post->data : NULL;
            $filter = isset($post->filter) ? $post->filter : NULL;
            $published = isset($post->published) ? $post->published : NULL;
            $deletelink = "<code><a href='content_edit.php?p=news&id={$id}&delete'>Delete</a></code>";
        }

        //Fetching categories
        $cat = new CCategories($this->registry);
        $categories = $cat->getAllPosts();

        $html = "<div style='margin:0px auto; width:400px;'><ul>";
        foreach($categories as $c) {
            if(!empty($relCat) && in_array($c->id, $relCat))
                $html .= "<li><input type='checkbox' name='cat[]' value='{$c->id}' checked>{$c->name} </li>";
            else
                $html .= "<li><input type='checkbox' name='cat[]' value='{$c->id}'>{$c->name} </li>";
        }
        $html .= "</ul></div>";

        return "<div class='content-row'>
            <div class='row-innards'>
                <div class='row-words'>
                    <form action='' method='post'>
                        <input type='hidden' name='id' value='$id'>
                        <input type='text' size='49' name='title' placeholder='Titel' value='$title'><br>
                        {$html}
                        <textarea cols='50' rows='10' name='data' placeholder='Data'>$data</textarea><br>
                        <input type='text' size='49' name='filter' placeholder='bbcode/link/markdown/nl2br' value='$filter'><br>
                        <input type='datetime' size='49' name='published' placeholder='Publiseringsdatum' value='$published'><br>
                        <input type='hidden' name='{$action}' value='true'/>
                        <button type='submit'>Save</button> $deletelink
                    </form>
                </div>
            </div>
        <div>";
    }
}