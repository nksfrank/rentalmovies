<?php

class CUser {
    private $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function Login($user, $password) {
        $this->registry->db->Connect();

        $sql = "SELECT acronym, name FROM rm_user WHERE acronym = ? AND password = md5(concat(?, salt))";
        $param = array($user, $password);

        $res = $this->registry->db->QueryAndFetch($sql, $param);
        if(isset($res) && $res != false) {
            $_SESSION['user'] = $res;
        }
        return (isset($_SESSION['user']));
    }

    public function Logout() {
        unset($_SESSION['user']);
    }

    public function IsAuthenticated() {
        return isset($_SESSION['user']);
    }

    public function GetAcronym() {
        return isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

    }

    public function GetName() {
        return (isset($_SESSION['user']) ? $_SESSION['user']->name : null);
    }

    public function ShowForm() {
        if(!$this->IsAuthenticated()) {
        $form = <<<EOD
        <a href='login.php'><div class='fr header-item'>LOGIN</div></a>
EOD;
        }
        else {
            $form = <<<EOD
            <a href='content.php'>
                <div class='fr header-item'>ADMIN</div>
            </a>
EOD;
        }
        return $form;
    }
}