<?php
include_once 'Classes/classDBConnect.php';
include_once 'Classes/classTable.php';

session_start();

// $dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

/* kann weg:
if (array_key_exists('Semester', $_REQUEST)) {
	$_SESSION['aktuellesSemester'] = explode(',', $_REQUEST['Semester']);
} else {
	$_SESSION['aktuellesSemester'] = array();
}
*/

/* Übernahme der Parameter bei Aufruf des Formulars */
if (array_key_exists('attribute', $_POST)) {
	$_SESSION['attribute'] = $_POST['attribute'];
}

if (array_key_exists('deadline', $_POST)) {
	$_SESSION['deadline'] = $_POST['deadline'];
}

$self = filter_input(INPUT_SERVER, 'PHP_SELF');


?>
<html>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<body>
		<form action="inhalt.php" method="post">
			<input type="submit" value="<<- Daten">
		</form>
		<form action="<?= $self ?>" method="post">			
			Attribut: <?= $_SESSION['attribute'] ?><br>
			<input type="radio" name="attribute" value="vorläufig" <?if ($_SESSION['attribute'] === "vorläufig") {echo " checked";}?> >vorläufig<br>
			<input type="radio" name="attribute" value="aktuell" <?if ($_SESSION['attribute'] === "aktuell") {echo " checked";}?> >aktuell<br>
			<input type="radio" name="attribute" value="korrigiert" <?if ($_SESSION['attribute'] === "korrigiert") {echo " checked";}?> >korrigiert<br>
                        
			Termin: <input type="text" name="deadline" value="<?= $_SESSION['deadline'] ?>"><br>
			<input type="submit" value="übernehmen">
		</form>
		<table>
		<tr><td><b>Abfragen</b></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageLV&action=print&table=Dozent&columns=Status&selection=" 
				   target="_blank">Lehrveranstaltungen alle</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageLV&action=print&table=Dozent&columns=Status&selection=Prof" 
				   target="_blank">Lehrveranstaltungen Professoren</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageLV&action=print&table=Dozent&columns=Status&selection=LB" 
				   target="_blank">Lehrveranstaltungen Lehrbeauftragte</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageLV&action=print&table=Dozent&columns=Status&selection=Ami" 
				   target="_blank">Lehrveranstaltungen wiss. Mitarbeiter</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageProjekte&action=print&table=Dozent&columns=Status&selection=Prof" 
				   target="_blank">Studienprojekte</a></td></tr>
		<tr><td><b>Berichte</b></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Bilanz&action=print&table=Dozent" 
				   target="_blank">Bilanz ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=BilanzShort&action=print&table=Dozent" 
				   target="_blank">Bilanz Übersicht</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Saldo&action=print&table=Dozent" 
				   target="_blank">Saldo ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=SaldoShort&action=print&table=Dozent" 
				   target="_blank">Saldo Übersicht</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Zeitkonto&action=print&table=Dozent" 
				   target="_blank">Zeitkonto ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=ZeitkontoShort&action=print&table=Dozent" 
				   target="_blank">Zeitkonto Übersicht</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Entlastungen&action=print&table=Dozent" 
				   target="_blank">Entlastungen ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=EntlastungenShort&action=print&table=Dozent" 
				   target="_blank">Entlastungen Übersicht</a></td></tr>
		<tr><td><b>Mails</b></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageLV&action=mail&table=Dozent&columns=Mailzustellung&selection=1" 
				   target="_blank">Lehrveranstaltungen alle</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageProjekte&action=mail&table=Dozent&columns=Status,Mailzustellung&selection=Prof,1" 
				   target="_blank">Studienprojekte</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Bilanz&action=mail&table=Dozent&columns=Mailzustellung&selection=1" 
				   target="_blank">Bilanz ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Zeitkonto&action=mail&table=Dozent&columns=Mailzustellung,Zeitkonto&selection=1,1" 
				   target="_blank">Zeitkonto ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Saldo&action=mail&table=Dozent&columns=Mailzustellung,Zeitkonto&selection=1,0" 
				   target="_blank">Saldo ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Entlastungen&action=mail&table=Dozent&columns=Mailzustellung&selection=1" 
				   target="_blank">Entlastungen ausführlich</a></td></tr>
		<tr><td><b>Ausdrucke</b></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageLV&action=print&table=Dozent&columns=Mailzustellung&selection=0" 
				   target="_blank">Lehrveranstaltungen alle</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=AbfrageProjekte&action=print&table=Dozent&columns=Status,Mailzustellung&selection=Prof,0" 
				   target="_blank">Studienprojekte</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Bilanz&action=print&table=Dozent&columns=Mailzustellung&selection=0" 
				   target="_blank">Bilanz ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Zeitkonto&action=print&table=Dozent&columns=Mailzustellung,Zeitkonto&selection=0,1" 
				   target="_blank">Zeitkonto ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Saldo&action=print&table=Dozent&columns=Mailzustellung,Zeitkonto&selection=0,0" 
				   target="_blank">Saldo ausführlich</a></td></tr>
		<tr><td><a href="Reports/ReportLong.php?report=Entlastungen&action=print&table=Dozent&columns=Mailzustellung&selection=0" 
				   target="_blank">Entlastungen ausführlich</a></td></tr>
			
		</table>
	</body>
</html>
