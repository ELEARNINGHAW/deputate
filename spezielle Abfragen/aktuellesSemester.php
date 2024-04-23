<?php

function getAktuellesSemester() {
	$dbTable = $_SESSION['tables']['aktuellesSemester'];
	$conditions = implode(' AND ', $dbTable->get_where());
	$query = "select {$dbTable->get_rows()} from {$dbTable->get_table()} where $conditions";
	$result = pg_query($query);
//	echo "$query<br>";
	if (pg_num_rows($result) <> 1) {
		die ("nicht genau ein aktuelles Semester gefunden!");
	}
	return reset(pg_fetch_all($result));
}

?>
