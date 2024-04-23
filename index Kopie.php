<?php
include_once 'Classes/classDBConf.php';
include_once 'Classes/classTable.php';
include_once 'Classes/classRelation.php';

include_once 'spezielle Abfragen/aktuellesSemester.php';
include_once 'spezielle Abfragen/DozentFromUser.php';;

session_start();

echo "Kein Treiber für Postgresql installiert!<br>";
exit;

$_SESSION['database'] = 'sawatzki.homedns.org';

$dbConf = new DBConf($_SESSION['database']);
$connection = $dbConf->get_connection();

$_SESSION['tables'] = array(
	'aktuellesSemester'
		=> new Table('aktuelles Semester', '"public"."Semester"', '"Jahr","Semester","Text","KurzText"', array('"aktuell" = \'true\'')),
	'Bilanzierung'
		=> new Table('Bilanzierungen', '"public"."Bilanzierung"', '*', array(), array('"Kurz"','"FachKurz"')),
	'Dozent'
		=> new Table('Dozenten', '"public"."Dozent"', '*', array(), array('"Name"'), array('Kurz'),
				array('Status' => array('Ami','LB','Prof'), 'Geschlecht' => array('m','w'), 'Professur' => array(' ','C','W','X'))),
	'Entlastungen'
		=> new Table('Entlastungen', '"public"."Entlastungen"', '*', array(), array('"Name"', '"Grund"')),
	'Saldierung3'
		=> new Table('Salden', '"public"."Saldierung3"', '*', array(), array('"DozKurz"', '"Status"', '"Jahr"', '"Semester"', '"Abrechnungsjahr"', '"Abrechnungssemester"')),
	'Semester'
		=> new Table('Semester', '"public"."Semester"', '*', array(), array('"Jahr" DESC', '"Semester" DESC'), array('Jahr', 'Semester')),
	);

$_SESSION['relations'] = array(	// Master => Detail
	);

$aktuellesSemester = getAktuellesSemester();
$semester = $aktuellesSemester['Jahr'].','.$aktuellesSemester['Semester'];

$Dozent = getDozentFromUserID();
$_SESSION['user'] = $Dozent['Kurz'];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head>
		<title></title>

		<script type='text/javascript' language='javascript'>
	<!--
		alert('<?= "angemeldeter User: {$_SESSION['user']}"?>')
	-->
	
	</script>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<frameset rows="70,*">
		<frame src="header.html" name="oben" />
		<frameset cols="250,*">
			<frame src="inhalt.php?Semester=<?= $semester?>" name="links"/>
			<frame src="Tables/TableDetail.php?table=Dozent&details=<?= $_SESSION['user']?>&action=update" name="rechts"/>
		</frameset>
	</frameset>
</html>
