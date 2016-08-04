<?php

class CCategories Extends CEdit {
    function __construct($registry) {
        $this->registry = $registry;
        $this->textFilter = new CTextFilter();
        $this->TABLE = "category";
    }

    public function getPost($id) {
        $this->validateInputs(null, $id);

        $sql = "SELECT * FROM {$this->TABLE} WHERE id = {$id}";
        $res = $this->registry->db->QueryAndFetch($sql);

        $res->name = htmlentities($res->name, null, 'UTF-8');

        return $res;
    }

    public function getAllPosts() {
        $sql = "SELECT * FROM {$this->TABLE};";
        $res = $this->registry->db->QueryAndFetchAll($sql);
        foreach($res as $key => $val) {
            $res[$key]->name = htmlentities($val->name, null, 'UTF-8');
        }
        return $res;
    }

    public function insert($values) {
        $params = $this->validateInputs($values);
        $slug = $this->slugify($params['title']);
        $sql = "INSERT INTO " . $this->TABLE . " SET
                name=\"{$params['name']}\"";

        return $this->registry->db->Query($sql);
    }

    public function update($id, $values) {
        $params = $this->validateInputs($values, $id);
        $slug = $this->slugify($params['title']);
        $sql = "UPDATE " . $this->TABLE . " SET
                name=\"{$params['name']}\" 
                WHERE id = {$id}";
        return $this->registry->db->Query($sql);
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->TABLE . " WHERE id = '$id'";
        return $this->registry->db->Query($sql);
    }

    public function getForm($id = null) {
        $action = "create";
        $name = NULL;
        $deletelink = null;

        if(isset($id)) {
            $this->validateInputs(null, $id);
            $post = $this->getPost($id);

            $action = "update";
            $name = isset($post->name) ? $post->name : NULL;
            $deletelink = "<code><a href='content_edit.php?p=cat&id={$id}&delete'>Delete</a></code>";
        }

        return "<div class='content-row'>
            <div class='row-innards'>
                <div class='row-words'>
                    <form action='' method='post'>
                        <input type='hidden' name='id' value='$id'>
                        <input type='text' size='49' name='name' placeholder='Category name' value='$name'><br>
                        <input type='hidden' name='{$action}' value='true'>
                        <button type='submit'>Save</button> $deletelink
                    </form>
                </div>
            </div>
        <div>";
    }
}