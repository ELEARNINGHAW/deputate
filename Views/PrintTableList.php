<?php
include_once '../Classes/classPDF2.php';
include_once '../Classes/classPrintable.php';
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();
error_log("PrintTableList - Memory usage: " . number_format(memory_get_usage()) . "\n");

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$table = filter_input(INPUT_GET, 'table');
$relation = filter_input(INPUT_GET, 'relation');
$selection = filter_has_var(INPUT_GET, 'selection')? explode(',', rawurldecode(filter_input(INPUT_GET, 'selection'))): array();

$aktuellesSemester = $_SESSION['aktuellesSemester'];

$dbTable = $_SESSION['tables'][$table];
$dbRelation = $_SESSION['relations'][$relation];

// $output = "/Users/papa/temp/$table.pdf";

// Daten abfragen
$query_list = "SELECT {$dbTable->get_rows()} FROM {$dbTable->get_table()}";
if ($relation) {
	$columns = $dbRelation->get_values();
	if (count($columns) != count($selection)) {
		die (count($columns).' <> '.count($selection).' ...schade.');
	}

	$conditions = array();
	for ($j = 0; $j < count($columns); $j++) {
		$conditions[] = "$columns[$j] = '".rawurldecode($selection[$j])."'";
	}

	if ($aktuellesSemester[0] != NULL) {
		$conditions[] = "Jahr = $aktuellesSemester[0]";
		$conditions[] = "Semester = '$aktuellesSemester[1]'";
	}
	
	if (count($conditions) > 0) {
		$query_list .= " WHERE " . implode(' AND ', $conditions);
	}
}
if ($dbTable->get_order()) {
	$query_list .= " ORDER BY " . implode(',', $dbTable->get_order());
}

error_log($query_list);

$result = $dbConnect->query($query_list);

// Druck initialisieren
$pdf = new PDF();
$boldfont = $pdf->getBoldfont();
$regularfont = $pdf->getFont();

// Formatierungen
$fontsize = 10;
$margin = 1.5;

$head_opts_right = "fittextline={position={right center} font=$boldfont fontsize=$fontsize} margin=$margin";
$head_opts_left = "fittextline={position={left center} font=$boldfont fontsize=$fontsize} margin=$margin";
$head_opts_center = "fittextline={position={center center} font=$boldfont fontsize=$fontsize} margin=$margin";
$body_opts_left = "fittextline={position={left center} font=$regularfont fontsize=$fontsize} margin=$margin";
$body_opts_center = "fittextline={position={center center} font=$regularfont fontsize=$fontsize} margin=$margin";
$body_opts_right = "fittextline={position={right center} font=$regularfont fontsize=$fontsize} margin=$margin";

$document = $pdf->createDocument($dbTable->get_header(), 'Martin Holle', 'LehrePHP', $output);

$docTable = $document->createTable();

foreach ($result->fetch_fields() as $j => $field) {
	$docTable->addCell($j + 1, 1, $field->name, $head_opts_left);
}

foreach ($result as $j => $row) {
	$i = 1;
	foreach ($row as $key => $value) {
		$docTable->addCell($i++, $j + 2, $value, $body_opts_left);
	}
}

$document->addLine($docTable);

$footer = new FooterWithCounter($document, $aktuellesSemester['Text']);
$footer->finish();

$document->printAll(NULL, $footer);

$document->finish();
$pdf->printOut();

$pdf = NULL;
gc_collect_cycles();

