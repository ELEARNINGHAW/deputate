<?php

include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classEnvelope.php';
include_once '../Classes/classReportBilanz.php';
include_once '../Classes/classReportZeitkonto.php';
include_once '../Classes/classReportSaldo.php';
include_once '../Classes/classReportEntlastungen.php';
include_once '../Classes/classReportBilanzShort.php';
include_once '../Classes/classReportZeitkontoShort.php';
include_once '../Classes/classReportSaldoShort.php';
include_once '../Classes/classReportEntlastungenShort.php';
include_once '../Classes/classReportAbfrageLV.php';
include_once '../Classes/classReportAbfrageProjekte.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);
$connection = $dbConnect->get_connection();

$attribut = "aktuell"; //"vorläufig" "aktuell" "korrigiert"

$report = $_GET['report'];
$action = $_GET['action'];
$docTable = $_GET['table'];
$dbTable = $_SESSION['tables'][$docTable];

$columns = $_GET['columns'] == NULL? array(): explode(',', $_GET['columns']);
$selection = $_GET['selection'] == NULL? array(): explode(',', $_GET['selection']);
$where = array();
$group = array();
$order = $_GET['order'] == NULL? array(): explode(',',$_GET['order']);

$query = "SELECT ". $dbTable->get_cols() . " FROM " . $dbTable->get_table();
if (count($selection) > 0) {
	if (count($columns) != count($selection)) {
		die (count($columns).' <> '.count($selection).' ...schade.');
	}
	foreach ($columns as $j => $column) {
		$where[] = "\"$column\" = '".rawurldecode($selection[$j])."'";
	}
}
if (count($where) > 0) {
	$query .= " WHERE " . implode(' AND ', $where);
}
if (count($group) > 0) {
	$query .= " GROUP BY ".implode(',', $group);
}
if (count($order) > 0) {
	$query .= " ORDER BY ".implode(',', $order);
}
$result = pg_query($connection, $query);

#echo "$query<br>$field_num<br>"; var_dump($array);

