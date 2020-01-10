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
		<main>
			<div class="container my-2">
				<div class="row mt-3" id="userPickerContainer">
					<div class="col-md-3 col-lg-3 offset-lg-1">
						<label class="text-center">Wybierz użytkownika</label>
					</div>
					<div class="col-md-4" id="divUserSelectorWorkTime">
						<select id="userSelectWorkTime" class="w-100 h-100">
							<!-- <option value="test1">Test1</option>
					<option value="test2">Test2</option>
					<option value="test3">Test3</option>
					<option value="test4">Test4</option> -->
						</select>
					</div>
					<div class="col-md-4 col-lg-3">
						<button class="w-100 h-100" id="userGetWorkTime">Pobierz godziny</button>
					</div>
				</div>

				<div class="row mt-2" id="timeSelectWeek">
					<div class="col-md-3 col-lg-2 offset-lg-1 my-auto">
						<input type="radio" id="timeRangeWeek" name="timeRange" value="week">
						<label for="timeRangeWeek">Tydzień</label>
					</div>
					<div class="col-md-4">
						<input type="week" id="inputWeek" value="2020-W05" required>
					</div>
				</div>

				<div class="row mt-2" id="timeSelectMonth">
					<div class="col-md-3 col-lg-2 offset-lg-1 my-auto">
						<input type="radio" id="timeRangeMonth" name="timeRange" value="month" checked>
						<label for="timeRangeMonth">Miesiąc</label>
					</div>
					<div class="col-md-4">
						<input type="month" id="inputMonth" value="2020-05" required>
					</div>
				</div>

				<div class="row mt-2" id="timeSelectDateToDate">
					<div class="col-md-3 col-lg-2 offset-lg-1 my-auto">
						<input type="radio" id="timeRangePeriod" name="timeRange" value="period">
						<label for="timeRangePeriod">Okres</label>
					</div>
					<div class="col-md-4" style="display: flex;">
						<label class="text-center" for="inputPeriodFrom" style="flex: 0.15">Od</label>
						<input type="date" style="flex: 0.85" id="inputPeriodFrom" value="2020-05-01" required>

					</div>
					<div class="col-md-4" style="display: flex;">
						<label class="text-center" for="inputPeriodTo" style="flex: 0.15">Do</label>
						<input type="date" style="flex: 0.85" id="inputPeriodTo" value="2020-06-30" required>
					</div>
				</div>

				<div id="tableContainer">
					<!-- <table class="table table-responsive table-dark my-3" style="font-size: 14px; white-space: nowrap;">
					<thead>
						<tr>
							<th scope="col" style="width: 40px">#</th>
							<th scope="col" style="width: 140px">Data</th>
							<th scope="col" style="width: 40px">Od</th>
							<th scope="col" style="width: 40px">Do</th>
							<th scope="col" style="width: 40px">Przer. klienta</th>
							<th scope="col" style="width: 40px">Przer. ustawowa</th>
							<th scope="col" style="width: 200px">Projekt</th>
							<th scope="col" style="width: 100%">Komentarz</th>
						</tr>
					</thead>
					<tbody id="timesheet">

					</tbody>
				</table> -->
				</div>

			</div>

			<div class="error m-3"></div>
		</main>
	</div>

	<script src="jquery-3.4.1.js"></script>
	<script src="jquery.cookie.js"></script>
	<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script> -->
	<!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script> -->
	<script src="bootstrap-4.4.1-dist/js/bootstrap.min.js"></script>
	<script src="panel.js"></script>
	<script src=" https://MomentJS.com/downloads/moment.js"></script>

</body>

</html>