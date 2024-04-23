<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of classAbfrageProjekte
 *
 * @author papa
 */
class ReportAbfrageProjekte extends Report {

	public function __construct($connection, Table $dbTable, $file = 'Projekt-Abfrage.pdf') {

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
//		$where = count($DozKurz) > 0? array("$dozent in ('". implode("','", $DozKurz)."')"): array();		
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
			$head->addLine($this->document->createTextflow("Ermittlung der Auslastung durch Studienprojekte, wissenschaftliche Projektarbeiten und Proseminare im {$value['Text']}", 
					$this->optionlist . " font=$this->boldfont fontsize=12"), 20);
			$head->addLine($this->document->createTextflow("{$value['Anrede']} {$value['Vorname']} {$value['Name']},", $this->optionlist), 10);
			$head->addLine($this->document->createTextflow("für die Stundenabrechnung benötigen wir auch dieses Semester wieder detaillierte Angaben".
				" über die von Ihnen betreuten oder mit betreuten Studienprojekte und Proseminare.".
				" Bitte tragen Sie hierzu die entsprechenden Angaben in die nachstehende Tabelle ein.", $this->optionlist), 10);
			$head->addLine($this->document->createTextflow("Fehlanzeige ist nicht erforderlich.", $this->optionlist. " font=$this->boldfont"), 10);
			$head->addLine($this->document->createTextflow("Pro Teilnehmer an einem Studienprojekt oder an einer wissenschaftlichen Projektarbeit werden 0,3 LVS,". 
				" an einem Proseminar 0,2 LVS für die Betreuung angerechnet und auf die betreuenden Kollegen aufgeteilt.".
				" Für alle Betreuungsleistungen zusammen können Ihnen höchstens 4 LVS angerechnet werden.", $this->optionlist), 10);

			$this->document->addLine($head);
			

// Detail-Tabelle erstellen
			$table = $this->document->createTable(); 

			// Tabellen-Details erstellen
			$nHeader = 1;
			$table->addCell(1, 1, 'Studienprojekt / Thema', $this->head_opts_left . " colwidth=50%");
			$table->addCell(2, 1, 'Namen der Teilnehmer', $this->head_opts_center . " colwidth=30%");
			$table->addCell(3, 1, 'weitere betreuende Kollegen', $this->head_opts_center . " colwidth=10%");
			$table->addCell(4, 1, 'LVS', $this->head_opts_center . " colwidth=10%");

			//Tabellenfuss erstellen
			$table->addCell(1, 2, ' ', $this->body_opts_center . " rowheight = 200");
			$table->addCell(1, 3, 'Summe der anzurechnenden LVS', $this->foot_opts_right . " colspan=3");
			$this->document->addLine($table, 15);
			$nFooter = 0;

			// Gruppenfuß erstellen
			$foot = $this->document->createBlock();
			$foot->addLine($this->document->createTextflow(""), $this->optionlist, 25);
			$foot->addLine($this->document->createTextflow("Hamburg, den \tUnterschrift: (".$value['Vorname']." ".$value['Name'].")", $this->optionlist . " font=$this->boldfont ruler={40%} tabalignment={left} hortabmethod=ruler"), 20);	
			if ($deadline <> "") {
				$foot->addLine($this->document->createTextflow("Bitte geben Sie die ausgefüllte und unterschriebene Übersicht bis zum", $this->optionlist), 5);
				$foot->addLine($this->document->createTextflow($deadline, $this->optionlist . " font=$this->boldfont alignment=center"), 5);
				$foot->addLine($this->document->createTextflow("an mich zurück, damit ich auf dieser Basis die Semesterabrechnung erstellen kann.", $this->optionlist), 10);
			}
			if ($value['Mailzustellung'] == 'f') {
				$foot->addLine($this->document->createTextflow("Wenn Sie Ihre Semesterabrechnung zukünftig per eMail erhalten möchten, tragen Sie bitte hier Ihre eMail-Adresse ein.", $this->optionlist), 10);
				$foot->addLine($this->document->createTextflow("Meine eMail-Adresse:", $this->optionlist. " font=$this->boldfont"), 10);
			}
			$foot->addLine($this->document->createTextflow("Vielen Dank und mit freundlichen Grüßen", $this->optionlist), 5);
			$foot->addLine($this->document->createTextflow("Martin Holle, Prodekan LS", $this->optionlist), 5);
			$foot->addLine($this->document->createImage('Unterschrift_MH001.png'));
			$this->document->addLine($foot);
		}
		return TRUE;
	}
}

?>
