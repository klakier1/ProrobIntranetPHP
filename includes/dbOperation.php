<?php

    class DbOperation{

        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/dbConnect.php';
            $db = new DbConnect();
            $this->con = $db->connect();
        }
        
        public function login($email, $pass, &$user_id, &$is_admin){
            if($this->con == null)
                return DB_ERROR;

            $query = $this->con->prepare("SELECT user_id, password, admin FROM public.employees WHERE email= :email;");
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch();

            if($result == null || count($result) == 0){
                return USER_NOT_FOUND;
            }else{
                if(password_verify($pass, $result['password'])){
                    $user_id = $result['user_id'];
                    $is_admin = $result['admin'];
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            }
        }

        public function createUser($email, $pass, $name, $surname){
            if($this->con == null)
                return DB_ERROR;
            
            if(!$this->isEmailExist($email)){
                $hash_password = password_hash($pass, PASSWORD_DEFAULT);
                $query = $this->con->prepare("INSERT INTO public.employees(name, surname, password, email, created_on, last_login) VALUES (:user, :surname, :pass, :email, NOW(), null);");
                $query->bindValue(':pass', $hash_password, PDO::PARAM_STR);
                $query->bindValue(':surname', $surname, PDO::PARAM_STR);
                $query->bindValue(':user', $name, PDO::PARAM_STR);
                $query->bindValue(':email', $email, PDO::PARAM_STR);
                if($query->execute()){
                    return USER_CREATED;
                }else{
                    return USER_FAILURE;
                }
            }else{
                return USER_EXISTS;
            }
        }

        private function isEmailExist($email){
            $query = $this->con->prepare('SELECT user_id FROM public.employees WHERE email = :email');
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetchAll();
            return count($result);
        }

        private function isIdExist($id){
            $query = $this->con->prepare('SELECT user_id FROM public.employees WHERE user_id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetchAll();
            return count($result);
        }

        public function getAllUsers(&$result){
            if($this->con == null)
                return DB_ERROR;
            
            $query = $this->con->prepare('SELECT * FROM public.employees', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            if($query->execute()){
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return GET_USERS_SUCCESS;
            }else{
                return GET_USERS_FAILURE;
            }
        }

        public function deleteUsersByEmail($email){
            if($this->con == null)
                return DB_ERROR;

            if($this->isEmailExist($email)){
                $query = $this->con->prepare('DELETE FROM public.employees WHERE email = :email');
                $query->bindValue(':email', $email, PDO::PARAM_STR);
                if($query->execute()){
                    return DELETE_USER_SUCCESS;
                }else{
                    return DELETE_USER_FAILURE;
                }
            }else{
                return USER_NOT_FOUND;
            }
        }

        public function deleteUsersById($id){
            if($this->con == null)
                return DB_ERROR;
            
            if($this->isIdExist($id)){
                $query = $this->con->prepare('DELETE FROM public.employees WHERE user_id = :id');
                $query->bindValue(':id', $id, PDO::PARAM_STR);
                if($query->execute()){
                    return DELETE_USER_SUCCESS;
                }else{
                    return DELETE_USER_FAILURE;
                }
            }else{
                return USER_NOT_FOUND;
            }
        }
    }
?>