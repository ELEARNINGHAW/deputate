<?php
function getDozentFromUserID () {
	$userID = $_ENV['USER'];

	$dbTable = $_SESSION['tables']['Dozent'];

	$query = 'SELECT '.$dbTable->get_rows().' FROM '.$dbTable->get_table().' WHERE "Kennung" = $1 OR "Alias" = $1';
	$result = pg_query_params($query, array($userID));
	$cols = pg_fetch_array($result);

	return $cols;
}

?>
