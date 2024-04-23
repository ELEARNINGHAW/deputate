<?php
include_once 'classReport.php';

/**
 * Description of classReportBilanz
 *
 * @author papa
 */
class ReportBilanz extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'Bilanzierung ausführlich.pdf') {
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

// ggf. neue Seite beginnen
			if ($this->first == TRUE) {
				$this->first = FALSE;
			} else {
				$this->document->addLine($this->document->createPagebreak());
			}
					
// Gruppenkopf erstellen
			$head = $this->document->createBlock();

			$head->addLine($this->document->createTextflow(date("d.m.Y"),  $this->optionlist . " alignment=right"), 20);
			$head->addLine($this->document->createTextflow("{$attribut}e Stundenbilanz für das {$value['Semester']}", $this->optionlist . " font=$this->boldfont fontsize=12"), 20);
			$head->addLine($this->document->createTextflow("{$value['Anrede']} {$value['Name']},", $this->optionlist), 10);
			$head->addLine($this->document->createTextflow("hiermit erhalten Sie die {$attribut}e Stundenbilanz für das zurückliegende Semester (alle Angaben in LVS).", $this->optionlist), 10);

			$table1 = $this->document->createTable();

			$table1->addCell(1, 1, "Summe der Lehrveranstaltungen und Entlastungen:", $this->body_opts_left);
			$table1->addCell(2, 1, number_format($value[$summeLVS],2), $this->body_opts_right);
			$table1->addCell(1, 2, "Ihre Lehrverpflichtung:", $this->body_opts_left);
			$table1->addCell(2, 2, number_format($value['Pflicht'],2), $this->body_opts_right);
			$table1->addCell(1, 3, "Ihr Saldo im {$value['Semester']} beträgt: ",  $this->foot_opts_left);
			$table1->addCell(2, 3, number_format($value[$saldo],2), $this->foot_opts_right);

			$head->addLine($table1, 15);

			$this->document->addLine($head, 5);

			$this->document->addLine($this->document->createTextflow("Wir haben im Einzelnen für Sie folgende Leistungen notiert:", $this->optionlist), 10);

		// Detail-Tabelle erstellen
			$table2 = $this->document->createTable();

			$table2->addCell(1, 1, ' ', $this->head_opts_left . " colwidth=10%");
			$table2->addCell(2, 1, 'Titel der Veranstaltung / Grund der Entlastung', $this->head_opts_left . " colwidth=60%");
			$table2->addCell(3, 1, 'Studiengruppe', $this->head_opts_center . " colwidth=10%");
			$table2->addCell(4, 1, 'SWS', $this->head_opts_center . " colwidth=10%");
			$table2->addCell(5, 1, 'LVS', $this->head_opts_right . " colwidth=10%");

			foreach ($rowArray as $j => $row) {
				$text2 = '';
				if (strcmp($row['Grund'], ' ') != 0) {
					$text2 .= $row['Grund'] . ' ';
				}
				if (strcmp($row['Fach'], ' ') != 0) {
					$text2 .= $row['Fach'] . ' ';
				}

				$table2->addCell(1, $j + 2, $row['FachKurz'], $this->body_opts_left);
				$table2->addCell(2, $j + 2, $text2, $this->body_opts_left);
				$table2->addCell(3, $j + 2, $row['Text'], $this->body_opts_center);
				if($row['SWS'] != 0) {
					$table2->addCell(4, $j + 2, number_format($row['SWS'],1), $this->body_opts_center);
				}
				$table2->addCell(5, $j + 2, number_format($row['LVS'],2), $this->body_opts_right);
			}
			$table2->addCell(2, $j + 3, "Summe der Lehrveranstaltungen und Entlastungen:", $this->foot_opts_left . " colspan=3");
			$table2->addCell(5, $j + 3, number_format($value[$summeLVS],2), $this->foot_opts_right);

			$this->document->addLine($table2, 15);

		// Gruppenfuß erstellen
			$fuss = $this->document->createBlock();
			$fuss->addLine($this->document->createTextflow("Ihr Überstundenkonto der letzten Jahre oder Ihr Arbeitszeitkonto wird Ihnen gesondert zugestellt.", $this->optionlist), 5);
			$fuss->addLine($this->document->createTextflow("Für weitere Fragen stehe ich Ihnen gerne zur Verfügung.", $this->optionlist), 5);
			$fuss->addLine($this->document->createTextflow("Mit freundlichen Grüßen", $this->optionlist), 5);
			$fuss->addLine($this->document->createTextflow("Martin Holle, Prodekan LS", $this->optionlist), 10);

			$fuss->addLine($this->document->createImage('Unterschrift_MH001.png'));

			$this->document->addLine($fuss, 5);	
		}
		return TRUE;
		
	}
	
}
