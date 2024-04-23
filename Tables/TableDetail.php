<?php
include_once '../Classes/classDBConf.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();

$table = $_GET['table'];
$relation = $_GET['relation'];
$selection = $_GET['selection'] == NULL? array(): explode(',', rawurldecode($_GET['selection']));
$details = $_GET['details'] == NULL? array(): explode(',', $_GET['details']);
$action = $_GET['action'];

$dbConf = new DBConf($_SESSION['database']);
$connection = $dbConf->get_connection();

$dbTable = $_SESSION['tables'][$table];
$dbRelation = $_SESSION['relations'][$relation];

$aktuellesSemester = $_SESSION['aktuellesSemester'];

$nColumns = 1;

//	var_dump($selection);
//	var_dump($detail);

function showOptionlist (array $optionlist, $key, $selection = NULL) {
	echo "<td>$key</td><td><select name=$key>";
	foreach ($optionlist as $value) {
		if ($value == $selection) {
			echo "<option selected value=$value> $value";
		} else {
			echo "<option value=$value> $value";
		}
		echo "</option>";
	}
	echo "</select>";
	//echo $selection? "Auswahl: $selection":""." </td>";
	return;
}

function showUplinklist ($uplink, $row) {
	$keys = array_keys($uplink->get_relation());
	$values = array_values($uplink->get_relation());
	$uplinktable = $uplink->get_master()->get_table();
	$keysString = implode('","', $keys);

	$uplinkQuery = "SELECT \"$keysString\" FROM $uplinktable";
//					if ($aktuellesSemester[0] != NULL) {
//						$uplinkQuery .= ' WHERE "Jahr" = '."'".$aktuellesSemester[0]."' AND ".'"Semester" = '."'".$aktuellesSemester[1]."'";
//					}
	$uplinkQuery .= " ORDER BY \"$keysString\"";
	$uplinkResult = pg_query($uplinkQuery);
	$uplinkRows = pg_fetch_all($uplinkResult);
	$valuesString = implode(', ', $values);
	$valuesStringURL = rawurlencode($valuesString);
	$auswahl = $row[$values[0]];
//					echo var_dump($keys).'<br>';
//					echo var_dump($uplinkRows).'<br>';
	if (count($values) == 1) {		// TODO: mehrwertige Felder
		echo "<td>$valuesString</td><td><select name=$valuesStringURL>";
		foreach ($uplinkRows as $uplinkRow) {
			$optionString = implode(', ', $uplinkRow);
			$optionStringURL = rawurlencode($optionString);
			if($uplinkRow[$keys[0]] == $row[$values[0]]) { // TODO: mehrwertige Felder
				echo "<option selected value=$optionStringURL> $optionString";
			} else {
				echo "<option value=$optionStringURL> $optionString";
			}
			echo '</option>';
		}
		echo "</select>";
		echo $auswahl? "Auswahl: $auswahl":""." </td>";
	}
}

$allRelations = $_SESSION['relations'];
$uplinks = array();
foreach ($allRelations as $name => $uplink) {
	if ($uplink->get_detail() == $dbTable) {
//				echo $name.': '.var_dump($uplink->get_relation()).'<br>';
		$uplinks[] = $uplink;
	}
}


$ID = $dbTable->get_ID();
$query_detail = "SELECT ".$dbTable->get_rows()." FROM ".$dbTable->get_table();
if ($action == 'insert') {
	$query_detail .= " WHERE \"$ID[0]\" = NULL";
} else {
	$conditions = array();
	for ($j = 0; $j < count($details); $j++) {
		$conditions[] = '"'.$ID[$j].'"'." = '$details[$j]'";
	}
	if (count($conditions) > 0) {
		$query_detail .= ' WHERE '.  implode(' AND ', $conditions);
	}
	if ($dbTable->get_order()) {
		$query_detail .= ' ORDER BY ' . implode(',', $dbTable->get_order());
	}
}
// echo "$query_detail<br>";

$result = pg_query($query_detail);
$row = pg_fetch_assoc($result);
$nID = pg_field_num($result, '"$ID."');

switch ($action) {
	case 'insert':
		echo "<p>Bitte füllen Sie die folgenden Felder aus; die Daten werden eingefügt, wenn Sie auf OK klicken.</p>";
		if ($dbRelation) {
			$columns = $dbRelation->get_values();
			if (count($columns) != count($selection)) {
				echo implode(', ', $columns).' <> '.implode(', ', $selection).'<br>';
				die ('columns '.count($columns).' <> selection '.count($selection).' ...schade.');
			}
			for ($j = 0; $j < count($columns); $j++) {
				$row[$columns[$j]] = $selection[$j];
			}
		}

		$row['Jahr'] = $aktuellesSemester[0];
		$row['Semester'] = $aktuellesSemester[1];
		break;
	case 'update':
		echo "<p>Bitte geben Sie Ihre Änderungen in die folgenden Felder ein; die Änderungen werden durchgeführt, wenn Sie auf OK klicken.</p>";
		break;
	case 'delete':
		echo "<p>Der folgende Datensatz wird unwiderruflich gelöscht, sobald Sie auf OK Klicken.</p>";
		break;
	default:
		die("<p>Unbekannte Aktion $action für diesen Datensatz.</p>");
}

?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
		<h3><?= $dbTable->get_header() ?></h3>
		<form action="TableAction" method="get">
			<table>
				<?php
				$uplinkFields = array();
				foreach ($uplinks as $uplink) {
					$values = array_values($uplink->get_relation());
					if (count($values) == 1) {		// TODO: mehrwertige Felder
						foreach ($values as $value) {
							$uplinkFields[$value] = $uplink;
						}
					}
				}

				$optionlists = $dbTable->get_optionlist();
				echo '<tr>';
				for ($field_number = 0; $field_number < pg_num_fields($result); $field_number++) {
					$field_name = pg_field_name($result, $field_number);
					$field_name_URL = rawurlencode($field_name);		// sonst wird das Leerzeichen durch _ ersetzt.
					if (array_key_exists($field_name, $uplinkFields)) {
						showUplinklist($uplinkFields[$field_name], $row);
					} else if ($optionlists!=NULL && array_key_exists($field_name, $optionlists)) {
						showOptionlist($optionlists[$field_name], $field_name, $row[$field_name]);
					} else {
						echo "<td>$field_name:</td><td><input size=50 name='$field_name_URL' value='$row[$field_name]'></td>";
					}
					if ($field_number % $nColumns == $nColumns - 1) {
						echo '</tr> <tr>';
					}
				}
				echo '</tr>';
				?>
			</table>
			<input type="hidden" name="table" value="<?= $table?>">
			<input type="hidden" name="relation" value="<?= $relation?>">
			<input type="hidden" name="details" value="<?= $_GET['details']?>">
			<input type="hidden" name="selection" value="<?= rawurlencode($_GET['selection'])?>">
			<input type="hidden" name="action" value="<?= $action?>">
			<input type="submit" value="OK">
		</form>
    </body>
</html>
