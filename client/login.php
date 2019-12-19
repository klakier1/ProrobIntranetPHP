<?php
if (isset($_COOKIE['token'])) {
	header('Location: index.php');
	die();
}
?>

<!DOCTYPE HTML>
<html lang="pl">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatibile" content="IE=egde,chrome=1" />
	<title>Pro-Rob Intranet API</title>
	<link rel="stylesheet" href="bootstrap-4.4.1-dist/css/bootstrap.min.css" type="text/css" />
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Josefin+Sans|Lato&amp;subset=latin-ext" rel="stylesheet">

</head>

<body>

	<div id="container-login">
		<h2>Pro-Rob Intranet API</h2>
		<h3>Zaloguj się</h3>

		<form id="loginForm" method="post" onSubmit="jQlogin()">

			<!--	<input type="text" name="login" placeholder="Login" onfocus="this.placeholder = '';" onblur="this.setAttribute('placeholder','Login');"/>		
				<input type="password" name="haslo" placeholder="Hasło" onfocus="this.setAttribute('placeholder','');" onblur="this.placeholder='Hasło'"/>	-->
			<input type="text" autocomplete="username" name="email" placeholder="Email" />
			<input type="password" autocomplete="current-password" name="password" placeholder="Hasło" />
			<input type="submit" value="Zaloguj się" />
			<input type="hidden" name="cookie" value="true" />

		</form>

		<div class=error></div>

	</div>

	<script src="jquery-3.4.1.js"></script>
	<script src="bootstrap-4.4.1-dist/js/bootstrap.min.js"></script>
	<script src="login.js?v=<?php echo time(); ?>"></script>

</body>

</html>