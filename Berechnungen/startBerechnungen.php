<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

include_once '../spezielle Funktionen/aktuellesSemester.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$aktuellesSemester = getAktuellesSemester();
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="refresh" content="0; URL=CopyBilanzToZeitkonto.php">
        <title>Erstellung der Zeitkonten</title>
    </head>
    <body>
		<h1>Erstellung der Zeitkonten</h1>
		<p>
			für das <?= $aktuellesSemester['Text'] ?> läuft... <br>
			Bitte warten... <br>
		</p>
    </body>
</html>


