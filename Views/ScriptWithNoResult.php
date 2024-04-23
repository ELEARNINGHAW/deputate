<?php
include_once '../Classes/classDBConnect.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$query_list = filter_input(INPUT_GET, 'querylist');
$scripts = $_SESSION['scripts'];

foreach ($scripts[$query_list] as $query) {
	
	error_log($query);
	
	$result = $dbConnect->query($query);
	if ($result) {
		echo "$query: <b>successful</b>. <br>";
	} else {
		echo "$query <b>not successful</b>. <br>";
		die('no result!');
	}
}


