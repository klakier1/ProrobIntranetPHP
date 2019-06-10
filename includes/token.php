<?php

    function checkTokenData($token)
    {
        if($token['version'] == getenv("TOKEN_VERSION")){
            $db = new DbOperation; 
            $id = $token['id'];
            if($db->isUserActive($id)){
                if($token['role'] == $db->getUserRole($id)){
                    switch($token['role']){
                        case "admin":{
                            return TOKEN_ADMIN;
                            break;
                        }
                        case "employee":{
                            return TOKEN_EMPLOYEE;
                            break;
                        }
                    }
                }
            }
        }

        return TOKEN_ERROR;
    }

    function getToken($user_id, $role)
    {
        return JWT::encode(['id' => $user_id, 'role' => $role, 'version' => getenv("TOKEN_VERSION")], getenv("JWT_SECRET"), "HS256");
    }


?>