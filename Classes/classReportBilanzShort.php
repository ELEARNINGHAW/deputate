<?php
include_once 'classReport.php';

/**
 * Description of classReportBilanz
 *
 * @author papa
 */
class ReportBilanzShort extends Report {

	public function __construct($connection, Table $dbTable, $file = 'Bilanzierung Übersicht.pdf') {
		parent::__construct($connection, $dbTable, $file);
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {
		
		// Aggregierte Daten abfragen
		$index = array("Kurz", "Status");
		$columns = array("Vorname", "Name", "Anrede", "Semester", "Pflicht", "Mailzustellung");
		$summeLVS = "Summe LVS";
		$anzahl = "Anzahl";
		$saldo = "Saldo";

		$group1 = array_merge($index, $columns);
		$cols1 = array_merge($group1, array(
			"sum(LVS) as `$summeLVS`",
			"sum(LVS) - Pflicht as `$saldo`",
			"count(*) as `$anzahl`"));
		$where1 = count($DozKurz) > 0? 
			array("$index[0] = '".implode("' OR $index[0] = '", $DozKurz)."'"): 
			array();
		$order1 = array($columns[1]);
		
		$groupArray = $this->query($cols1, $where1, $group1, $order1);
		
		if ($groupArray == false || $groupArray->num_rows == 0) {
			echo 'nothing to do...<br>';
			return FALSE;
		}
		
// Detaillierte Daten abfragen
		$cols2 = explode(',', $this->dbTable->get_rows());
		$group2 = array();
		$order2 = $this->dbTable->get_order();
		
		foreach ($groupArray as $value) {
			$where2 = array();
			foreach ($index as $col) {
				$where2[] = "`$col` = '$value[$col]' ";
			}
			$rowArray = $this->query($cols2, $where2, $group2, $order2);
			
			$part = $this->document->createBlock();

// Gruppenkopf erstellen
			$head = $this->document->createBlock();
			$textflow = $this->document->createTextflow("{$value['Name']}, ", $this->optionlist . " font=$this->boldfont");
			$textflow->addTextflow("{$value['Vorname']}, {$value['Status']}", $this->optionlist);
			$head->addLine($textflow, 10);
			$part->addLine($head);

// Detail-Tabelle erstellen
			$table = $this->document->createTable();
			$table->addCell(1, 1, ' ', $this->head_opts_left . " colwidth=10%");
			$table->addCell(2, 1, 'Titel der Veranstaltung / Grund der Entlastung', $this->head_opts_left . " colwidth=60%");
			$table->addCell(3, 1, 'Studiengruppe', $this->head_opts_center . " colwidth=10%");
			$table->addCell(4, 1, 'SWS', $this->head_opts_center . " colwidth=10%");
			$table->addCell(5, 1, 'LVS', $this->head_opts_right . " colwidth=10%");


			foreach ($rowArray as $j => $row) {
				if ($row['FachKurz'] != ' ') {
					$table->addCell(1, $j + 2, $row['FachKurz'], $this->body_opts_left . " colwidth=10%");
					$table->addCell(2, $j + 2, $row['Fach'], $this->body_opts_left . " colwidth=60%");
					$table->addCell(3, $j + 2, $row['Text'], $this->body_opts_center . " colwidth=10%");
					$table->addCell(4, $j + 2, number_format($row['SWS'],1), $this->body_opts_center . " colwidth=10%");
				} else {
					$table->addCell(2, $j + 2, $row['Grund'] . ' ' . $row['Fach'], $this->body_opts_left . " colwidth=60%");			
				}
				$table->addCell(5, $j + 2, number_format($row['LVS'],2), $this->body_opts_right . " colwidth=10%");
			}
			$table->addCell(2, $j + 3, "Summe der Lehrveranstaltungen und Entlastungen:", $this->foot_opts_left . " colspan=3");
			$table->addCell(5, $j + 3, number_format($value[$summeLVS],2), $this->foot_opts_right);
			$table->addCell(2, $j + 4, "Lehrverpflichtung:", $this->foot_opts_left . " colspan=3");
			$table->addCell(5, $j + 4, number_format($value['Pflicht'],2), $this->foot_opts_right);
			$table->addCell(2, $j + 5, "Saldo im {$value['Semester']} beträgt: ", $this->foot_opts_left . " colspan=3");
			$table->addCell(5, $j + 5, number_format($value[$saldo],2), $this->foot_opts_right);

			$part->addLine($table, 20);

		// Gruppenfuß erstellen

			
			$this->document->addLine($part, 5);

		}
		
		return TRUE;
		
	}

	protected function getHeader() {
		//$header = new HeaderWithSemester($document, $aktuellesSemester['Text']);
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
