<?php
	use Firebase\JWT\JWT;

	// if(isset($_COOKIE['token']))
	// {
		
	// 	$decoded = JWT::decode(
	// 		$_COOKIE['token'],
	// 		getenv("JWT_SECRET"),
	// 		["HS256"]);

	// 	if($decoded->version == getenv("TOKEN_VERSION")) {
	// 		setcookie("id", $decoded->id);
	// 		setcookie("role", $decoded->role);
	// 		redirect("panel.php");
	// 	} else {
	// 		setcookie("id", null, time() - 3600);
	// 		setcookie("role", null, time() - 3600);
	// 		setcookie("token", null, time() - 3600);
	// 	}
	// }

	function redirect($url, $statusCode = 303) {
		header('Location: ' . $url, true, $statusCode);
		die();
	}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatibile" content="IE=egde,chrome=1"/>
	<title>Pro-Rob Intranet API</title>
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Josefin+Sans|Lato&amp;subset=latin-ext" rel="stylesheet">
	
</head>
<body>

	<div id="container-panel">
        
        <header>
        
        </header>
		<nav>
            <ul>
                <li>Czas pracy</li>
                <li>Wyloguj</li>
            </ul>
        </nav>

		<div class=error></div>

	</div>

	<script src="jquery-3.4.1.js"></script>
    <script src="panel.js"></script>

</body>
</html>