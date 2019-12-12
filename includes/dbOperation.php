<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DbOperation
{

    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/dbConnect.php';
        $db = new DbConnect();
        if ($db == null)
            throw new Exception("Can't connect with database, PDO is null.");
        $this->con = $db->connect();
        if ($this->con == null)
            throw new Exception("Can't connect with database, connection is null");
    }

    public function login($email, $pass, &$id, &$role)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare("SELECT id, encrypted_password, role, active FROM public.users WHERE email= :email;");
        $query->bindValue(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if ($result == null || count($result) == 0) {
            return USER_NOT_FOUND;
        } elseif (filter_var($result['active'], FILTER_VALIDATE_BOOLEAN) == false) {
            return USER_NOT_ACTIVE;
        } else {
            if (password_verify($pass, $result['encrypted_password'])) {
                $id = $result['id'];
                $role = $result['role'];
                return USER_AUTHENTICATED;
            } else {
                return USER_PASSWORD_DO_NOT_MATCH;
            }
        }
    }

    public function createUser($email, $pass, $role, $active, $first_name, $last_name, $title, $phone, $days_availabe, $notify)
    {
        if ($this->con == null)
            return DB_ERROR;

        if (!$this->isEmailExist($email)) {
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

            if ($query->execute()) {
                return USER_CREATED;
            } else {
                return USER_FAILURE;
            }
        } else {
            return USER_EXISTS;
        }
    }

    private function isEmailExist($email)
    {
        $query = $this->con->prepare('SELECT id FROM public.users WHERE email = :email');
        $query->bindValue(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetchAll();
        return count($result);
    }

    private function isIdExist($id): int
    {
        $query = $this->con->prepare('SELECT id FROM public.users WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetchAll();
        return count($result);
    }

    public function isUserActive($id)
    {
        $query = $this->con->prepare('SELECT active FROM public.users WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return filter_var($result['active'], FILTER_VALIDATE_BOOLEAN);
    }

    public function getUserRole($id)
    {
        $query = $this->con->prepare('SELECT role FROM public.users WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['role'];
    }

    public function getAllUsers(&$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare('SELECT * FROM public.users', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if ($query->execute()) {
            $result['data_length'] = $query->rowCount();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $result['data'][] = $row;
            }
            return GET_USERS_SUCCESS;
        } else {
            return GET_USERS_FAILURE;
        }
    }

    public function getAllUsersShort(&$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare(
                'SELECT email, avatar_file_name, avatar_content_type, avatar_file_size, role, active, first_name, last_name, title, phone
                        FROM public.users',
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        if ($query->execute()) {
            $result['data_length'] = $query->rowCount();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $result['data'][] = $row;
            }
            return GET_USERS_SUCCESS;
        } else {
            return GET_USERS_FAILURE;
        }
    }

    public function getUserShort($id, &$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare(
            'SELECT email, avatar_file_name, avatar_content_type, avatar_file_size, role, active, first_name, last_name, title, phone
                    FROM public.users  WHERE id = :id;',
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        $query->bindValue(':id', $id, PDO::PARAM_STR);

        if ($query->execute()) {
            if ($query->rowCount() == 1) {
                $result['data_length'] = $query->rowCount();
                $result['data'][] = $query->fetch(PDO::FETCH_ASSOC);
                return GET_USERS_SUCCESS;
            } else {
                return GET_USERS_NOT_FOUND;
            }
        } else {
            return GET_USERS_FAILURE;
        }
    }

    public function deleteUsersByEmail($email)
    {
        if ($this->con == null)
            return DB_ERROR;

        if ($this->isEmailExist($email)) {
            $query = $this->con->prepare('DELETE FROM public.users WHERE email = :email');
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            if ($query->execute()) {
                return DELETE_USER_SUCCESS;
            } else {
                return DELETE_USER_FAILURE;
            }
        } else {
            return USER_NOT_FOUND;
        }
    }

    public function deleteUsersById($id)
    {
        if ($this->con == null)
            return DB_ERROR;

        if ($this->isIdExist($id) > 0) {
            $query = $this->con->prepare('DELETE FROM public.users WHERE id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            if ($query->execute()) {
                return DELETE_USER_SUCCESS;
            } else {
                return DELETE_USER_FAILURE;
            }
        } else {
            return USER_NOT_FOUND;
        }
    }

    //TIMESHEET OPERATIONS ******************************************
    public function getTimesheet(&$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare(
            'SELECT id, user_id, date, "from", "to", customer_break, statutory_break, comments, project_id, company_id, status, created_at, updated_at, project
                    FROM public.timesheets',
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );

        if ($query->execute()) {
            $result['data_length'] = $query->rowCount();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $result['data'][] = $row;
            }

            return GET_TIMESHEET_SUCCESS;
        } else {
            return GET_TIMESHEET_FAILURE;
        }
    }

    public function getTimesheetByUser($user_id, &$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare(
            'SELECT id, user_id, date, "from", "to", customer_break, statutory_break, comments, project_id, company_id, status, created_at, updated_at, project
                    FROM public.timesheets WHERE user_id = :user_id;',
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        $query->bindValue(':user_id', $user_id, PDO::PARAM_STR);

        if ($query->execute()) {
            $result['data_length'] = $query->rowCount();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $result['data'][] = $row;
            }

            return GET_TIMESHEET_SUCCESS;
        } else {
            return GET_TIMESHEET_FAILURE;
        }
    }

    public function getTimesheetById($id, &$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare(
            'SELECT id, user_id, date, "from", "to", customer_break, statutory_break, comments, project_id, company_id, status, created_at, updated_at, project
                    FROM public.timesheets WHERE id = :id;',
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        $query->bindValue(':id', $id, PDO::PARAM_STR);

        if ($query->execute()) {
            $result['data_length'] = $query->rowCount();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $result['data'][] = $row;
            }

            return GET_TIMESHEET_SUCCESS;
        } else {
            return GET_TIMESHEET_FAILURE;
        }
    }

    public function createTimesheetRow($user_id, $date, $from, $to, $customer_break, $statutory_break, $comments, $project_id, $company_id, $status, $created_at, $updated_at, $project, &$result)
    {
        if ($this->con == null)
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
                    updated_at,
                    project)
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
                    :updated_at,
                    :project);"
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
        $query->bindValue(':project', $project, PDO::PARAM_STR);

        if ($query->execute()) {
            $result['id'] = $this->con->lastInsertId('public.timesheets_id_seq');
            return INSERT_TIMESHEETROW_SUCCESS;
        } else {
            return INSERT_TIMESHEETROW_FAILURE;
        }
    }

    public function deleteTimesheetRowById($id)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare('DELETE FROM public.timesheets WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        if ($query->execute()) {
            return DELETE_TIMESHEETROW_SUCCESS;
        } else {
            return DELETE_TIMESHEETROW_FAILURE;
        }
    }

    public function updateTimesheetRowById(int $id, array $params): int
    {
        if ($this->con == null)
            return DB_ERROR;

        if ($this->isTimesheetExist($id)) {
            //$query = $this->con->prepare('DELETE FROM public.users WHERE email = :email');
            //$query->bindValue(':email', $email, PDO::PARAM_STR);
            //$query = createUpdateQuery('public.timesheets' ,$params, array('id' => $id));

            $this->con->query("SET TIMEZONE TO 'CET';");
            if (!isset($params['updated_at'])) {
                //$now = new DateTime();
                //$params['updated_at'] = $now->format('Y-m-d H:i:s.u');
                $params['updated_at'] = "NOW()";
            }
            $nullKeys = array('comments', 'project');

            $sql = $this->createUpdateQuery('public.timesheets', $params, array('id' => $id), $nullKeys);

            $query = $this->con->prepare($sql);
            $params['id'] = $id;

            foreach ($nullKeys as $nullKey) {
                if (!array_key_exists($nullKey, $params)) {
                    $params[$nullKey] = NULL;
                }
            }

            try {
                if ($query->execute($params)) {
                    return UPDATE_TIMESHEETROW_SUCCESS;
                } else {
                    return UPDATE_TIMESHEETROW_FAILURE;
                }
            } catch (Exception $e) {
                $log = new Logger(DEBUG_TAG);
                $log->addWarning($e->getMessage());
                return UPDATE_TIMESHEETROW_FAILURE;
            }
        } else {
            return TIMESHEET_NOT_FOUND;
        }
    }

    private function isTimesheetExist($id): int
    {
        $query = $this->con->prepare('SELECT id FROM public.timesheets WHERE id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetchAll();
        return count($result);
    }

    // COUNTRY OPERATIONS *************************************************************
    public function getCountries(&$result)
    {
        if ($this->con == null)
            return DB_ERROR;

        $query = $this->con->prepare(
            'SELECT id, name, currency, cash_per_day, hotel_cash_per_day, created_at, updated_at, objectives
                FROM public.countries;',
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );

        if ($query->execute()) {
            $result['data_length'] = $query->rowCount();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $result['data'][] = $row;
            }

            return GET_COUNTRIES_SUCCESS;
        } else {
            return GET_COUNTRIES_FAILURE;
        }
    }

    // COMMON FUNCTIONS ***************************************************************
    private function createUpdateQuery(string $tablename, array $params, array $where, array $nullParams = []): string
    {
        //UPDATE public.timesheets
        //SET id=?, user_id=?, date=?, "from"=?, "to"=?, customer_break=?, statutory_break=?, comments=?, project_id=?, company_id=?, status=?, created_at=?, updated_at=?, project=?
        //WHERE <condition>;
        $valueSets = array();
        foreach ($params as $key => $value) {
            $valueSets[] = "\"" . $key . "\" = :" . $key;
        }
        foreach ($nullParams as $nullKey) {
            if (!array_key_exists($nullKey, $params)) {
                $valueSets[] = "\"" . $nullKey . "\" = :" . $nullKey;
            }
        }

        $conditionSets = array();
        foreach ($where as $key => $value) {
            $conditionSets[] = "\"" . $key . "\" = :" . $key;
        }

        return $sql = "UPDATE $tablename SET " . join(",", $valueSets) . " WHERE " . join(" AND ", $conditionSets);
    }
}
