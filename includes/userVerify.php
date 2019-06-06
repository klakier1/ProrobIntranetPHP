<?php

    function checkTokenData($token)
    {
        if($token['version'] == TOKEN_VERSION){
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


?>