switch ($action) {
	case 'mail':
		$array = pg_fetch_all($result);
		foreach ($array as $value) {
			$dozent = $value['Kurz'];
			$status = $value['Status'];
			$adresse = $value['Mailadresse'];
			$anrede = $value['Anrede'] . ' ' . $value['Name'];
			$envelope = new Envelope($connection);	

			switch ($report) {
				case 'AbfrageLV':
					$dbReport = $_SESSION['tables']['AbfrageLehrveranstaltungen'];
					$envelope->addReportAbfrageLV(array($dozent), $dbReport);
					$subject = 'Abfrage der Lehrveranstaltungen';
					break;
				case 'AbfrageProjekte':
					$dbReport = $_SESSION['tables']['AbfrageProjekte'];
					$envelope->addReportAbfrageProjekte(array($dozent), $dbReport);
					$subject = 'Abfrage der Studienprojekte';
					break;
				case 'AbfrageLVProjekte':
					$dbReport = $_SESSION['tables']['AbfrageLehrveranstaltungen'];
					$envelope->addReportAbfrageLV(array($dozent), $dbReport);
					$dbReport = $_SESSION['tables']['AbfrageProjekte'];
					$envelope->addReportAbfrageProjekte(array($dozent), $dbReport);
					$subject = 'Abfrage der Lehrveranstaltungen und der Studienprojekte';
					break;
				case 'Bilanz':
					$dbReport = $_SESSION['tables']['Bilanzierung'];
					$envelope->addReportBilanz(array($dozent), $dbReport);
					$subject = 'Bilanz';
					break;
				case 'Saldo':
					$dbReport = $_SESSION['tables']['Saldierung3'];
					$envelope->addReportSaldo(array($dozent), $dbReport);
					$subject = 'Saldo';
					break;
				case 'Zeitkonto':
					$dbReport = $_SESSION['tables']['Zeitkontosicht'];
					$envelope->addReportZeitkonto(array($dozent), $dbReport);
					$subject = 'Zeitkonto';
					break;
				case 'BilanzZeitkonto':
					$dbReport = $_SESSION['tables']['Bilanzierung'];
					$envelope->addReportBilanz(array($dozent), $dbReport);
					$dbReport = $_SESSION['tables']['Zeitkontosicht'];
					$envelope->addReportZeitkonto(array($dozent), $dbReport);
					$subject = 'Bilanz und Zeitkonto';
					break;
				case 'Entlastungen':
					$dbReport = $_SESSION['tables']['Entlastungen nach Dozent'];
					$envelope->addReportEntlastungen(array($dozent), $dbReport);
					$subject = 'Entlastungen';
					break;
				default:
					?>
					<script type='text/javascript' language='javascript'>
					<!--
						alert('Falscher Parameter report: <?=$report?>')
					-->
					</script>
					<?php
					break;

			}
			$envelope->sendReports($adresse, $anrede, $subject);
		}
		break;
	case 'print':
		$field_num = pg_field_num($result, '"Kurz"');
		$dozent = pg_fetch_all_columns($result, $field_num);
		$envelope = new Envelope($connection);	
		
		switch ($report) {
			case 'AbfrageLV':
				$dbReport = $_SESSION['tables']['AbfrageLehrveranstaltungen'];
				$envelope->addReportAbfrageLV($dozent, $dbReport);
				break;
			case 'AbfrageProjekte':
				$dbReport = $_SESSION['tables']['AbfrageProjekte'];
				$envelope->addReportAbfrageProjekte($dozent, $dbReport);
				break;
			case 'AbfrageLVProjekte':
				$dbReport = $_SESSION['tables']['AbfrageLehrveranstaltungen'];
				$envelope->addReportAbfrageLV($dozent, $dbReport);
				$dbReport = $_SESSION['tables']['AbfrageProjekte'];
				$envelope->addReportAbfrageProjekte($dozent, $dbReport);
				break;
			case 'Bilanz':
				$dbReport = $_SESSION['tables']['Bilanzierung'];
				$envelope->addReportBilanz($dozent, $dbReport);
				break;
			case 'Saldo':
				$dbReport = $_SESSION['tables']['Saldierung3'];
				$envelope->addReportSaldo($dozent, $dbReport);
				break;
			case 'Zeitkonto':
				$dbReport = $_SESSION['tables']['Zeitkontosicht'];
				$envelope->addReportZeitkonto($dozent, $dbReport);
				break;
			case 'Entlastungen':
				$dbReport = $_SESSION['tables']['Entlastungen nach Dozent'];
				$envelope->addReportEntlastungen($dozent, $dbReport);
				break;
			case 'BilanzShort':
				$dbReport = $_SESSION['tables']['Bilanzierung'];
				$envelope->addReportBilanzShort($dozent, $dbReport);
				break;
			case 'SaldoShort':
				$dbReport = $_SESSION['tables']['Saldierung3'];
				$envelope->addReportSaldoShort($dozent, $dbReport);
				break;
			case 'ZeitkontoShort':
				$dbReport = $_SESSION['tables']['Zeitkontosicht'];
				$envelope->addReportZeitkontoShort($dozent, $dbReport);
				break;
			case 'EntlastungenShort':
				$dbReport = $_SESSION['tables']['Entlastungen nach Text'];
				$envelope->addReportEntlastungenShort($dozent, $dbReport);
				break;
			default:
				?>
				<script type='text/javascript' language='javascript'>
				<!--
					alert('Falscher Parameter report: <?=$report?>')
				-->
				</script>
				<?php
				break;
		}
		$envelope->printReports();
		break;
	default:
		?>
		<script type='text/javascript' language='javascript'>
		<!--
			alert('Falscher Parameter action: <?=$action?>')
		-->
		</script>
		<?php
		break;
}

function  addReport() {
	
}


?>