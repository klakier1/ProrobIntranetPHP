<?php
	if(!isset($_COOKIE['token']) || !isset($_COOKIE['id']))
	{
		header('Location: index.php');
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
    <script src="panel.js"></script>

</body>
</html>