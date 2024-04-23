<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>

		<?php
		include_once '../Classes/classDBConnect.php';
		include_once '../Classes/classTable.php';
		include_once '../Classes/classRelation.php';

		include_once '../spezielle Funktionen/aktuellesSemester.php';

		session_start();

		echo "Stage 3 of 3 <br>";

		$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

		$dbTableCopy = $_SESSION['tables']['Bilanz'];
		$dbTableSelect = $_SESSION['tables']['Saldo3intertemporaer'];
		$dbTableUpdate = $_SESSION['tables']['Saldo3'];
		$index = "DozKurz";

// aktuelles Semester abfragen
		$aktuellesSemester = getAktuellesSemester();

//echo implode(',', $aktuellesSemester) . "<br>";
// Gruppen abfragen
		$query = "select distinct $index from {$dbTableSelect->get_table()} where Status = 'Prof' group by $index order by $index";
		echo "$query<br>";

		$result = $dbConnect->query($query);

		$query_copy = "SELECT Saldo" .
			" FROM {$dbTableCopy->get_table()}" .
			" WHERE Semester = '{$aktuellesSemester['Text']}'" .
			" AND Kurz = '%s' AND Status = 'Prof' ";
		echo "$query_copy <br>";

//Daten abfragen
		$query_select = "SELECT {$dbTableSelect->get_rows()} FROM {$dbTableSelect->get_table()} " .
			" WHERE DozKurz = '%s' " .
			" ORDER BY Abrechnungsjahr ASC, Abrechnungssemester ASC";
		echo "$query_select <br>";

		$query_update = "UPDATE {$dbTableUpdate->get_table()} SET Stunden = %d" .
			" WHERE DozKurz = '%s' AND Status = 'Prof' AND Jahr = {$aktuellesSemester['Jahr']} AND Semester = '{$aktuellesSemester['Semester']}' " .
			" AND Abrechnungsjahr = %d AND Abrechnungssemester = '%s' ";
		echo "$query_update <br>";

		foreach ($result as $row) { // Schleife über alle Dozenten
			$value = $row[$index];
			// Zuerst neuen Saldo kopieren...
			//echo sprintf($query_copy, $value). "<br>";
			$result_copy = $dbConnect->query(sprintf($query_copy, $value));

			if ($result_copy->num_rows > 1) {
				die("Error in DB-Query: " . sprintf($query_copy, $value));
			}
			$result1 = $result_copy->fetch_array(MYSQLI_ASSOC);

			//echo sprintf($query_update, $result1['Saldo'], $value, $aktuellesSemester['Jahr'], $aktuellesSemester['Semester']). "<br>";
			if(!$dbConnect->query(sprintf($query_update, $result1['Saldo'], $value, $aktuellesSemester['Jahr'], $aktuellesSemester['Semester']))) {
				die("Update failed: " . sprintf($query_update, $result1['Saldo'], $value, $aktuellesSemester['Jahr'], $aktuellesSemester['Semester']));
			}

			// dann intertemporalen Ausgleich berechnen
			//echo sprintf($query_select, $value). "<br>";
			if (!$result2 = $dbConnect->query(sprintf($query_select, $value))) {
				die("Error in DB-Query: " . sprintf($query_select, $value));
			}

			if ($result2->num_rows == 0) {
				continue; // der nächste bitte..
			}
			foreach ($result2 as $row) {  // die letzte Zeile sollte das aktuelle Semester sein...
				$aktBilanz = $row;
			}

			echo "aktBilanz: {$aktBilanz['Abrechnungsjahr']} / {$aktBilanz['Abrechnungssemester']} für $value: {$aktBilanz['Stunden']} <br>";

			if (!($aktBilanz['Abrechnungsjahr'] == $aktuellesSemester['Jahr'] && $aktBilanz['Abrechnungssemester'] == $aktuellesSemester['Semester'])) {
				continue; // der nächste bitte..
			}

			$aktStunden = $aktBilanz['Stunden'];
			$aktBilanz['Stunden'] = 0;
			$LVVOBilanzStunden = 0;
			$LVVOKey = NULL;

			if ($aktStunden > 0) {
				foreach ($rowArray as $key => $Bilanz) {
					if ($Bilanz['Abrechnungsjahr'] == '2006' && $Bilanz['Abrechnungssemester'] == 'S') { // alte LVVO ist als SS 2006 gespeichert
						$LVVOBilanzStunden = $Bilanz['Stunden'];
						$LVVOKey = $key;
					} else {
						if ($Bilanz['Stunden'] < 0) {
							$Stunden = $aktStunden + $Bilanz['Stunden'];
							if ($Stunden > 0) {
								$rowArray[$key]['Stunden'] = 0;
								$aktStunden = $Stunden;
							} else {
								$rowArray[$key]['Stunden'] = $Stunden;
								$aktStunden = 0;
								break;
							}
						}
					}
				}
				if ($aktStunden > 0) {
					if ($LVVOBilanzStunden < 0) {
						$Stunden = $aktStunden + $LVVOBilanzStunden;
						if ($Stunden > 0) {
							$LVVOBilanzStunden = 0;
							$aktStunden = $Stunden;
						} else {
							$LVVOBilanzStunden = $Stunden;
							$aktStunden = 0;
						}
						$rowArray[$LVVOKey]['Stunden'] = $LVVOBilanzStunden;
					}
				}
			} elseif ($aktStunden < 0) {
				foreach ($rowArray as $key => $Bilanz) {

					echo "Stunden: {$Bilanz['Stunden']} <br>";

					if ($Bilanz['Abrechnungsjahr'] == '2006' && $Bilanz['Abrechnungssemester'] == 'S') { // alte LVVO ist als SS 2006 gespeichert
						$LVVOBilanzStunden = $Bilanz['Stunden'];
						$LVVOKey = $key;
					} else {
						if ($Bilanz['Stunden'] > 0) {
							$Stunden = $aktStunden + $Bilanz['Stunden'];
							if ($Stunden < 0) {
								$rowArray[$key]['Stunden'] = 0;
								$aktStunden = $Stunden;
							} else {
								$rowArray[$key]['Stunden'] = $Stunden;
								$aktStunden = 0;
								break;
							}
						}
					}
				}
				if ($aktStunden < 0) {
					if ($LVVOBilanzStunden > 0) {
						$Stunden = $aktStunden + $LVVOBilanzStunden;
						if ($Stunden < 0) {
							$LVVOBilanzStunden = 0;
							$aktStunden = $Stunden;
						} else {
							$LVVOBilanzStunden = $Stunden;
							$aktStunden = 0;
						}
						$rowArray[$LVVOKey]['Stunden'] = $LVVOBilanzStunden;
					}
				}
			}
			$rowArray[end(array_keys($rowArray))]['Stunden'] = $aktStunden;

			foreach ($rowArray as $Bilanz) {
				// echo sprintf($query_update, $value, $Bilanz['Abrechnungsjahr'], $Bilanz['Abrechnungssemester'], $Bilanz['Stunden']);
				if(!$dbConnect->query(sprintf($query_update, $value, $Bilanz['Abrechnungsjahr'], $Bilanz['Abrechnungssemester'], $Bilanz['Stunden']))) {
					die("Update failed: " . sprintf($query_update, $value, $Bilanz['Abrechnungsjahr'], $Bilanz['Abrechnungssemester'], $Bilanz['Stunden']));
				}
			}
		}
		?>

		<script type='text/javascript'>
            <!--
			alert('Berechnung erfolgreich abgeschlossen.');
-->
		</script>

    </body>
</html>
