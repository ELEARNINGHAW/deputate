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
		showTable($result);
	} else {
		echo "$query <b>not successful</b>. <br>";
		die('no result!');
	}
}


function showTable($result) {

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Table</title>
	</head>
	<body>
		<p><?= $result->num_rows; ?> Datensätze gefunden.</p>
		<table border="1" width="100%">
			<tr>
				<th> Nr. </th>
				<?php
				$types = array();
				foreach ($result->fetch_fields() as $field) {
					echo "<th> $field->name </th>";
					$types[$field->name] = $field->type;
				}
				?>
			</tr>
			<?php
			foreach($result as $j => $row) {
				?>
				<tr>
				<td><?=$j?></td>
				<?php
				foreach ($row as $name => $field) {
					switch($types[$name]) {
						case MYSQLI_TYPE_BIT:
							if ($field == 0) {
								echo ("<td> false </td>");
							} else if ($field == 1) {
								echo ("<td> true </td>");
							} else {
								echo ("<td> - $field - </td>");					
							}
							break;
						default:
							echo ("<td> $field </td>");
					}
				}
				?>
				</tr>
				<?php
			}
			?>
		</table>
	</body>
</html>	
<?php
}
