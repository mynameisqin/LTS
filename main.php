<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html;charset=utf8");
    define("CLASS_NAME","MAIN_CLASS");
    define("SERVER",$_SERVER);
    define("POST",$_POST);
    define("GET",$_GET);
    define("REQUEST",$_REQUEST);
    
    class MAIN_CLASS{
        function __construct(){    

        }
        public function _route():bool{
            $path_info = SERVER["PATH_INFO"];
            $response = true;
            if(!isset($path_info)){
                $response = false;
            }
            return $response;
        }

        /**
         * @method 
         * @param void
         * @return array
         * @author 
         */
        public function _use_route():array{
            $exists = $this->_route();
            if($exists){
                $array = explode("/", SERVER["PATH_INFO"]);
                list($null,$class,$func) = $array;
                return [$class,$func];
            }
            else{
                return ["code"=>0];
            }
        }
    }

    function PDOFunc(string $sql):Object{
        $pdo = new PDO("mysql:host=localhost;dbname=qq","root","root");
        $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->query("set names utf8");
        $query = $pdo->query($sql) or die("SQL 语句出错". $sql);
        $pdo = null;
        return $query;
    }

    $m = new MAIN_CLASS();
    $array = $m->_use_route();
    $_error_response = ["msg"=>"Param Error","code"=>0];
    $_success_response = ["msg"=>"Success","code"=>1];
    if(array_key_exists("code", $array)){
        echo json_encode($_error_response);
    }else{
        require_once "./".$array[0].".php";
        $_success_response = call_user_func_array([new $array[0](),$array[1]],[]);
        echo $_success_response;
    }
?>