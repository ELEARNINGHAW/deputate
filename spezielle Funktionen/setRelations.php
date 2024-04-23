<?php

function setRelations() {
    $_SESSION['relations'] = array(// Master => Detail
        'DozentAuslastung'
        => new Relation(array('Kurz' => 'DozentKurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Auslastung']),
        'DozentBeteiligung'
        => new Relation(array('Kurz' => 'DozentKurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Beteiligung']),
        'DozentBilanz'
        => new Relation(array('Kurz' => 'Kurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Bilanz']),
        #	'BilanzSaldo'				
        #		=> new Relation(array('Kurz' => 'DozKurz', 'Status' => 'Status'), $_SESSION['tables']['Bilanz'], $_SESSION['tables']['Saldo']),
        'BilanzSaldo3'
        => new Relation(array('Kurz' => 'DozKurz', 'Status' => 'Status'), $_SESSION['tables']['Bilanz'], $_SESSION['tables']['Saldo3']),
        'BilanzZeitkonto'
        => new Relation(array('Kurz' => 'DozKurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Zeitkonto']),
        'DozentLehrverpflichtung'
        => new Relation(array('Kurz' => 'DozKurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Lehrverpflichtung']),
        #	'DozentSaldo'				
        #		=> new Relation(array('Kurz' => 'DozKurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Saldo']),
        'DozentSaldo3'
        => new Relation(array('Kurz' => 'DozKurz'), $_SESSION['tables']['Dozent'], $_SESSION['tables']['Saldo3']),
        'FachBeteiligung'
        => new Relation(array('Kurz' => 'Fach'), $_SESSION['tables']['Fach'], $_SESSION['tables']['Beteiligung']),
        'FachLVA'
        => new Relation(array('Kurz' => 'FachKurz'), $_SESSION['tables']['Fach'], $_SESSION['tables']['LVA']),
        'LVABeteiligung'
        => new Relation(array('FachKurz' => 'Fach', 'Studiengang' => 'Studiengang', 'Jahr' => 'Jahr', 'Semester' => 'Semester'), $_SESSION['tables']['LVA'], $_SESSION['tables']['Beteiligung']),
        'LVAZuordnung'
        => new Relation(array('Jahr' => 'Jahr', 'Semester' => 'Semester', 'FachKurz' => 'FachKurz', 'Studiengang' => 'Text'), $_SESSION['tables']['LVA'], $_SESSION['tables']['Zuordnung']),
        'AuslastungsgrundAuslastung	'
        => new Relation(array('Grund' => 'Grund'), $_SESSION['tables']['Auslastungsgrund'], $_SESSION['tables']['Auslastung'])
    );
}
