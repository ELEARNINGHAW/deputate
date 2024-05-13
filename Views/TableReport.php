<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();
error_log("TableReport - Memory usage: " . number_format(memory_get_usage()) . "\n");

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$docTable = filter_input(INPUT_GET, 'table');	//$_GET['table'];
$relation = filter_input(INPUT_GET, 'relation');
$selection = filter_has_var(INPUT_GET, 'selection')? explode(',',filter_input(INPUT_GET, 'selection')): array();
$columns = filter_has_var(INPUT_GET, 'columns')? explode(',', filter_input(INPUT_GET, 'columns')): array();
$details = filter_has_var(INPUT_GET, 'details')? explode(',', filter_input(INPUT_GET, 'details')): array();

$aktuellesSemester = $_SESSION['aktuellesSemester'];

$dbTable = $_SESSION['tables'][$docTable];
$ID = $dbTable->get_ID();
$where = $dbTable->get_where();
$order = $dbTable->get_order();

$query_list = "SELECT ".$dbTable->get_rows()." FROM ".$dbTable->get_table();
if ($relation) {
	$dbRelation = $_SESSION['relations'][$relation];
	$columns = $dbRelation->get_values();

	// Notausgang: aktuelles Semester gibt es zur Zeit nur in Sekundärtabellen..... :-))
	if ($aktuellesSemester[0] != NULL) {
		$where[] = "Jahr = $aktuellesSemester[0]";
		$where[] = "Semester = '$aktuellesSemester[1]'";
}


}
if (count($columns) > 0) {
	if (count($columns) != count($selection)) {
		echo implode(', ', $columns) . " <> " . implode(', ', $selection) . "<br>";
		die (count($columns).' <> '.count($selection).' ...schade.');
	}
	for ($j = 0; $j < count($columns); $j++) {
		$where[] = "$columns[$j] = ". rawurldecode($selection[$j]);
	}
}

if (count($where) > 0) {
	$query_list .= " WHERE ".implode(' AND ', $where);
}
if (count($order) > 0) {
	$query_list .= " ORDER BY ".implode(',', $order);
}

$result = $dbConnect->query($query_list);
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> title </title>
    </head>
    <body>
		<table><tr>
		<td><b> <?= $dbTable->get_header() ?>,</b> <?= $result->num_rows; ?> Datensätze gefunden.</td>
		</table>
		
		<a id='top'></a>
		<a href="#A">A</a>
		<a href="#B">B</a>
		<a href="#C">C</a>
		<a href="#D">D</a>
		<a href="#E">E</a>
		<a href="#F">F</a>
		<a href="#G">G</a>
		<a href="#H">H</a>
		<a href="#I">I</a>
		<a href="#J">J</a>
		<a href="#K">K</a>
		<a href="#L">L</a>
		<a href="#M">M</a>
		<a href="#N">N</a>
		<a href="#O">O</a>
		<a href="#P">P</a>
		<a href="#Q">Q</a>
		<a href="#R">R</a>
		<a href="#S">S</a>
		<a href="#T">T</a>
		<a href="#U">U</a>
		<a href="#V">V</a>
		<a href="#W">W</a>
		<a href="#X">X</a>
		<a href="#Y">Y</a>
		<a href="#Z">Z</a>

		<table border="1" width="2000">
			<tr>
				<th> </th> <th> Nr. </th>
				<?php
				if (count($ID) > 0) {
					echo "<th></th><th></th><th></th>";
				}
				foreach ($result->fetch_fields() as $col) {
					echo "<th> $col->name </th>";					
				}
				?>
			</tr>
			<?php
			if ($result->num_rows > 0) {
				foreach($result as $j => $row) {
					$values = array();
					if ($ID != NULL) {
						foreach ($ID as $key) {
							$values[] = $row[$key];
						}
						$anchor = substr($values[0], 0, 1);
					}
					?>
					<tr>
					<td><a href='#top'>🔝</a> <a id='<?=$anchor?>'></a></td>
					<td><?=$j?></td>
					<?php
					if (count($values) > 0) {

                        $selection = filter_input(INPUT_GET, 'selection');    if ( $selection == null) { $selection=''; }



						echo "<td><a href=../Reports/ReportFrame.php?report=AbfrageLVProjekte&table=$docTable&relation=$relation".
							"&selection=".$selection.
							"&detail=".rawurlencode(implode(',', $values))." target=_blank>Abfragen</a></td>";
						echo "<td><a href=../Reports/ReportFrame.php?report=Bilanz&table=$docTable&relation=$relation".
                            "&selection=".$selection.
							"&detail=".rawurlencode(implode(',', $values))." target=_blank>Bilanz</a></td>";
						echo "<td><a href=../Reports/ReportFrame.php?report=Zeitkonto&table=$docTable&relation=$relation".
                            "&selection=".$selection.
							"&detail=".rawurlencode(implode(',', $values))." target=_blank>Zeitkonto</a></td>";
					}
					
					foreach ($row as $field) {
						echo ("<td> $field </td>");
					}
					?>
					</tr>
					<?php
				}
			}
			?>
		</table>
    </body>
</html>

