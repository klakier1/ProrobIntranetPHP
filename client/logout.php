<?php

require '../vendor/autoload.php';

setcookie("id", null, time() - 3600);
setcookie("role", null, time() - 3600);
setcookie("token", null, time() - 3600, '/');
session_destroy();
redirect("index.php");

function redirect($url, $statusCode = 303)
{
    header('Location: ' . $url, true, $statusCode);
    die();
}
