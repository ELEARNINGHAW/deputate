<?php
include_once 'classReport.php';

/**
 * Description of classReportZeitkonto
 *
 * @author papa
 */
class ReportEntlastungen extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'Entlastungen EFZ.pdf') {

		parent::__construct($connection, $dbTable, $file);
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {

		// aggregierte Daten abfragen
		$index = array("Kurz", "Status");
		$columns = array("Name", "Anrede", "Text");
		$summeLVS = "Summe LVS";
		$anzahl = "Anzahl";

		$group1 = array_merge($index, $columns);
		$cols1 = array_merge($group1, array(
			"sum(LVS) as `$summeLVS`", 
			"count(*) as `$anzahl`"));
		$where1 = count($DozKurz) > 0? 
			array("$index[0] = '".implode("' OR $index[0] = '", $DozKurz)."'"): 
			array();
		$order1 = array($columns[0]);


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
			foreach ($index as $key) {
				$where2[] = "$key = '$value[$key]'";
			}
			
			$rowArray = $this->query($cols2, $where2, $group2, $order2);

// ggf. neue Seite beginnen
			if ($this->first == TRUE) {
				$this->first = FALSE;
			} else {
				$this->document->addLine($this->document->createPagebreak());
			}
					
// Gruppenkopf erstellen
			$head = $this->document->createBlock();
	
			$head->addLine($this->document->createTextflow(
					date("d.m.Y"),  $this->optionlist . " alignment=right"), 20);
			$head->addLine($this->document->createTextflow(
					"Feststellung der Lehrermäßigung für das {$value['Text']}", $this->optionlist . " font=$this->boldfont fontsize=12"), 20);
			$head->addLine($this->document->createTextflow(
					"{$value['Anrede']} {$value['Name']},", $this->optionlist), 10);
			$head->addLine($this->document->createTextflow(
					"Im {$value['Text']} erhielten Sie eine Lehrermäßigung zur Wahrnehmung folgender Funktionen bzw. Aufgaben:", $this->optionlist), 20);
	
			$this->document->addLine($head, 5);
	
// Detail-Tabelle erstellen
			$body = $this->document->createBlock();
			$table = $this->document->createTable(1, 1);
	
			$table->addCell(1, 1, "Funktion/Aufgabe", $this->head_opts_left);
			$table->addCell(2, 1, "LVS", $this->head_opts_center);

			foreach ($rowArray as $j => $row) {
				if ($row['Kommentar'] == '') {
					$table->addCell(1, $j + 2, "Forschungsentlastung", $this->body_opts_left);
				} else {
					$table->addCell(1, $j + 2, $row['Kommentar'], $this->body_opts_left);
				}
				$table->addCell(2, $j + 2, number_format($row['LVS'],2), $this->body_opts_right);
			}
			$table->addCell(1, $j + 3, "Summe der Lehrermäßigungen:", $this->foot_opts_left);
			$table->addCell(2, $j + 3, number_format($value[$summeLVS],2), $this->foot_opts_right);

			$body->addLine($table, 10);
			$this->document->addLine($body, 10);
	
	
// Gruppenfuß erstellen
			$fuss = $this->document->createBlock();

			$fuss->addLine($this->document->createTextflow("Für weitere Fragen stehe ich Ihnen gerne zur Verfügung.", $this->optionlist), 5);
			$fuss->addLine($this->document->createTextflow("Mit freundlichen Grüßen", $this->optionlist), 5);
			$fuss->addLine($this->document->createTextflow("Martin Holle, Prodekan LS", $this->optionlist), 10);

			$fuss->addLine($this->document->createImage('Unterschrift_MH001.png'));

			$this->document->addLine($fuss, 5);
			
		}
		return TRUE;
		
	}
}
