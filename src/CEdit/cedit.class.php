<?php

abstract class CEdit {
    protected $registry;
    protected $textFilter;

    protected $TABLE;
    protected $REL_TABLE;

    protected $REL_TABLE_FK_1;//Key in relation table that points to primary table id
    protected $REL_TABLE_FK_2;//Key in relation table that points to secondary table id
    
    protected $lastInsertedId = null;

    function __construct($registry) {
        $this->registry = $registry;
        $this->textFilter = new CTextFilter();
    }

    public function getPost($id) {
        $this->validateInputs(null, $id);

        $sql = "SELECT * FROM {$this->TABLE} WHERE id = {$id}";
        $res = $this->registry->db->QueryAndFetch($sql);

        $res->title = htmlentities($res->title, null, 'UTF-8');
        $res->data = $this->textFilter->doFilter(htmlentities($res->data, null, 'UTF-8'), $res->filter);

        return $res;
    }

    public function getAllPosts() {
        $sql = "SELECT *, (published <= NOW()) AS available FROM {$this->TABLE};";
        $res = $this->registry->db->QueryAndFetchAll($sql);
        foreach($res as $key => $val) {
            $res[$key]->title = htmlentities($val->title, null, 'UTF-8');
            $res[$key]->data = $this->textFilter->doFilter(htmlentities($val->data, null, 'UTF-8'), $val->filter);
        }
        return $res;
    }

    public function insert($values) {
        $params = $this->validateInputs($values);
        $params[':slug'] = $this->slugify($params[':title']);
        $sql = "INSERT INTO {$this->TABLE} SET
                title=:title,
                slug=:slug,
                data=:data,
                published=:published,
                filter=:filter,
                created=NOW();";
        $this->lastInsertedId = $this->registry->db->LastInsertId();
        return $this->registry->db->Query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->TABLE . " WHERE id = '$id'";
        return $this->registry->db->Query($sql);
    }

    public function deleteRel($id) {
        $sql = "DELETE FROM " . $this->REL_TABLE . " WHERE " . $this->REL_TABLE_FK_1 . " = '$id';";
        return $this->registry->db->Query($sql);
    }

    public function update($id, $values) {
        $params = $this->validateInputs($values, $id);
        $params['slug'] = $this->slugify($params[':title']);
        $sql = "UPDATE {$this->TABLE} SET
                title=:title,
                slug=:slug,
                data=:data,
                published=:published,
                filter=:filter,
                updated=NOW()
                WHERE id = {$id}";
        return $this->registry->db->Query($sql, $params);
    }

    public function updateRel($values, $id) {
        $values = $this->validateInputs($values, $id);

        $sql = "DELETE FROM " . $this->REL_TABLE . " WHERE " . $this->REL_TABLE_FK_1 . " = '$id';";
        $sql .= "INSERT INTO {$this->REL_TABLE}($this->REL_TABLE_FK_1, $this->REL_TABLE_FK_2) VALUES";
        foreach($values as $val) {
            $sql .= "($id, $val),";
        }
        $sql = rtrim($sql, ',').";";
        return $this->registry->db->Query($sql);
    }

    protected function validateInputs($input = null, $id = null) {
        if(isset($id))
            is_numeric($id) or die('Check: Id must be numeric.');
        if(isset($input) && is_array($input)) {
            foreach($input as $key => $val) {
                $input[$key] = empty($val) ? null : strip_tags($val);
            }
        }
        else if (isset($input)) {
            $input = strip_tags($input);
        }
        return $input;
    }

    protected function slugify($str) { 
        $str = strtolower(trim($str)); 
        $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str); 
        $str = preg_replace('/[^a-z0-9-]/', '-', $str); 
        $str = trim(preg_replace('/-+/', '-', $str), '-'); 
        return $str; 
    }

    public abstract function getForm($id = null);
}