<?php

/**
 * Description of classEnvelope
 *
 * @author papa
 */

include_once '../Classes/classReportBilanz.php';
include_once '../Classes/classReportZeitkonto.php';
include_once '../Classes/classReportSaldo.php';
include_once '../Classes/classReportAbfrageLV.php';
include_once '../Classes/classReportAbfrageProjekte.php';
include_once '../spezielle Funktionen/aktuellesSemester.php';
	
class Envelope {
	
	private $connection;
	private $reports = array();
	private $aktuellesSemester;
	private $attribute; //"vorläufig" "aktuell" "korrigiert"
	private $deadline;
	private $sender;
	
	function __construct(DBConnect $connection, $attribute = "aktuell", $deadline = "") {
		$this->connection = $connection;
		$this->attribute = $attribute;
		$this->aktuellesSemester = getAktuellesSemester();
		$this->deadline = $deadline;
//		$this->sender = "Rainer.Sawatzki@haw-hamburg.de";
		$this->sender = "Martin.Holle@haw-hamburg.de";
	}
	
	function  addReport($report, $dozent) {
		switch ($report) {
			case 'AbfrageLV':
				$dbReport = $_SESSION['tables']['AbfrageLehrveranstaltungen'];
				$this->addReportAbfrageLV($dozent, $dbReport);
				break;
			case 'AbfrageProjekte':
				$dbReport = $_SESSION['tables']['AbfrageProjekte'];
				$this->addReportAbfrageProjekte($dozent, $dbReport);
				break;
			case 'AbfrageLVProjekte':
				$dbReport = $_SESSION['tables']['AbfrageLehrveranstaltungen'];
				$this->addReportAbfrageLV($dozent, $dbReport);
				$dbReport = $_SESSION['tables']['AbfrageProjekte'];
				$this->addReportAbfrageProjekte($dozent, $dbReport);
				break;
			case 'Bilanz':
				$dbReport = $_SESSION['tables']['Bilanzierung'];
				$this->addReportBilanz($dozent, $dbReport);
				break;
			case 'Saldo':
				$dbReport = $_SESSION['tables']['Saldierung3'];
				$this->addReportSaldo($dozent, $dbReport);
				break;
			case 'Zeitkonto':
				$dbReport = $_SESSION['tables']['Zeitkontosicht'];
				$this->addReportZeitkonto($dozent, $dbReport);
				break;
			case 'Entlastungen':
				$dbReport = $_SESSION['tables']['Entlastungen nach Dozent'];
				$this->addReportEntlastungen($dozent, $dbReport);
				break;
			case 'BilanzShort':
				$dbReport = $_SESSION['tables']['Bilanzierung'];
				$this->addReportBilanzShort($dozent, $dbReport);
				break;
			case 'SaldoShort':
				$dbReport = $_SESSION['tables']['Saldierung3'];
				$this->addReportSaldoShort($dozent, $dbReport);
				break;
			case 'ZeitkontoShort':
				$dbReport = $_SESSION['tables']['Zeitkontosicht'];
				$this->addReportZeitkontoShort($dozent, $dbReport);
				break;
			case 'EntlastungenShort':
				$dbReport = $_SESSION['tables']['Entlastungen nach Text'];
				$this->addReportEntlastungenShort($dozent, $dbReport);
				break;
			default:
				?>
				<script type='text/javascript' language='javascript'>
				<!--
					alert('Falscher Parameter report: <?=$report?>');
				-->
				</script>
				<?php
				return FALSE;
		}
		return TRUE;
	}
	
