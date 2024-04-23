<?php
include_once 'classReport.php';

/**
 * Description of classReportZeitkonto
 *
 * @author papa
 */
class ReportEntlastungenShort extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'Entlastungen EFZ.pdf') {

		parent::__construct($connection, $dbTable, $file);
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {

		// aggregierte Daten abfragen
		$index = array("Grund");
		$columns =  array("Auslastungsgrund");
		$dozent = "Kurz";
		$summeLVS = "Summe LVS";
		$anzahl = "Anzahl";
		
		$group1 = array_merge($index, $columns);
		$cols1 = array_merge($group1, array(
			"sum(LVS) as `$summeLVS`", 
			"count(*) as `$anzahl`"));
		$where1 = count($DozKurz) > 0? 
			array("$dozent = '".implode("' OR $dozent = '", $DozKurz)."'"): 
			array();
		$order = $index; //array("Grund");
		
		$where[] = "Grund in ('E', 'F', 'Z')";

		$groupArray = $this->query($cols, $where, $group, $order, TRUE);
		
		if ($groupArray == false || $groupArray->num_rows == 0) {
			echo 'nothing to do...<br>';
			return FALSE;
		}
				
// Detaillierte Daten abfragen

		$cols = explode(',', $this->dbTable->get_rows());
#		$where = array("Kurz = '$DozKurz'", "Status = '$Status'");
		$group = array();
		$order = $this->dbTable->get_order();
		
		foreach ($groupArray as $value) {
			$where = array("Grund = '{$value['Grund']}'");

			$rowArray = $this->query($cols, $where, $group, $order);

// Gruppenkopf erstellen
			$head = $this->document->createBlock();
			$head->addLine($this->document->createTextflow("Grund:" , $this->optionlist . " font=$this->boldfont"), 0);
			$head->addLine($this->document->createTextflow($value['Auslastungsgrund'], $this->optionlist . " font=$this->boldfont"), 20);
			$this->document->addLine($head, 5);

// Detail-Tabelle erstellen
			$table = $this->document->createTable(1,0);

			$table->addCell(1, 1, 'Entlastung', $this->head_opts_left . " colwidth=50%");
			$table->addCell(2, 1, 'Grund', $this->head_opts_left . " colwidth=10%");
			$table->addCell(3, 1, 'LVS', $this->head_opts_center . " colwidth=10%");
			$table->addCell(4, 1, 'Dozent', $this->head_opts_center . " colwidth=10%");
			foreach ($rowArray as $j => $row) {
				$table->addCell(1, $j + 2, $row['Kommentar'], $this->body_opts_left . " colwidth=50%");
				$table->addCell(2, $j + 2, $row['Grund'], $this->body_opts_left . " colwidth=10%");
				$table->addCell(3, $j + 2, $row['LVS'], $this->body_opts_right . " colwidth=10%");
				$table->addCell(4, $j + 2, $row['Name'], $this->body_opts_left . " colwidth=10%");
			}
			$j = $rowArray->num_rows + 1;
			$table->addCell(1, $j + 2, "Summe der Entlastungen:", $this->foot_opts_left . " colspan=2");
			$table->addCell(3, $j + 2, number_format($value[$summeLVS],2), $this->foot_opts_right);

			$this->document->addLine($table);
			

// Gruppenfuß erstellen


			$this->document->addLine($this->document->createPagebreak());
		}
		return TRUE;
		
	}

	protected function getHeader() {
		//$header = new HeaderWithSemester($this->document, $aktuellesSemester['Text']);
		$header = new PDF_Header($this->document);
		$header->finish();
		return $header;
	}

	protected function getFooter() {
		$footer = new FooterWithCounter($this->document, $this->aktuellesSemester['Text']);
		$footer->finish();
		return $footer;
	}
}
