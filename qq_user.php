<?php

    class qq_user{

        /**
         * @author 
         * @return string
         * @method 生成唯一帐号  
         * @
         * */
        public function create_qq_acc():string{
            $array = [];
            $min_count = 8;
            $acc = "";
            for($i=0;$i<$min_count;$i++){
                $acc .= rand(1,$min_count);
            }
            $exists = $this->check_qq_acc_exists($acc);

            if($exists){
                $this->create_qq_acc();
            }else{
                return $acc;
            }
        }
        /** 
         * @return boolean
         * @method 查看账号是否存在
         * @param string 账号
        */
        public function check_qq_acc_exists(string $acc):bool{

            $query = PDOFunc('
                SELECT count(0) FROM qq_users WHERE q_acc LIKE "'.$acc.'"
            ');
            $column = $query->fetchColumn();

            if($column > 0){
                return true;
            }else{
                return false;
            }
        }

        /**
         * @return string
         * @author qxt
         * @method 账号注册
         * 
         */
        public function qq_register():string{
            $acc = $this->create_qq_acc();
            $pass = $_REQUEST["p"];
            $nick = $_REQUEST["n"];
            $md5_pass = md5($pass);
            PDOFunc('
                INSERT INTO qq_users(q_name,q_acc,q_pass,q_md5_pass) 
                VALUES("'.$nick.'","'.$acc.'","'.$pass.'","'.$md5_pass.'")
            ');
            return $this->_res("success",1,["acc"=>$acc]);
        }


        /**
         * @return string
         * @method 获取密码
         * @author qxt
         */
        public function qq_pass():string{
            $pass = $_REQUEST["p"];
            $type = gettype($pass);
            $type = strtolower($type);
            $s = $pass;
            if($type == "array"){
                foreach($pass as $k){
                    $s .= chr($v);
                }
            }
            return $s;
        }
        /**
         * @return boolean
         * @author qxt
         * @method 用户登录
         */
        public function qq_sign():string{
            $acc = $_REQUEST["acc"];
            $pass = $this->qq_pass();
            $msg = "登录成功";
            $code = 1;
            $data = [];
            $query = PDOFunc('
                SELECT * FROM qq_users WHERE q_acc LIKE "'.$acc.'" AND q_pass LIKE "'.$pass.'"
            ');
            $count = $query->rowCount();
            if($count > 0){
                $data = $query->fetch(PDO::FETCH_ASSOC);
            }else{
                $code = 0;
                $msg = "账号密码不匹配";
            }

            return $this->_res($msg,$code,$data);
        }

        public function _res(string $msg, int $code, array $data){
            return json_encode(["msg"=>$msg,"code"=>$code,"data"=>$data]);
        }
    }
?>