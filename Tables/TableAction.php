<?php 
include_once '../Classes/classDBConf.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();

$dbConf = new DBConf($_SESSION['database']);
$connection = $dbConf->get_connection();

$action = $_GET['action'];
$table = $_GET['table'];
$details = $_GET['details'] == NULL? array(): explode(',', $_GET['details']);			// wird bei insert nicht benötigt
$selection = $_GET['selection'] == NULL? array(): explode(',', $_GET['selection']);		// wird durchgereicht
$relation = $_GET['relation'];															// wird durchgereicht

$dbTable = $_SESSION['tables'][$table];
$dbRelation = $_SESSION['relations'][$relation];

$fields = $_GET;
unset($fields['action']);
unset($fields['table']);
unset($fields['details']);
unset($fields['relation']);
unset($fields['selection']);

$keys = array_keys($fields);
switch ($action) {
	case 'update':
		foreach ($fields as $key => $value) {
			if ($value == '') {
				$fields[$key] = NULL;
			} else {
				$fields[$key] = rawurldecode($value);
			}
		}
		$assignments = array();
		foreach ($fields as $key => $value) {
			$key_decoded = rawurldecode($key);
			if ($value == NULL) {
				$assignments[] = "\"$key_decoded\"=NULL";
			} else {
				$assignments[] = "\"$key_decoded\"='$value'";
			}
		}

		$ID = $dbTable->get_ID();
		$conditions = array();
		for ($j = 0; $j < count($ID); $j++) {
			$conditions[] = "\"$ID[$j]\" = '$details[$j]'";
		}

		$query_update = "UPDATE ".$dbTable->get_table();
		$query_update .= " SET ".implode(', ', $assignments);
		$query_update .= " WHERE ".  implode(' AND ', $conditions);

		//echo "<p>$query_update</p>";
		$result = pg_query($query_update);
		break;
	case 'insert':
		foreach ($fields as $key => $value) {
			if ($value == '' || $value == NULL) {
				unset($fields[$key]);
			} else {
				$fields[$key] = rawurldecode($value);
			}
		}
		$keys = array_keys($fields);
		$values = array_values($fields);
		$query_insert = "INSERT INTO ".$dbTable->get_table();
		$query_insert .= ' ("'.implode('", "', $keys).'")'." VALUES ('".implode("','", $values)."')";
		//echo $query_insert."<br>";
		$result = pg_query($query_insert);
		break;
	case 'delete':
		$ID = $dbTable->get_ID();
		$conditions = array();
		for ($j = 0; $j < count($ID); $j++) {
			$conditions[] .= "\"$ID[$j]\" = '$details[$j]'";
		}
		$query_delete = "DELETE FROM ".$dbTable->get_table();
		$query_delete .= " WHERE ".implode(' AND ', $conditions);
		//echo $query_delete."<br>";
		$result = pg_query($query_delete);
		break;
	default:
		die('unbekannt Aktion'.$action.'<br>');
}
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>

	<script type='text/javascript' language='javascript'>
	<!--
		alert('<?= $result?
			"Datensatz erfolgreich geändert.":
			"Fehler beim Ändern des Datensatzes; Änderungen wurden nicht übernommen.";
		?>')
	-->
	</script>
	<meta http-equiv="refresh" content="0; URL=TableDetail.php?table=Dozent&details=<?= $_SESSION['user']?>&action=update">
    </body>
</html>
