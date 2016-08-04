<?php
/**
 * Created by PhpStorm.
 * User: Niklas
 * Date: 2014-09-09
 * Time: 17:50
 */

class helper {
    private $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    public function dump($array) {
        echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
    }

    public function response($data, $status = 200) {
        header("HTTP/1.1 " . $status . $this->requestStatus($status));
        header("Content-type: text/html");
        echo $data;
    }

    private function requestStatus($code) {
        $status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');
        return ($status[$code])?$status[$code]:$status[500];
    }

    public function arrangeFilesArray(&$files) {
        $vars = array('name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

        foreach($files as $key => $part) {
            $key = (string)$key;
            if(isset($vars[$key]) && is_array($part)) {
                foreach($part as $position => $value) {
                    $files[$position][$key] = $value;
                }
                unset($files[$key]);
            }
        }
    }

    public function appendUrl($parameters, $keep_url = true) { 
        if(!isset($parameters) || is_null($parameters)) 
            return; 

        if(!is_array($parameters)) { 
            $param = []; 
            foreach(explode("&", $parameters) as $value) { 
                foreach(explode("=", $value) as $key => $val) { 
                    $param[$key] = $val; 
                } 
            } 
            $parameters = $param; 
        } 

        if($keep_url) { 
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY); 
            parse_str($query, $p); 
            $parameters = array_merge($p, $parameters); 
        } 

        $url = "?"; 

        foreach($parameters as $key => $val) { 
            $url .= "$key=$val&"; 
        } 
        return rtrim($url, '&'); 
    }

    public function strrpos_arr($haystack, $needles, $offset = 0) {
        if(!is_array($needles)) return strrpos($haystack, $needles, $offset);
        $a = 0;
        foreach($needles as $needle) {
            $t =  strrpos($haystack, $needle, $offset);
            $a = $a > $t ? $a : $t;
        }
        return $a;
    }
}