<?php
if (!isset($_COOKIE['token']) || !isset($_COOKIE['id'])) {
	header('Location: index.php');
	die();
}
?>

<!DOCTYPE HTML>
<html lang="pl">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatibile" content="IE=egde,chrome=1" />
	<title>Pro-Rob Intranet API</title>
	<link rel="stylesheet" href="bootstrap-4.4.1-dist/css/bootstrap.min.css" type="text/css" />
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Josefin+Sans|Lato&amp;subset=latin-ext" rel="stylesheet">

</head>

<body>

	<div id="container-panel">

		<header>
			<nav>
				<ul>
					<li>Czas pracy</li>
					<li>Wyloguj</li>
				</ul>
			</nav>
		</header>

		<div class=error></div>

	</div>

	<script src="jquery-3.4.1.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="bootstrap-4.4.1-dist/js/bootstrap.min.js"></script>
	<script src="panel.js"></script>

</body>

</html>