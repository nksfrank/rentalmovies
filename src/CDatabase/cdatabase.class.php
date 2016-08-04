<?php

class CDatabase {
    private $registry;
    private $dbObj;
    private $stmt;

    private static $numQueries = 0;
    private static $queries = array();
    private static $params = array();

    function __construct($registry) {
        $this->registry = $registry;
        self::Connect();
    }

    public function Connect() {
        $default = array(
            'dsn' => null,
            'username' => null,
            'password' => null,
            'driver_options' => null,
            'fetch_style' => PDO::FETCH_OBJ,
        );

        $default = array_merge($default, $this->registry->database);

        try{
            $this->dbObj = new PDO($default['dsn'], $default['username'],
                $default['password'], $default['driver_options']);
        }
        catch(Exception $e) {
            echo $e->getMessage();
            throw new PDOException('Could not connect to database, hiding connection details.');
        }

        $this->dbObj->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $default['fetch_style']);
    }

    public function QueryAndFetchAll($query, $params=array()) {
        self::$queries[] = $query;
        self::$params[] = $params;
        self::$numQueries++;

        if($this->registry->debug) {
            echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".var_dump($params, 1)."</pre></p>";
        }

        $this->stmt = $this->dbObj->prepare($query);
        $this->stmt->execute($params);

        return $this->stmt->fetchAll();
    }

    public function QueryAndFetch($query, $params=array()) {
        self::$queries[] = $query;
        self::$params[] = $params;
        self::$numQueries++;

        if($this->registry->debug) {
            echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".var_dump($params, 1)."</pre></p>";
        }
        try {
            $this->stmt = $this->dbObj->prepare($query);
            $this->stmt->execute($params);
            $res = $this->stmt->fetch();
        }
        catch (Exception $ex) {
            echo $ex->getMessage();
        }

        return $res;
    }

    public function Query($query, $params=array()) {
        self::$queries[] = $query;
        self::$params[] = $params;
        self::$numQueries++;

        if($this->registry->debug) {
            echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".var_dump($params, 1)."</pre></p>";
        }

        $this->stmt = $this->dbObj->prepare($query);

        return (bool)$this->stmt->execute($params);
    }

    public function Dump() {
        $html  = '<p><i>You have made ' . self::$numQueries . ' database queries.</i></p><pre>';
        foreach(self::$queries as $key => $val) {
            $params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], 1)) . '<br/></br>';
            $html .= $val . '<br/></br>' . $params;
        }
        return $html . '</pre>';
    }

    public function LastInsertId() {
        return $this->dbObj->lastInsertId();
    }
    public function RowCount() {
        return is_null($this->stmt) ? null : $this->stmt->rowCount();
    }

    public function ErrorCode() {
        return $this->stmt->errorCode();
    }

    public function ErrorInfo() {
        return $this->stmt->errorInfo();
    }
}