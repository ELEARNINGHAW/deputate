<?php
include_once '../Classes/classReport.php';
include_once '../Classes/classReportBilanz.php';
include_once '../Classes/classReportZeitkonto.php';
include_once '../Classes/classReportSaldo.php';
include_once '../Classes/classReportAbfrageLV.php';
include_once '../Classes/classReportAbfrageProjekte.php';

session_start();

$id = filter_input(INPUT_GET, 'id');
$page = filter_input(INPUT_GET, 'page');

//var_dump($_GET);

$data = $_SESSION[$id][$page];

$buf = $data['data'];
$len =  strlen($buf);
$file = $data['file'];

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=$file;");
print $buf;

unset($_SESSION[$id]);
?>