	function addReportBilanz(array $detail, Table $dbTable) {
		$report = new ReportBilanz($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array (
			'Report' => $report, 
			'Anlage' => "den {$this->attribute}en Stand Ihrer Stundenbilanz für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportBilanzShort(array $detail, Table $dbTable) {
		$report = new ReportBilanzShort($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array (
			'Report' => $report, 
			'Anlage' => "die Übersicht über die {$this->attribute}en Stundenbilanzen für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportZeitkonto(array $detail, Table $dbTable) {
		$report = new ReportZeitkonto($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array (
			'Report' => $report,
			'Anlage' => "den {$this->attribute}en Stand Ihres Zeitkontos für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportZeitkontoShort(array $detail, Table $dbTable) {
		$report = new ReportZeitkontoShort($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array (
			'Report' => $report,
			'Anlage' => "die Übersicht über die {$this->attribute}en Zeitkonten für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportSaldo(array $detail, Table $dbTable) {
		$report = new ReportSaldo($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array(
			'Report' => $report,
			'Anlage' => "den {$this->attribute}en Stand Ihres Stundensaldos für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}
	
	function addReportSaldoShort(array $detail, Table $dbTable) {
		$report = new ReportSaldoShort($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array(
			'Report' => $report,
			'Anlage' => "die Übersicht über die {$this->attribute}en Stundensalden für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}
	
	function addReportAbfrageLV(array $detail, Table $dbTable) {
		$report = new ReportAbfrageLV($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array(
			'Report' => $report,
			'Anlage' => "die Abfrage Ihrer Lehrveranstaltungen für das {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportAbfrageProjekte(array $detail, Table $dbTable) {
		$report = new ReportAbfrageProjekte($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array(
			'Report' => $report,
			'Anlage' => "die Abfrage der von Ihnen betreuten Studienprojekte im {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportEntlastungen(array $detail, Table $dbTable) {
		$report = new ReportEntlastungen($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array(
			'Report' => $report,
			'Anlage' => "Ihre Entlastungsstunden im {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}

	function addReportEntlastungenShort(array $detail, Table $dbTable) {
		$report = new ReportEntlastungenShort($this->connection, $dbTable);
		if ($report->add($detail, $this->attribute, $this->deadline) == FALSE) {
			return FALSE;
		}
		$report->close();
		$this->reports[] = array(
			'Report' => $report,
			'Anlage' => "die Entlastungsstunden im {$this->aktuellesSemester['Text']}"
		);
		return TRUE;
	}
	


	function sendReports($adresse, $anrede, $subject) {
		if (count($this->reports) > 0) {
			$anlagen = array();
			foreach ($this->reports as $report) {
				$anlagen[] = $report['Anlage'];
			}
			$text =  
				"{$anrede}, \n\n" .
				"in der Anlage finden Sie " . implode(' und ', $anlagen) . ".\n" .
				"Viele Grüße \n" .
				"\n" .
				"Martin Holle \n".
				"Prodekan LS";
			$this->mail($adresse, $text, $subject);
		}
		return;
	}
	
	function printReports() {
		foreach ($this->reports as $report) {
			$data[] = $report['Report']->getDataWithFile();
		}
		$id = uniqid();
		$_SESSION[$id] = $data;
		error_log($id);
		
		assert($_SESSION[$id]);
		
		echo '<html>';
		echo '<meta http-equiv="refresh" content="10;URL=ReportEnvelope.php?id='. $id .'&page=0"/>';
		echo '</html>';
		
		return $id;
	}
	
	private function mail ($to, $text, $subject) {
				
		$data = array();
		foreach ($this->reports as $report) {
			$data[] = $report['Report']->getDataEncodedWithFile();
		}

		$semi_rand = md5(time()); 
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x=="; 		
		
#		$to = "papa@miraculix.wittreem";
#		$to = "rainer@sawatzki.eu";
#		$to = "rainer.sawatzki@haw-hamburg.de";
		
		$headers = array(
			"MIME-Version: 1.0", 
			"Content-Type: multipart/mixed; boundary=\"{$mime_boundary}",
			"From: {$this->sender}",
			"X-Mailer: PHP/" . phpversion()
		);

		$message = "This is a multi-part message in MIME format.\n" . 
			"--{$mime_boundary}\n" . 
			"Content-Type:text/plain; charset=utf-8 \n" . 
			"Content-Transfer-Encoding: 8bit \n\n" . 
			"{$text}\n" .
			"\n";
		foreach($data as $appendix) {
			$message .=
			"--{$mime_boundary}\n" . 
			"Content-Type:application/pdf; name=\"{$appendix['file']}\"\n" . 
			"Content-Disposition: attachment; filename=\"{$appendix['file']}\"\n" . 
			"Content-Transfer-Encoding: base64\n\n" . 
			"{$appendix['data']}\n\n";
		}
		$message .=	"--{$mime_boundary}--\n"; 
		
		if (mail($to, $subject, $message, implode("\r\n",$headers))){
			echo "Mail sent to: $to <br>";
		}
	}
	
}
