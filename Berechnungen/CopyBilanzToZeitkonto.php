<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

include_once '../spezielle Funktionen/aktuellesSemester.php';

echo "Stage 1 of 3 <br>";

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$dbZKonto = $_SESSION['tables']['Zeitkonto_Table'];
$dbBilanz = $_SESSION['tables']['Bilanz'];

// aktuelles Semester abfragen
$aktuellesSemester = getAktuellesSemester();
$Jahr = $aktuellesSemester['Jahr'];
$Semester = $aktuellesSemester['Semester'];

$insertRows = explode(',', $dbZKonto->get_rows());
$key1 = array_search('"Jahr"', $insertRows);
if ($key1 !== null) {
	unset($insertRows[$key1]);
}
$key2 = array_search('"Semester"', $insertRows);
if ($key2 !== null) {
	unset($insertRows[$key2]);
}


$query1 = "DELETE FROM {$dbZKonto->get_table()}" .
	" WHERE Jahr = '$Jahr' AND Semester = '$Semester' " .
	" AND manuell = false";

$query3 = "INSERT INTO {$dbZKonto->get_table()}" .
//		" ({$dbZKonto->get_rows()})" .
	"(Jahr, Semester, DozKurz, Status, manuell, Stunden, Pflicht, Saldo)" .
	" (SELECT $Jahr, '$Semester', `Kurz`, `Status`, 0, `Summe LVS`, `Pflicht`, `Saldo`" .
	" FROM {$dbBilanz->get_table()}" .
	" WHERE Status = 'Prof')";

//echo "$query1 <br>";
//echo "$query3 <br>";
//echo "<br>";

if (!$dbConnect->query($query1)) {
	die("Löschen misslungen!");
}
if (!$dbConnect->query($query3)) {
	die("Kopieren der Bilanz misslungen!");
}
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="refresh" content="0; URL=CopyBilanzToSaldo.php">
        <title>Erstellung der Salden</title>
    </head>
    <body>
		<h1>Erstellung der Salden</h1>
		<p>
			für das <?= $aktuellesSemester['Text'] ?> läuft... <br>
			Bitte warten... <br>
		</p>
    </body>
</html>

