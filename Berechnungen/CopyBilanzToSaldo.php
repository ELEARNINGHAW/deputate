<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

include_once '../spezielle Funktionen/aktuellesSemester.php';

echo "Stage 2 of 3 <br>";

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$dbSaldo3 = $_SESSION['tables']['Saldo3'];
$dbBilanz = $_SESSION['tables']['Bilanz'];

// aktuelles Semester abfragen
$aktuellesSemester = getAktuellesSemester();
$Jahr = $aktuellesSemester['Jahr'];
$Semester = $aktuellesSemester['Semester'];

$aktuellesVorsemester = getAktuellesVorsemester();
$VJahr = $aktuellesVorsemester['VorsemesterJahr'];
$VSemester = $aktuellesVorsemester['VorsemesterSemester'];

$insertRows = explode(',', $dbSaldo3->get_rows());

$key1 = array_search("Jahr", $insertRows);
if ($key1 !== null) {
	unset($insertRows[$key1]);
}
$key2 = array_search("Semester", $insertRows);
if ($key2 !== null) {
	unset($insertRows[$key2]);
}

$query1 = "DELETE FROM {$dbSaldo3->get_table()}" .
	" WHERE Jahr = $Jahr AND Semester = '$Semester' " .
	" AND manuell = false";
$query2 = "INSERT INTO {$dbSaldo3->get_table()}" .
	// ' (' . $dbSaldo3->get_rows() . ') ' .
	" (SELECT $Jahr, '$Semester'," . implode(',', $insertRows) .
	" FROM {$dbSaldo3->get_table()} T" .
	" WHERE Jahr = $VJahr AND Semester =  '$VSemester' " .
	" AND DozKurz in" .
	" (SELECT DozKurz FROM Lehrverpflichtung" .
	" WHERE Jahr = $Jahr AND Semester =  '$Semester' AND Status = 'Prof' " .
	")" .
	" AND NOT EXISTS" .
	" ( SELECT * FROM {$dbSaldo3->get_table()} U" .
	" WHERE Jahr = $Jahr AND Semester =  '$Semester' " .
	" AND U.DozKurz = T.DozKurz" .
	" AND U.Abrechnungsjahr = T.Abrechnungsjahr" .
	" AND U.Abrechnungssemester = T.Abrechnungssemester" .
	")" .
	")";

$query3 = "INSERT INTO {$dbSaldo3->get_table()} ({$dbSaldo3->get_rows()}) " .
	" (SELECT $Jahr, '$Semester', `Kurz`, $Jahr, '$Semester', `Saldo`, `Status`, 0 " .
	" FROM {$dbBilanz->get_table()}" .
	" WHERE Status = 'Prof'" .
	")";

// echo "$query1 <br>";
// echo "$query2 <br>";
// echo "$query3 <br>";
// echo "<br>";

if (!$dbConnect->query($query1)) {
	die("Löschen misslungen!");
}
if (!$dbConnect->query($query2)) {
	die("Kopieren des Vorsemesters misslungen!");
}
if (!$dbConnect->query($query3)) {
	die("Kopieren der Bilanz misslungen!");
}
?>


<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="refresh" content="0; URL=SaldenBerechnen.php">		
        <title>Berechnung der Salden</title>
    </head>
    <body>
		<h1>Berechnung der Salden</h1>
		<p>
			für das <?= $aktuellesSemester['Text'] ?> läuft... <br>
			Bitte warten... <br>
		</p>
    </body>
</html>

