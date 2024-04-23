<?php
include_once 'classReport.php';

/**
 * Description of classReportZeitkonto
 *
 * @author papa
 */
class ReportAbfrageLV extends Report {
	
	public function __construct($connection, Table $dbTable, $file = 'LV-Abfrage.pdf') {

		parent::__construct($connection, $dbTable, $file);
	}
	
	public function add(array $DozKurz, $attribut, $deadline) {
				
		// aggregierte Daten abfragen
		$index = array("DozentKurz", "Status");
		$columns = array("Name", "Vorname", "Anrede", "Text", "Mailzustellung");
		$anzahl = "Anzahl";

		$group1 = array_merge($index, $columns);
		$cols1 = array_merge($group1, array("count(*) as `$anzahl`"));
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

		$cols2 = explode(',', $this->dbTable->get_cols());
		$group2 = array();
		$order2 = $this->dbTable->get_order();
		
		foreach ($groupArray as $value) {
			$index0 = $index[0];
			$index1 = $index[1];
			$where2 = array("$index0 = '$value[$index0]'", "$index1 = '$value[$index1]'");
			$rowArray = $this->query($cols2, $where2, $group2, $order2);

// ggf. neue Seite beginnen
			if ($this->first == TRUE) {
				$this->first = FALSE;
			} else {
				$this->document->addLine($this->document->createPagebreak());
			}
					
// Gruppenkopf erstellen
			$head = $this->document->createBlock();
			$head->addLine($this->document->createTextflow(date("d.m.Y"), $this->optionlist . " alignment=right"), 0);
			$head->addLine($this->document->createTextflow("Ermittlung der Lehr- und Praktikumsauslastung im {$value['Text']}", 
				$this->optionlist . " font=$this->boldfont fontsize=12"), 20);
			$head->addLine($this->document->createTextflow("{$value['Anrede']} {$value['Vorname']} {$value['Name']},", $this->optionlist), 10);
			$head->addLine($this->document->createTextflow("anbei erhalten Sie eine auf der Stundenplanung basierende Übersicht " .
				"über Ihre Lehrveranstaltungen im {$value['Text']} (alle Angaben in LVS). " .
				"Bitte prüfen Sie diese Liste kritisch, ergänzen fehlende Lehrveranstaltungen und Lehrexporte " .
				"und streichen fälschlich aufgeführte Lehrveranstaltungen.",
				$this->optionlist), 10);
			if ($value[$index1] == 'Prof') {
				$head->addLine($this->document->createTextflow("Mit Ihrer Unterschrift kommen Sie Ihrer Verpflichtung gemäß §20(1) LVVO nach, " .
					"die persönliche Erfüllung Ihrer Lehrverpflichtung schriftlich zu bestätigen.", 
					$this->optionlist), 10);
			}
			$head->addLine($this->document->createTextflow("Bitte tragen Sie ggf. die Anzahl der Teilungsgruppen und unbedingt die Anzahl Teilnehmer je Lehrveranstaltung ein.",
				$this->optionlist), 10);
			
			if ($value['Status'] == 'Prof') {
				$head->addLine($this->document->createTextflow("Die auf Ihr Lehrdeputat anzurechnenden Stunden können Sie leicht selber errechnen: ", 
					$this->optionlist), 0);
				$head->addLine($this->document->createTextflow("Stunden = LVS * ggf. Anzahl der Gruppen * Mein Anteil * ggf. Betreuung.",
					$this->optionlist), 10);
			}
			
			$this->document->addLine($head);

// Detail-Tabelle erstellen
			$body = $this->document->createBlock();
			$table = $this->document->createTable();

// Beginn Anpassung
			if ($value[$index1] == 'Prof') {
				$nHeader = 1;
				$table->addCell(1, 1, 'Lehrveranstaltung/Praktikum', $this->head_opts_left . " colwidth=35%");
				$table->addCell(2, 1, ' ', $this->head_opts_left . " colwidth=5%");
				$table->addCell(3, 1, 'Studiengruppe', $this->head_opts_center . " colwidth=10%");
				$table->addCell(4, 1, 'LVS', $this->head_opts_center . " colwidth=8%");
				$table->addCell(5, 1, 'Gruppen', $this->head_opts_center . " colwidth=10%");
				$table->addCell(6, 1, 'Mein Anteil', $this->head_opts_center . " colwidth=8%");
				$table->addCell(7, 1, 'Betreuung', $this->head_opts_center . " colwidth=8%");
				$table->addCell(8, 1, 'Teilnehmer', $this->head_opts_center . " colwidth=8%");
				$table->addCell(9, 1, 'Stunden', $this->head_opts_center . " colwidth=8%");

				foreach ($rowArray as $j => $row) {
					$table->addCell(1, $j + 2, $row['Fach'], $this->body_opts_left);
					$table->addCell(2, $j + 2, $row['FachKurz'], $this->body_opts_left);
					$table->addCell(3, $j + 2, $row['Studiengang'], $this->body_opts_center);
					$table->addCell(4, $j + 2, $row['LVS'], $this->body_opts_center);
					if ($row['Typ'] == 'L') {
						// $table->addCell(5, $j + 2, $row['T'], $this->body_opts_center);
					} else {
						$table->addCell(5, $j + 2, '-', $this->body_opts_center);
					}
					$table->addCell(6, $j + 2, $row['K']*100.0 . '%', $this->body_opts_center);
					if ($row['Typ'] == 'L') {
						$table->addCell(7, $j + 2, $row['B'], $this->body_opts_center);
					} else {
						$table->addCell(7, $j + 2, '-', $this->body_opts_center);				
					}
				}

			} else if ($value[$index1] == 'LB') {
				$nHeader = 1;
				$table->addCell(1, 1, 'Lehrveranstaltung/Praktikum', $this->head_opts_left . " colwidth=35%");
				$table->addCell(2, 1, ' ', $this->head_opts_left . " colwidth=5%");
				$table->addCell(3, 1, 'Studiengruppe', $this->head_opts_center . " colwidth=10%");
				$table->addCell(4, 1, 'LVS', $this->head_opts_center . " colwidth=10%");
				$table->addCell(5, 1, 'Gruppen', $this->head_opts_center . " colwidth=10%");
				$table->addCell(6, 1, 'Mein Anteil', $this->head_opts_center . " colwidth=10%");
				$table->addCell(7, 1, 'Betreuung', $this->head_opts_center . " colwidth=10%");
				$table->addCell(8, 1, 'Teilnehmer', $this->head_opts_center . " colwidth=10%");

				foreach ($rowArray as $j => $row) {
					$table->addCell(1, $j + 2, $row['Fach'], $this->body_opts_left);
					$table->addCell(2, $j + 2, $row['FachKurz'], $this->body_opts_left);
					$table->addCell(3, $j + 2, $row['Studiengang'], $this->body_opts_center);
					$table->addCell(4, $j + 2, $row['LVS'], $this->body_opts_center);
					if ($row['Typ'] == 'L') {
						// $table->addCell(5, $j + 2, $row['T'], $this->body_opts_center);
					} else {
						$table->addCell(5, $j + 2, '-', $this->body_opts_center);
					}
					$table->addCell(6, $j + 2, $row['K']*100.0 . '%', $this->body_opts_center);
					if ($row['Typ'] == 'L') {
						$table->addCell(7, $j + 2, $row['B'], $this->body_opts_center);
					} else {
						$table->addCell(7, $j + 2, '-', $this->body_opts_center);				
					}
				}

			} else if ($value[$index1] == 'Ami') {
				$nHeader = 1;
				$table->addCell(1, 1, 'Lehrveranstaltung/Praktikum', $this->head_opts_left . " colwidth=35%");
				$table->addCell(2, 1, ' ', $this->head_opts_left . " colwidth=5%");
				$table->addCell(3, 1, 'Studiengruppe', $this->head_opts_center . " colwidth=10%");
				$table->addCell(4, 1, 'LVS', $this->head_opts_center . " colwidth=10%");
				$table->addCell(5, 1, 'Gruppen', $this->head_opts_center . " colwidth=10%");
				$table->addCell(6, 1, 'Mein Anteil', $this->head_opts_center . " colwidth=10%");
				$table->addCell(7, 1, 'Teilnehmer', $this->head_opts_center . " colwidth=10%");
				$table->addCell(8, 1, 'Stunden', $this->head_opts_center . " colwidth=10%");

				foreach ($rowArray as $j => $row) {
					$table->addCell(1, $j + 2, $row['Fach'], $this->body_opts_left);
					$table->addCell(2, $j + 2, $row['FachKurz'], $this->body_opts_left);
					$table->addCell(3, $j + 2, $row['Studiengang'], $this->body_opts_center);
					$table->addCell(4, $j + 2, $row['LVS'], $this->body_opts_center);
					if ($row['Typ'] == 'L') {
						// $table->addCell(5, $j + 2, $row['T'], $this->body_opts_center);
					} else {
						$table->addCell(5, $j + 2, '-', $this->body_opts_center);
					}
					$table->addCell(6, $j + 2, $row['K']*100.0 . '%', $this->body_opts_center);
				}
			}

			for ($i = 0; $i < 3; $i++) {
				$table->addCell(1, $j + 3 + $i, ' ', $this->body_opts_center);
			}
			$nFooter = 0;
			
			$body->addLine($table, 10);

			if ($value[$index1] == 'Prof' || $value[$index1] == 'LB') {
				$body->addLine($this->document->createTextflow("Erläuterung der Betreuung:", $this->optionlistsmall . " font=$this->boldfont leading=100%"), 5);
				$body->addLine($this->document->createTextflow("1.00 - Betreuung durch den persönlich während der gesamten Praktikumszeit anwesenden Professor", 
					$this->optionlistsmall), 0);
				$body->addLine($this->document->createTextflow("0.65 - 50% der Praktikumszeit betreut der Professor persönlich und 50% der Praktikumszeit betreut".
					" ein Assistent unter der Verantwortung des Professors", 
					$this->optionlistsmall), 0);
				$body->addLine($this->document->createTextflow("0.30 - Betreuung durch einen Assistenten unter der Verantwortung eines Professors", 
					$this->optionlistsmall), 0);
				$body->addLine($this->document->createTextflow("0.00 - für die Lehrveranstaltung wurde kein eigener Termin eingerichtet; die Studenten nehmen".
					" an einem parallel laufenden inhaltlich gleichen Praktikum teil", $this->optionlistsmall), 0);
			}
			$this->document->addLine($body);
			
			// Gruppenfuß erstellen
			$foot = $this->document->createBlock();
			$foot->addLine($this->document->createTextflow("",  $this->optionlist), 10);
			if ($value[$index1] == 'Prof') {
				$foot->addLine($this->document->createTextflow("Hiermit bestätige ich die persönliche Erfüllung meiner Lehrverpflichtung. ".
					"Bei Online-Veranstaltungen habe ich die Studierenden gemäß § 5a LVVO während der Durchführung aktiv betreut.", 
					$this->optionlist . " font=$this->boldfont"), 15);
				
			}
			$foot->addLine($this->document->createTextflow("", $this->optionlist), 15);
			$foot->addLine($this->document->createTextflow("Hamburg, den \tUnterschrift:", 
					$this->optionlist . " font=$this->boldfont ruler={40%} tabalignment={left} hortabmethod=ruler"), 20);
			if ($deadline <> "") {
				$foot->addLine($this->document->createTextflow("Bitte geben Sie die ausgefüllte und unterschriebene Übersicht bis zum", 
						$this->optionlist), 5);
				$foot->addLine($this->document->createTextflow($deadline, $this->optionlist . " font=$this->boldfont alignment=center"), 5);
			$foot->addLine($this->document->createTextflow("an mich zurück, damit ich auf dieser Basis die Semesterabrechnung erstellen kann.", 
					$this->optionlist), 10);
			}
			if ($value['Mailzustellung'] == 'f') {
				$foot->addLine($this->document->createTextflow("Wenn Sie Ihre Semesterabrechnung zukünftig per eMail erhalten möchten, " .
					"tragen Sie bitte hier Ihre eMail-Adresse ein.",
					$this->optionlist), 10);
				$foot->addLine($this->document->createTextflow("Meine eMail-Adresse:", 
					$this->optionlist. " font=$this->boldfont"), 10);
			}
			$foot->addLine($this->document->createTextflow("Vielen Dank und mit freundlichen Grüßen", $this->optionlist), 10);
			$foot->addLine($this->document->createTextflow("Martin Holle, Prodekan LS", $this->optionlist), 10);

			$foot->addLine($this->document->createImage('Unterschrift_MH001.png'));
			$this->document->addLine($foot);
			
		}
		return TRUE;
		
	}
}

?>
