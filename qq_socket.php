<?php

    
    namespace socket\init;

    class webscoket{
        private $connection;
        private $name = "websocket";
        private $connections = [];
        private $count = 0;

        private $client_data = "";
        /**
         * @method 构造函数
         * @author qxt
         * @return void
         */
        function __construct(object $data, object $con, array $cons){
            $this->set_count(count($cons));
            $this->set_connections($con, $cons);
            $this->set_client_data($data);

            $filed = $this->is_have_file();
            if($filed){
                $type = $this->file_type();
                $this->upload_file($type);
            }
            $this->socket_msg();
        }


        /**
         * @method 获取当前连接的所有用户
         * @author qxt
         * @return void
         */
        public function set_connections(object $con,array $cons):void{
            $this->connection = $con;
            $this->connections = $cons;
        }



        /**
         * @method 是否有文件内容
         * @author qxt
         * @return boolean
         */
        public function is_have_file():bool{
            $bool = false;
            if(isset($this->client_data->file)){
                $bool = true;
            }

            return $bool;
        }
        /**
         * @method 保存文件流数据
         * @author qxt
         * @return boolean
         */
        public function upload_file(string $ext):bool{
            $file = $this->client_data->file;
            $inner = $file->inner;
            $name = date("Y-m-d h:i:s");
            $upfile = fopen("./${name}.${ext}","w");
            if($upfile){
                fwrite($upfile, $inner);
                fclose($upfile);
                return true;
            }else{
                return false;
            }
        }     

        /**
         * @method 如果有文件流,获取文件后缀
         * @author qxt
         * @return string
         */
        public function file_type():string{
            $type = $this->client_data->file->type;
            $ext = explode(".", $file->name)[1];
            return $ext;
        }

        /**
         * @method 单人或是多人发送信息
         * @author qxt
         * @return void
         */
        public function socket_msg():void{
            $data = json_encode($this->client_data->data);

            if(isset($data->from_id) && isset($data->to_id)){
                $this->connections[$data->to_id]->send($data);
            }else{
                foreach($this->connections as $client){
                    $client->send($data);
                }
            }

        }
        /**
         * @method 设置当前需要发送人数
         * @author qxt
         * @return void
         */
        public function set_count(int $n):void{
            $this->count = $n;
        }

        * @author qxt
        /**
         * @method 获取当前用户需要发送的数据
         * @return void
         */
        public function set_client_data(object $data):void{
            $this->client_data = $data;
        }
    }

    
    
    
    
    require_once "${workerman}/Autoloader.php";
    require_once "${workerman}/Connection.php";
    
    use socket\init\websocket;
    use Workerman\Worker;
    use Workerman\MySQL\Connection;
    
    
    
    header("Access-Control-Allow-Origin: *");
    
    $websocket = new Worker("websocket://localhost:909");
    define("workerman", "./Workerman-master/");

    $mysql = new Connection("localhost","3306","root","root","qq");



    $websocket->onMessage = function($connection, $data){
        $data = json_decode($data);
        $ws = new webscoket($data, $connection, $connection->worker->connections);
        unset($ws);
    };


    $websocket->onClose = function($connection, $data){

    };

    Worker::runAll();
?>
