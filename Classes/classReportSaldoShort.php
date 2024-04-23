<?php

/**
 * Description of classReportSaldi
 *
 * @author papa
 */
class ReportSaldoShort extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'Saldo Übersicht.pdf') {

		parent::__construct($connection, $dbTable, $file);
		$this->first = TRUE;
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {

		// aggregierte Daten abfragen
		$index = array("DozKurz", "Status");
		$columns = array("Name", "Vorname", "Anrede");
		$anzahl = "Anzahl";

		$group1 = array_merge($index, $columns);
		$cols1 = array_merge($group1, array("count(*) as `$anzahl`"));
		$where1 = count($DozKurz) > 0? 
			array("$index[0] = '".implode("' OR $index[0] = '", $DozKurz)."'"): 
			array();
		$order1 = array($columns[0]);

		$where1[] = "Zeitkonto = 0";
		
		$groupArray = $this->query($cols1, $where1, $group1, $order1);
		
		if ($groupArray == false || $groupArray->num_rows == 0) {
			echo 'nothing to do...<br>';
			return FALSE;
		}
				
// Detaillierte Daten abfragen

		$cols2 = explode(',', $this->dbTable->get_rows());
#		$where2 = array("Kurz = '$DozKurz'", "Status = '$Status'");
		$whereOld = array(
			"Jahr = {$this->aktuellesSemester['VorsemesterJahr']}", 
			"Semester = '{$this->aktuellesSemester['VorsemesterSemester']}'",
			"intertemporaer = true");
		$whereNew = array(
			"Jahr = {$this->aktuellesSemester['Jahr']}", 
			"Semester = '{$this->aktuellesSemester['Semester']}'",
			"intertemporaer = true");
		$group2 = array();
		$order2 = $this->dbTable->get_order();
		
		$headercols = array("KurzText", "Abrechnungsjahr", "Abrechnungssemester");
		$headerwhere = array("intertemporaer = true");
		$headergroup = array();
		$headerorder = array("Abrechnungsjahr ASC", "Abrechnungssemester ASC");
		$headerResult = $this->query($headercols, $headerwhere, $headergroup, $headerorder, TRUE);
		if (!$headerResult) {
			die ("Error in DB-Query");
		}
		
		$headerArray = $headerResult->fetch_array(MYSQLI_NUM);
		var_dump($headerArray);
		
		foreach ($groupArray as $value) {
			$where2 = array();
			foreach ($index as $col) {
				$where2[] = "`$col` = '$value[$col]' ";
			}
			
			$rowArrayOld = $this->query($cols2, array_merge($where2,$whereOld), $group2, $order2);
			$rowArrayNew = $this->query($cols2, array_merge($where2,$whereNew), $group2, $order2);

			$part = $this->document->createBlock();
			
// Gruppenkopf erstellen
			$head = $this->document->createBlock();
			$textflow = $this->document->createTextflow("{$value['Name']}, ", $this->optionlist . " font=$this->boldfont");
			$textflow->addTextflow("{$value['Vorname']}, {$value['Status']}", $this->optionlist);
			$head->addLine($textflow);
			$part->addLine($head, 5);
	
	
// Detail-Tabelle erstellen
			$table = $this->document->createTable();

			$tablerow = 1;
			$colwidth = 90 / ($headerResult->num_rows + 1);
			$sumcol = $headerResult->num_rows + 2;
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

			$part->addLine($table, 10);

// Gruppenfuß erstellen

			
			$this->document->addLine($part);	
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

?>
