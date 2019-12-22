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
			<nav class="navbar navbar-dark navbar-expand-lg">
				<!-- <a class="navbar-brand" href="#">Pro-rob</a> -->
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainmenu" aria-controls="mainmenu" aria-expanded="false" aria-label="Przełącznik nawigacji">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="mainmenu">
					<!-- LEWA CZESC -->
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="nav-link" href="#"> Czas pracy </a>
						</li>
					</ul>
					<!-- PRAWA CZESC -->
					<ul class="navbar-nav ml-auto">
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" data-toggle="dropdown" type="button" role="button" id="userName" href="#" aria-expanded="false" aria-haspopup="true">
								<div class="spinner-border"></div>
							</a>
							<!-- SUBMENU -->
							<ul class="dropdown-menu">
								<li class="dropdown-item">
									<a class="dropdown-link" href="logout.php"> Wyloguj </a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</nav>
		</header>

		
		<div class="error"></div>

		<div class="container">
			<div class="row">
				<span class="col-md-3">Wybierz użytkownika</span>
				<select class="col-md-4" id="userSelectorWorkTime">
					<option value="test1">Test1</option>
					<option value="test2">Test2</option>
					<option value="test3">Test3</option>
					<option value="test4">Test4</option>
				</select>
			</div>
		</div>
	</div>

	<script src="jquery-3.4.1.js"></script>
	<script src="jquery.cookie.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="bootstrap-4.4.1-dist/js/bootstrap.min.js"></script>
	<script src="panel.js"></script>

</body>

</html>