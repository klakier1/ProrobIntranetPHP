<?php

    class DbConnect{

        private $con;

        function connect(){

            include_once dirname(__FILE__) . '/constants.php';

            try
            {
                $credentials = $this->getDbCredentials();
                extract($credentials);
                $path = ltrim($path, "/");
                
                $this->con = new PDO(
                    sprintf("pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s", $host, $port, $path, $user, $pass),
                    $user,
                    $pass,
                    [
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]
                );
            } 
            catch(PDOException $e)
            {
                //echo "Failed to connect " . $e->getCode() . " " . $e->getMessage();
                return null;
            }
            catch(Exception $e)
            {
                //echo "Failed to connect " . $e->getCode() . " " . $e->getMessage();
                return null;
            }

            return $this->con;
        }

        function getDbCredentials(){

            $database_url = getenv("DATABASE_URL_PROROB");
            //$database_url = getenv("DATABASE_URL_LOCAL");
            return parse_url($database_url);
        }
    }