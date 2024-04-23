<?php

/**
 * Description of classReportSaldi
 *
 * @author papa
 */
class ReportSaldo extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'Saldo ausführlich.pdf') {

		parent::__construct($connection, $dbTable, $file);
		$this->first = TRUE;
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {

// aggregierte Daten abfragen
		$index = array("DozKurz", "Status");
		$columns = array("Name", "Vorname", "Anrede");

		$group1 = array_merge($index, $columns);
		$cols1 = $group1;
		$where1 = count($DozKurz) > 0? 
			array("$index[0] = '".implode("' OR $index[0] = '", $DozKurz)."'"): 
			array();
//		$where1 = count($DozKurz) > 0? array("$index[0] in (".implode(",", $DozKurz).")"): array();
		$order1 = array($columns[0]);

		$where1[] = "Zeitkonto = 0";
		
		$groupArray = $this->query($cols1, $where1, $group1, $order1);
		
		if ($groupArray == false || $groupArray->num_rows == 0) {
			echo 'nothing to do...<br>';
			return FALSE;
		}
				
// Detaillierte Daten abfragen

		$cols2 = explode(',', $this->dbTable->get_rows());
#		$where2 = array('"Kurz" = \''.$DozKurz."'", '"Status" = \''.$Status."'");
		$whereOld = array("Jahr = {$this->aktuellesSemester['VorsemesterJahr']}", 
				"Semester = '{$this->aktuellesSemester['VorsemesterSemester']}'",
				"intertemporaer = true");
		$whereNew = array("Jahr = {$this->aktuellesSemester['Jahr']}", 
				"Semester = '{$this->aktuellesSemester['Semester']}'",
				"intertemporaer = true");
		$group2 = array();
		$order2 = $this->dbTable->get_order();
		
		$headercols = array("KurzText", "Abrechnungsjahr", "Abrechnungssemester");
		$headerwhere = array("intertemporaer = true");
		$headergroup = array();
		$headerorder = array("Abrechnungsjahr ASC", "Abrechnungssemester ASC");
		$headerArray = $this->query($headercols, $headerwhere, $headergroup, $headerorder, TRUE);
		if (!$headerArray) {
			die ("Error in DB-Query");
		}
		
		foreach ($groupArray as $value) {
			$where = array();
			foreach ($index as $col) {
				$where[] = "`$col` = '$value[$col]' ";
			}
			
			$rowArrayOld = $this->query($cols2, array_merge($where,$whereOld), $group2, $order2);
			$rowArrayNew = $this->query($cols2, array_merge($where,$whereNew), $group2, $order2);
			
// ggf. neue Seite beginnen
			if ($this->first == TRUE) {
				$this->first = FALSE;
			} else {
				$this->document->addLine($this->document->createPagebreak());
			}
					
// Gruppenkopf erstellen
			$head = $this->document->createBlock();

			$head->addLine($this->document->createTextflow(date("d.m.Y"),$this->optionlist . " alignment=right"), 10);
			$head->addLine($this->document->createTextflow("{$attribut}e Abrechnung des intertemporalen Ausgleichs", $this->optionlist . " font=$this->boldfont fontsize=12"), 5);
			$head->addLine($this->document->createTextflow("für das {$this->aktuellesSemester['Text']}", $this->optionlist . " font=$this->boldfont fontsize=12"), 20);
			$head->addLine($this->document->createTextflow("{$value['Anrede']} {$value['Name']},", $this->optionlist), 10);
			$head->addLine($this->document->createTextflow("Sie erhalten heute die {$attribut}e Abrechnung des intertemporalen Ausgleichs für das {$this->aktuellesSemester['Text']}.", $this->optionlist),5);
			$head->addLine($this->document->createTextflow("Der folgenden Tabelle entnehmen Sie bitte die nach der alten LVVO angesammelten Überstunden sowie die Überstunden " .
					"oder Minusstunden jedes einzelnen Semesters nach der neuen LVVO, soweit sie noch nicht ausgeglichen sind.", $this->optionlist),5);
			$head->addLine($this->document->createTextflow("Zum Vergleich sind die Bilanzen des letzten Semesters und des aktuellen Semesters aufgeführt.", $this->optionlist), 10);

			$this->document->addLine($head, 5);
	
// Detail-Tabelle erstellen
			$table = $this->document->createTable();

			$tablerow = 1;
			$colwidth = 90 / ($headerArray->num_rows + 1);
			$sumcol = $headerArray->num_rows + 2;
			$decimals = 2;

// Spaltenköpfe
			foreach ($headerArray as $j => $header) {
				$table->addCell($j + 2, $tablerow, $header, $this->head_opts_center . " colwidth=$colwidth%");
			}
			$table->addCell($sumcol, $tablerow, 'Summe', $this->head_opts_center . " colwidth=$colwidth%");
						
			$summeold = 0;
			if ($rowArrayOld) {
				++$tablerow;
				$table->addCell(1, $tablerow, $rowArrayOld[0]['Text'], $this->body_opts_left);
				foreach ($rowArrayOld as $row) {
					$colnum = array_search($row['KurzText'], $headerArray);
					$table->addCell($colnum + 2, $tablerow, number_format($row['Stunden'],$decimals), $this->body_opts_center);
					$summeold += $row['Stunden'];
				}
				$table->addCell($sumcol, $tablerow, number_format($summeold,$decimals), $this->body_opts_center);
			}

			$diffrow = NULL;
			if ($rowArrayOld && $rowArrayNew) {
				$diffrow = ++$tablerow;
				$table->addCell(1, $tablerow, "Änderungen", $this->body_opts_left);
				foreach ($rowArrayOld as $rowOld) {
					$colnum = array_search($rowOld['KurzText'], $headerArray);
					foreach ($rowArrayNew as $rowNew) {
						if ($rowNew['KurzText'] == $rowOld['KurzText']) {
							$table->addCell($colnum + 2, $diffrow, number_format($rowNew['Stunden'] - $rowOld['Stunden'],$decimals), $this->body_opts_center);
						}
					}
				}
			}

			$summenew = 0;
			if ($rowArrayNew) {
				++$tablerow;
				$table->addCell(1, $tablerow, $rowArrayNew[0]['Text'], $this->body_opts_left);
				foreach ($rowArrayNew as $row) {
					$colnum = array_search($row['KurzText'], $headerArray);
					$table->addCell($colnum + 2, $tablerow, number_format($row['Stunden'],$decimals), $this->body_opts_center);
					$summenew += $row['Stunden'];
				}
				$table->addCell($sumcol, $tablerow, number_format($summenew,$decimals), $this->body_opts_center);
			}

			if ($diffrow) {
				$table->addCell($sumcol, $diffrow, number_format($summenew - $summeold,$decimals), $this->body_opts_center);
			}

			$this->document->addLine($table, 10);

// Gruppenfuß erstellen
			$foot = $this->document->createBlock();
			$foot->addLine($this->document->createTextflow("Bitte beachten Sie, dass Überstunden nach sechs Semestern verfallen, soweit sie nicht ausgeglichen werden.", $this->optionlist), 5);
			$foot->addLine($this->document->createTextflow("Minusstunden verfallen nicht, sondern müssen in den Folgesemestern ausgeglichen werden. ", $this->optionlist), 5);
			$foot->addLine($this->document->createTextflow("Die nach alter LVVO erworbenen Überstunden bleiben bestehen und stehen unbefristet zum " .
				" intertemporalen Stundenausgleich zur Verfügung.", $this->optionlist), 10);
			$foot->addLine($this->document->createTextflow("Für weitere Fragen stehe ich Ihnen gerne zur Verfügung.", $this->optionlist), 5);
			$foot->addLine($this->document->createTextflow("Mit freundlichen Grüßen", $this->optionlist), 5);
			$foot->addLine($this->document->createTextflow("Martin Holle, Prodekan LS", $this->optionlist), 10);

			$foot->addLine($this->document->createImage('Unterschrift_MH001.png'));
			$this->document->addLine($foot, 5);

		}
		
		return TRUE;
	}
}

?>
