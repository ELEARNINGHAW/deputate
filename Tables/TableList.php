<?php
include_once '../Classes/classDBConf.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();

$dbConf = new DBConf($_SESSION['database']);
$connection = $dbConf->get_connection();

$table = $_GET['table'];
$relation = $_GET['relation'];
$selection = $_GET['selection']==NULL? array(): explode(',',$_GET['selection']);
$columns = $_GET['columns']==NULL? array(): explode(',', $_GET['columns']);
//$details = $_GET['details']==NULL? array(): explode(',', $_GET['details']);

$aktuellesSemester = $_SESSION['aktuellesSemester'];

$dbTable = $_SESSION['tables'][$table];
$dbRelation = $_SESSION['relations'][$relation];
$ID = $dbTable->get_ID();
$where = $dbTable->get_where();
$order = $dbTable->get_order();

$query_list = "SELECT ".$dbTable->get_rows()." FROM ".$dbTable->get_table();
if ($relation) {
	$columns = $dbRelation->get_values();
}
if (count($columns) > 0) {
	if (count($columns) != count($selection)) {
		echo implode(', ', $columns) . " <> " . implode(', ', $selection) . "<br>";
		die (count($columns).' <> '.count($selection).' ...schade.');
	}
	for ($j = 0; $j < count($columns); $j++) {
		$where[] = '"'.$columns[$j].'" = '."'".rawurldecode($selection[$j])."'";
	}
}

if ($aktuellesSemester[0] != NULL) {
	$where[] = '"Jahr" = '."'".$aktuellesSemester[0]."'";
	$where[] = '"Semester" = '."'".$aktuellesSemester[1]."'";
}

if (count($where) > 0) {
	$query_list .= ' WHERE '.  implode(' AND ', $where);
}
if (count($order) > 0) {
	$query_list .= ' ORDER BY ' . implode(',', $order);
}

$result = pg_query($connection, $query_list);
$rowArray = pg_fetch_all($result);
$nID = array();
foreach ($ID as $key) {
	$nID[] = pg_field_num($result, '"'.$key.'"');
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
		<table><tr>
		<td><b> <?= $dbTable->get_header() ?>,</b> <?= pg_num_rows($result); ?> Datensätze gefunden.</td>
		<?php if($ID != NULL) { ?>
			<td>
			<form action="TableDetail.php" method="get">
				<input type="submit" name="insert" value="Datensatz einfügen">
				<input type="hidden" name="action" value="insert">
				<input type="hidden" name="table" value="<?=$table?>">
				<input type="hidden" name="relation" value="<?=$relation?>">
				<input type="hidden" name="selection" value="<?=$_GET['selection']?>">
				<input type="hidden" name="details" value="<?=$_GET['details']?>">
			</form></td>
		<?php } ?>
		<td>
		<form action="PrintTableList.php" method="get" target="_blank">
			<input type="submit" name="print" value="Tabelle drucken">
			<input type="hidden" name="table" value="<?=$table?>">
			<input type="hidden" name="relation" value="<?=$relation?>">
			<input type="hidden" name="selection" value="<?=$_GET['selection']?>">
		</form></td>
		</table>
		<table border>
			<tr>
				<th> Nr. </th>
				<?php
				if (count($nID) > 0) {
					echo "<th></th><th></th>";
				}
				for ($field_number = 0; $field_number < pg_num_fields($result); $field_number++) {
					echo '<th>'.pg_field_name($result, $field_number).'</th>';
				}
				?>
			</tr>
			<?php
			if (pg_num_rows($result) > 0) {
				foreach ($rowArray as $j => $row) {
					echo ("<tr>");
					echo ("<td>".$j."</td>");

					$values = array();
					if ($ID != NULL) {
						foreach ($ID as $key) {
							$values[] = $row[$key];
						}
					}
					if (count($values) > 0) {
						echo "<td><a href=TableDetail.php?table=$table&relation=$relation&selection=".rawurlencode($_GET['selection']).
							"&details=".rawurlencode(implode(',', $values))."&action=update>ändern</a></td>";
						echo "<td><a href=TableDetail.php?table=$table&relation=$relation&selection=".rawurlencode($_GET['selection']).
						"&details=".rawurlencode(implode(',', $values))."&action=delete>löschen</a></td>";
					}
					
					foreach ($row as $field) {
						echo ("<td> ".$field."</td>");
					}
					echo ("</tr>");
				}
			}
			?>
		</table>
    </body>
</html>

