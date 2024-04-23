<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$Kurz = 'Kurz';
$detail = filter_has_var(INPUT_GET, 'detail')? explode(',', filter_input(INPUT_GET, 'detail')): array();
$docTable = filter_input(INPUT_GET, 'table');

$dbTable = $_SESSION['tables'][$docTable];

$query = "SELECT * FROM {$dbTable->get_table()} where $Kurz = '$detail[0]'";

$result = $dbConnect->query($query);
$dozent = $result->fetch_array(MYSQLI_ASSOC);

#var_dump($dozent);

assert($dozent != NULL, "Kein Dozent gefunden!");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
		Dozent: <?=$dozent['Name']?>, <?=$dozent['Vorname']?><br>
		Report: <?=filter_input(INPUT_GET, 'report')?><br>
		
		<form action="ReportLong.php" method="get" target="_blank"> 
			Zeitkonto: <input type="checkbox" name="zeitkonto" checked="<?=$dozent['Zeitkonto'] == 1?'checked':''?>"><br>
			Zustellung: <input type="checkbox" name="Mailzustellung" checked="<?=$dozent['Mailzustellung'] == 1?'checked':''?>"><br>
			Mailadresse: <input type="text" name="adresse" size="40" value="<?=$dozent['Mailadresse']?>"><br>
			Anrede: <input type="text" name="anrede" size="40" value="<?=$dozent['Anrede'].' '.$dozent['Name']?>"><br>
			<input type="hidden" name="report" value="<?=filter_input(INPUT_GET, 'report')?>"><br>
			<input type="hidden" name="table" value="<?=filter_input(INPUT_GET, 'table')?>"><br>
			<input type="hidden" name="columns" value="<?=$Kurz?>"><br>
			<input type="hidden" name="selection" value="<?=filter_input(INPUT_GET, 'detail')?>"><br>
			<input type="submit" name="action" value="mail">
		</form>
    </body>
</html>

