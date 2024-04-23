<?php

// Structure tables:
// Header, Table, Columns, Where, Order, ID, Optionlist

function setTables() {
	$geschlechter = array('m', 'w', 'd');
	$status = array('Prof', 'Ami', 'LB');
	$semester = array('S', 'W');
	$professuren = array(' ', 'C', 'W', 'X');
	
    $_SESSION['tables'] = array(
        'aktuellesSemester'
        => new Table('aktuelles Semester', 'Semester', 'Jahr,Semester,Text,VorsemesterJahr,VorsemesterSemester', array('aktuell = \'true\'')),
        'AbfrageLehrveranstaltungen'
        => new Table('Abfrage Lehrveranstaltungen', 'Abfrage Lehrveranstaltungen', '*', array(), array('DozentKurz COLLATE utf8_general_ci')),
        'AbfrageProjekte'
        => new Table('Abfrage Studienprojekte', 'Abfrage Studienprojekte', '*', array(), array('DozentKurz COLLATE utf8_general_ci')),
        'Auslastung'
        => new Table('Entlastungen', 'Auslastung', '*', array(), array('DozentKurz COLLATE utf8_general_ci', 'Jahr DESC', 'Semester DESC', 'Grund', 'Kommentar'), array('ID'), array('Semester' => $semester, 'Status' => $status)),
        'Auslastungsgrund'
        => new Table('Entlastungsgründe', 'Auslastungsgrund', '*', array(), array('Grund'), array('Grund')),
        'Beteiligung'
        => new Table('Beteiligungen', 'Beteiligung', '*', array(), array('Jahr DESC', 'Semester DESC', 'DozentKurz', 'Fach'), array('ID'), array('Semester' => $semester, 'Status' => $status)),
        'Bilanz'
        => new Table('Bilanzen', 'BilanzSaldo', '*', array(), array('Name', 'Vorname', 'Semester'), array('Kurz', 'Status')),
        'Bilanzierung'
        => new Table('Bilanzierungen', 'Bilanzierung', '*', array('Kurz = $1'), array('Kurz', 'FachKurz')),
        'Dozent'
        => new Table('Dozenten', 'Dozent', '*', array(), array('Name'), array('Kurz'), array('Status' => $status, 'Geschlecht' => $geschlechter, 'Professur' => $professuren)),
        'Entlastungen nach Dozent'
        => new Table('Entlastungen', 'Entlastungen', '*', array(), array('Name', 'Grund', 'Kommentar')),
        'Entlastungen nach Text'
        => new Table('Entlastungen', 'Entlastungen', '*', array(), array('Kommentar', 'Name')),
        'Fach'
        => new Table('Fächer', 'Fach', '*', array(), array('Kurz COLLATE utf8_general_ci'), array('Kurz')),
        'Import_LVA'
        => new Table('Import', 'Import_LVA', '*', array(), array('Lehrer', 'Fach', 'Klasse'), array('ID')),
        'Import_LVA_GW'
        => new Table('Import', 'Import_LVA_GW', '*', array(), array(), array('ID')),
        'Import_LVA_OT'
        => new Table('Import', 'Import_LVA_OT', '*', array(), array(), array('ID')),
        'Import_LVA_LB'
        => new Table('Import', 'Import_LVA_LB', '*', array(), array(), array('ID')),
        'LVA'
        => new Table('Lehrveranstaltungen', 'LVA', '*', array(), array('FachKurz', 'Studiengang', 'Jahr DESC', 'Semester DESC'), array('FachKurz', 'Studiengang', 'Jahr', 'Semester'), array('Semester' => $semester)),
        'Lehrverpflichtung'
        => new Table('Lehrverpflichtungen', 'Lehrverpflichtung', '*', array(), array('DozKurz', 'Jahr DESC', 'Semester DESC'), array('DozKurz', 'Status', 'Jahr', 'Semester'), array('Semester' => $semester, 'Status' => $status, 'Professur' => $professuren)),
        'Saldo3'
        => new Table('Salden', 'Saldo3', 'Jahr,Semester,DozKurz,Abrechnungsjahr,Abrechnungssemester,Stunden,Status,manuell', # kein '*' möglich!!!
                array(), array('Jahr DESC', 'Semester DESC', 'DozKurz', 'Abrechnungsjahr ASC', 'Abrechnungssemester ASC'), array('Jahr', 'Semester', 'DozKurz', 'Status', 'Abrechnungsjahr', 'Abrechnungssemester'), array('Status' => $status)),
        'Saldo3intertemporaer'
        => new Table('Salden intertemporär', 'Saldo3intertemporaer', '*'),
        'Saldierung3'
        => new Table('Salden', 'Saldierung3', '*', array(), array('DozKurz', 'Status', 'Jahr', 'Semester', 'Abrechnungsjahr', 'Abrechnungssemester')),
        'Semester'
        => new Table('Semester', 'Semester', '*', array(), array('Jahr DESC', 'Semester DESC'), array('Jahr', 'Semester'), array('Semester' => $semester, 'VorsemesterSemester' => $semester)),
        'Studiengang'
        => new Table('Studiengänge', 'Studiengang', '*', array(), array('Kurz COLLATE utf8_general_ci'), array('Kurz')),
        'Zeitkonto'
        => new Table('Zeitkonto', 'Zeitkonto', '*', array(), array('DozKurz COLLATE utf8_general_ci', 'Status', 'Jahr', 'Semester')),
        'Zeitkonto_Table'
        => new Table('Zeitkonto Table', 'Zeitkonto_Table', '*', array(), array('DozKurz COLLATE utf8_general_ci', 'Status', 'Jahr', 'Semester')),
        'Zeitkontosicht'
        => new Table('Zeitkonto', 'Zeitkontosicht', '*', array(), array('DozKurz COLLATE utf8_general_ci', 'Status', 'Jahr', 'Semester')),
        'Zuordnung'
        => new Table('Zuordnungen', 'Zuordnung', '*', array(), array('Jahr DESC', 'Semester DESC', 'FachKurz', 'Text', 'Studiengang'), array('Jahr', 'Semester', 'FachKurz', 'Text', 'Studiengang'))
    );
}
