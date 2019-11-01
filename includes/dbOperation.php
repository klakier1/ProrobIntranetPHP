<?php

    class DbOperation{

        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/dbConnect.php';
            $db = new DbConnect();
            $this->con = $db->connect();
        }
        
        public function login($email, $pass, &$id, &$role){
            if($this->con == null)
                return DB_ERROR;

            $query = $this->con->prepare("SELECT id, encrypted_password, role, active FROM public.users WHERE email= :email;");
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch();

            if($result == null || count($result) == 0){
                return USER_NOT_FOUND;
            }elseif(filter_var($result['active'], FILTER_VALIDATE_BOOLEAN) == false){
                return USER_NOT_ACTIVE;
            }else{
                if(password_verify($pass, $result['encrypted_password'])){
                    $id = $result['id'];
                    $role = $result['role'];
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            }
        }

        public function createUser($email, $pass, $role, $active, $first_name, $last_name, $title, $phone, $days_availabe, $notify){
            if($this->con == null)
                return DB_ERROR;
            
            if(!$this->isEmailExist($email)){
                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                $query = $this->con->prepare(
                    "INSERT INTO public.users(
                        created_at, 
                        updated_at, 
                        email, 
                        encrypted_password, 
                        confirmation_token, 
                        remember_token, 
                        avatar_file_name, 
                        avatar_content_type, 
                        avatar_file_size, 
                        avatar_updated_at, 
                        role, 
                        active, 
                        first_name, 
                        last_name, 
                        title, 
                        phone, 
                        days_available, 
                        notify)
                    VALUES (
                        NOW(),
                        NOW(), 
                        :email,
                        :pass, 
                        NULL, 
                        'test',
                        NULL, 
                        NULL, 
                        NULL,
                        NULL,
                        :role,
                        :active,
                        :first_name,
                        :last_name, 
                        :title, 
                        :phone, 
                        :days_availabe, 
                        :notify);"
                );

                $query->bindValue(':email', $email, PDO::PARAM_STR);
                $query->bindValue(':pass', $hashed_pass, PDO::PARAM_STR);
                $query->bindValue(':role', $role, PDO::PARAM_STR);
                $query->bindValue(':active', $active, PDO::PARAM_BOOL);
                $query->bindValue(':first_name', $first_name, PDO::PARAM_STR);
                $query->bindValue(':last_name', $last_name, PDO::PARAM_STR);
                $query->bindValue(':title', $title, PDO::PARAM_STR);
                $query->bindValue(':phone', $phone, PDO::PARAM_STR);
                $query->bindValue(':days_availabe', $days_availabe, PDO::PARAM_INT);
                $query->bindValue(':notify', $notify, PDO::PARAM_BOOL);

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
            $query = $this->con->prepare('SELECT id FROM public.users WHERE email = :email');
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetchAll();
            return count($result);
        }

        private function isIdExist($id){
            $query = $this->con->prepare('SELECT id FROM public.users WHERE id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetchAll();
            return count($result);
        }

        public function isUserActive($id){
            $query = $this->con->prepare('SELECT active FROM public.users WHERE id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return filter_var($result['active'], FILTER_VALIDATE_BOOLEAN);
        }

        public function getUserRole($id){
            $query = $this->con->prepare('SELECT role FROM public.users WHERE id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['role'];
        }

        public function getAllUsers(&$result){
            if($this->con == null)
                return DB_ERROR;
            
            $query = $this->con->prepare('SELECT * FROM public.users', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            if($query->execute()){
                $result['data_length'] = $query->rowCount();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $result['data'][] = $row;
                }
                return GET_USERS_SUCCESS;
            }else{
                return GET_USERS_FAILURE;
            }
        }

        public function getUserShort($id, &$result){
            if($this->con == null)
                return DB_ERROR;
            
            $query = $this->con->prepare(
                'SELECT email, avatar_file_name, avatar_content_type, avatar_file_size, role, active, first_name, last_name, title, phone
                    FROM public.users  WHERE id = :id;',
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $query->bindValue(':id', $id, PDO::PARAM_STR);

            if($query->execute()){
                if($query->rowCount() == 1){
                    $result['data_length'] = $query->rowCount();
                    $result['data'][] = $query->fetch(PDO::FETCH_ASSOC);
                    return GET_USERS_SUCCESS;
                }else{
                    return GET_USERS_NOT_FOUND;
                }
            }else{
                return GET_USERS_FAILURE;
            }
        }

        public function deleteUsersByEmail($email){
            if($this->con == null)
                return DB_ERROR;

            if($this->isEmailExist($email)){
                $query = $this->con->prepare('DELETE FROM public.users WHERE email = :email');
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
                $query = $this->con->prepare('DELETE FROM public.users WHERE id = :id');
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


        //TIMESHEET OPERATIONS ******************************************
        public function getTimesheet($id, &$result){
            if($this->con == null)
                return DB_ERROR;

            $query = $this->con->prepare(
                'SELECT id, user_id, date, "from", "to", customer_break, statutory_break, comments, project_id, company_id, status, created_at, updated_at
                    FROM public.timesheets WHERE user_id = :id;',
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $query->bindValue(':id', $id, PDO::PARAM_STR);

            if($query->execute()){
                $result['data_length'] = $query->rowCount();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $result['data'][] = $row;
                }

                return GET_TIMESHEET_SUCCESS;
            }else{
                return GET_TIMESHEET_FAILURE;
            }
        }

        public function createTimesheetRow($user_id, $date, $from, $to, $customer_break, $statutory_break, $comments, $project_id, $company_id, $status, $created_at, $updated_at, &$result){
            if($this->con == null)
                return DB_ERROR;
            
            $query = $this->con->prepare(
                "INSERT INTO public.timesheets(
                    user_id, 
                    date, 
                    \"from\", 
                    \"to\", 
                    customer_break, 
                    statutory_break, 
                    comments, 
                    project_id, 
                    company_id, 
                    status, 
                    created_at, 
                    updated_at)
                VALUES ( 
                    :user_id, 
                    :date, 
                    :from, 
                    :to, 
                    :customer_break, 
                    :statutory_break, 
                    :comments, 
                    :project_id, 
                    :company_id, 
                    :status, 
                    :created_at, 
                    :updated_at);"
            );

            $query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $query->bindValue(':date', $date, PDO::PARAM_STR);
            $query->bindValue(':from', $from, PDO::PARAM_STR);
            $query->bindValue(':to', $to, PDO::PARAM_STR);
            $query->bindValue(':customer_break', $customer_break, PDO::PARAM_STR);
            $query->bindValue(':statutory_break', $statutory_break, PDO::PARAM_STR);
            $query->bindValue(':comments', $comments, PDO::PARAM_STR);
            $query->bindValue(':project_id', $project_id, PDO::PARAM_INT);
            $query->bindValue(':company_id', $company_id, PDO::PARAM_INT);
            $query->bindValue(':status', $status, PDO::PARAM_BOOL);
            $query->bindValue(':created_at', $created_at, PDO::PARAM_STR);
            $query->bindValue(':updated_at', $updated_at, PDO::PARAM_STR);

            if($query->execute()){
                $result['id'] = $this->con->lastInsertId('public.timesheets_id_seq');
                return INSERT_TIMESHEETROW_SUCCESS;
            }else{
                return INSERT_TIMESHEETROW_FAILURE;
            }
        }
    }
?>