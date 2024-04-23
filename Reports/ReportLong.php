<?php

include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classEnvelope.php';
include_once '../Classes/classReportBilanz.php';
include_once '../Classes/classReportZeitkonto.php';
include_once '../Classes/classReportSaldo.php';
include_once '../Classes/classReportEntlastungen.php';
include_once '../Classes/classReportBilanzShort.php';
include_once '../Classes/classReportZeitkontoShort.php';
include_once '../Classes/classReportSaldoShort.php';
include_once '../Classes/classReportEntlastungenShort.php';
include_once '../Classes/classReportAbfrageLV.php';
include_once '../Classes/classReportAbfrageProjekte.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$report = filter_input(INPUT_GET, 'report');
$action = filter_input(INPUT_GET, 'action');
$attribute = $_SESSION['attribute'];
$deadline = $_SESSION['deadline'];

$docTable = filter_input(INPUT_GET, 'table');
$dbTable = $_SESSION['tables'][$docTable];

$columns = filter_has_var(INPUT_GET, 'columns')? explode(',', filter_input(INPUT_GET, 'columns')): array();
$selection = filter_has_var(INPUT_GET, 'selection')? explode(',', filter_input(INPUT_GET, 'selection')): array();
$where = array();
$group = array();
$order = filter_has_var(INPUT_GET, 'order')? explode(',',  filter_input(INPUT_GET, 'order')): array();

$query = "SELECT ". $dbTable->get_cols() . " FROM " . $dbTable->get_table();
if (count($selection) > 0) {
	if (count($columns) != count($selection)) {
		die (count($columns).' <> '.count($selection).' ...schade.');
	}
	foreach ($columns as $j => $column) {
		$where[] = "$column = '".rawurldecode($selection[$j])."'";
	}
}

// zum Wiederaufsetzen nach Abbruch:
//$where[] = "Kurz > 'Ldo'";

if (count($where) > 0) {
	$query .= " WHERE ".implode(' AND ', $where);
}
if (count($group) > 0) {
	$query .= " GROUP BY ".implode(',', $group);
}
if (count($order) > 0) {
	$query .= " ORDER BY ".implode(',', $order);
}
$result = $dbConnect->query($query);

error_log("ReportLong: $query; {$result->num_rows} rows");

ini_set('memory_limit', '2048M');
//ini_set('max_execution_time', '10');
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ReportLong</title>
    </head>
    <body>
		ReportLong... <br>
<?php
switch ($action) {
	case 'mail':
		?>
		<h1>Versand der Mails</h1>
		<p>
		Bitte warten... <br>
		</p>		
		<?php
		foreach($result as $value) {
			$dozent = $value['Kurz'];
			$status = $value['Status'];
			$adresse = $value['Mailadresse'];
			$anrede = $value['Anrede'] . ' ' . $value['Name'];
			$subject = "Stundenabrechnung";
			$envelope = new Envelope($dbConnect, $attribute, $deadline);	
			$envelope->addReport($report, array($dozent));
			$envelope->sendReports($adresse, $anrede, $subject);
		}
		break;
	case 'print':
		?>
		<h1>Drucken</h1>
		<p>
			Bitte warten...<br>
		</p>
		<?php
		$dozent = array();
		foreach($result as $value) {			
			$dozent[] = $value['Kurz'];
		}
		$envelope = new Envelope($dbConnect, $attribute, $deadline);	
		$envelope->addReport($report, $dozent);
		$envelope->printReports();
		break;
	default:
		?>
		<script type='text/javascript' language='javascript'>
		<!--
			alert('Falscher Parameter action: <?=$action?>')
		-->
		</script>
		<?php
		break;
}
//ini_set('memory_limit', '256MB');
//ini_set('max_execution_time', '30');
?>

	</body>
</html>
