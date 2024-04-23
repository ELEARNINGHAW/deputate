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
$connection = $dbConnect->get_connection();

$report = $_GET['report'];
$action = $_GET['action'];
$docTable = $_GET['table'];
$dbTable = $_SESSION['tables'][$docTable];

$columns = $_GET['columns'] == NULL? array(): explode(',', $_GET['columns']);
$selection = $_GET['selection'] == NULL? array(): explode(',', $_GET['selection']);
$where = array();
$group = array();
$order = $_GET['order'] == NULL? array(): explode(',',$_GET['order']);

$query = "SELECT ". $dbTable->get_cols() . " FROM " . $dbTable->get_table();
if (count($selection) > 0) {
	if (count($columns) != count($selection)) {
		die (count($columns).' <> '.count($selection).' ...schade.');
	}
	foreach ($columns as $j => $column) {
		$where[] = "\"$column\" = '".rawurldecode($selection[$j])."'";
	}
}
if (count($where) > 0) {
	$query .= " WHERE " . implode(' AND ', $where);
}
if (count($group) > 0) {
	$query .= " GROUP BY ".implode(',', $group);
}
if (count($order) > 0) {
	$query .= " ORDER BY ".implode(',', $order);
}
$result = pg_query($connection, $query);

#echo "$query<br>$field_num<br>"; var_dump($array);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Druckparameter</title>
	</head>
	<body>
		<h1>Bitte Druck-Parameter auswählen:</h1>
		<form action="ReportSelect.php">
		<p>
			<input type="radio" name="attribut" value="vorläufig"> vorläufig<br>
			<input type="radio" name="attribut" value="aktuell"> aktuell<br>
			<input type="radio" name="attribut" value="korrigiert"> korrigiert
		</p>
		
		<p>
			<input type="checkbox" name="with_termin" value="true" checked> Rückgabetermin <br>
			<input type="date" name="termin" value="">
		</p>
		<input type="hidden"
		</form>		
	</body>
</html>

