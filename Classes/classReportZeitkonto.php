<?php
include_once 'classReport.php';

/**
 * Description of classReportZeitkonto
 *
 * @author papa
 */
class ReportZeitkonto extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'Zeitkonto ausführlich.pdf') {

		parent::__construct($connection, $dbTable, $file);
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {
		$cap = 36.0;

		// aggregierte Daten abfragen
		$index = array("DozKurz", "Status");
		$columns = array("Name", "Vorname", "Anrede");

		$group1 = array_merge($index, $columns);
		$cols1 = $group1;
		$where1 = count($DozKurz) > 0? 
			array("$index[0] = '".implode("' OR $index[0] = '", $DozKurz)."'"): 
			array();
//		$where = count($DozKurz) > 0? array("$index[0] in ('".implode("','", $DozKurz)."')"): array();
		$order1 = array($columns[0]);

		$where1[] = "Zeitkonto = true";

		$groupArray = $this->query($cols1, $where1, $group1, $order1);
		
		if ($groupArray == false || $groupArray->num_rows == 0) {
			echo 'nothing to do...<br>';
			return FALSE;
		}
				
// Detaillierte Daten abfragen

		$cols2 = explode(',', $this->dbTable->get_rows());
#		$where2 = array('"Kurz" = \''.$DozKurz."'", '"Status" = \''.$Status."'");
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
	
			$head->addLine($this->document->createTextflow(date("d.m.Y"),
				$this->optionlist . " alignment=right"), 20);
			$head->addLine($this->document->createTextflow("{$attribut}er Stand des Arbeitszeitkontos für das {$this->aktuellesSemester['Text']}", 
				$this->optionlist . " font=$this->boldfont fontsize=12"), 20);
			$head->addLine($this->document->createTextflow("{$value['Anrede']} {$value['Name']},", 
				$this->optionlist), 10);
			$head->addLine($this->document->createTextflow("hiermit erhalten Sie den {$attribut}en Stand Ihres Zeitkontos für das zurückliegende Semester (alle Angaben in LVS).", 
				$this->optionlist), 10);
	
			$this->document->addLine($head, 5);
	
// Detail-Tabelle erstellen
			$body = $this->document->createBlock();
	
			$table = $this->document->createTable();
	
			$sum = 0;
			$tablerow = 1;
			$decimals = 2;

			$table->addCell(1, $tablerow, "Semester", $this->head_opts_center);
			$table->addCell(2, $tablerow, "Stunden", $this->head_opts_center);
			$table->addCell(3, $tablerow, "Pflicht", $this->head_opts_center);
			$table->addCell(4, $tablerow, "Bilanz", $this->head_opts_center);
			$table->addCell(5, $tablerow, "Summe", $this->head_opts_center);
			$table->addCell(6, $tablerow, "Kommentar", $this->head_opts_center);
			++$tablerow;
	
			foreach ($rowArray as $row) {
				$kurztext = array_key_exists('KurzText', $row)? $row['KurzText']: '';
				$stunden = $row['Stunden'];
				$pflicht = $row['Pflicht'];
				$saldo = $row['Saldo'];
				$sum += $saldo;
				if ($sum > $cap) {
					$sum = $cap;
				}
				$kommentar = array_key_exists('Kommentar', $row)? $row['Kommentar']: '';

				$table->addCell(1, $tablerow, $kurztext, $this->body_opts_left);
				$table->addCell(2, $tablerow, number_format($stunden, $decimals), $this->body_opts_right);
				$table->addCell(3, $tablerow, number_format($pflicht, $decimals), $this->body_opts_right);
				$table->addCell(4, $tablerow, number_format($saldo, $decimals), $this->body_opts_right);
				$table->addCell(5, $tablerow, number_format($sum, $decimals), $this->body_opts_right);
				$table->addCell(6, $tablerow, $kommentar, $this->body_opts_left);
				++$tablerow;
			}

			$body->addLine($table, 15);
			$body->addLine($this->document->createTextflow("Der {$attribut}e Stand Ihres Arbeitszeitkontos beträgt " . number_format($sum, $decimals) . " Stunden.", 
				$this->optionlist . " font=$this->boldfont"), 10);
			$body->addLine($this->document->createTextflow("Bitte beachten Sie, dass das Arbeitszeitkonto auf $cap Stunden begrenzt ist.", 
				$this->optionlist));
			$body->addLine($this->document->createTextflow("Darüber hinaus geleistete Stunden können nicht in das Arbeitszeitkonto übernommen werden.", 
				$this->optionlist), 10);

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

?>
