<?php

	require '../vendor/autoload.php';

	use Firebase\JWT\JWT;

	if(isset($_COOKIE['token']))
	{
		
		$decoded = JWT::decode(
			$_COOKIE['token'],
			getenv("JWT_SECRET"),
			["HS256"]);

		if($decoded->version == getenv("TOKEN_VERSION")) {
			setcookie("id", $decoded->id);
			setcookie("role", $decoded->role);
			redirect("panel.php");
		} else {
			setcookie("id", null, time() - 3600);
			setcookie("role", null, time() - 3600);
			setcookie("token", null, time() - 3600);
			redirect("login.php");
		}
	}
	redirect("login.php");

	function redirect($url, $statusCode = 303) {
		header('Location: ' . $url, true, $statusCode);
		die();
	}

?>