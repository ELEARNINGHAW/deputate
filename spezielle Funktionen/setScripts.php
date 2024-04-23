<?php

function setScripts() {
    $_SESSION['scripts'] = array(// Skripte
        'copy_Lehrverpflichtung' =>
        array(
            "DELETE FROM Lehrverpflichtung
                WHERE (Lehrverpflichtung.Jahr, Lehrverpflichtung.Semester) in (SELECT Jahr, Semester FROM  Semester WHERE aktuell=true)",
            "INSERT INTO Lehrverpflichtung
		(SELECT Lehrverpflichtung.DozKurz, Lehrverpflichtung.Status, Lehrverpflichtung.Professur, Semester.Jahr, Semester.Semester, Dozent.Pflicht_weg, NULL, false, Lehrverpflichtung.grundfinanziert 
                    FROM  Dozent, Lehrverpflichtung, Semester
                    WHERE Semester.aktuell = true
                    AND Lehrverpflichtung.Jahr = Semester.VorsemesterJahr
                    AND  Lehrverpflichtung.Semester = Semester.VorsemesterSemester
                    AND Dozent.Kurz=Lehrverpflichtung.DozKurz)"
        ),
        'copy_Auslastung' =>
        array(
            "DELETE FROM Auslastung 
		WHERE (Auslastung.Jahr, Auslastung.Semester) in (SELECT Jahr, Semester FROM  Semester WHERE aktuell=true)",
            "INSERT INTO Auslastung (Jahr,Semester,DozentKurz, Status, LVS,Grund,Kommentar,Erfassung)
                (SELECT Semester.Jahr,Semester.Semester,Auslastung.DozentKurz, Auslastung.Status, Auslastung.LVS,Auslastung.Grund,Auslastung.Kommentar,current_date
                    FROM  Auslastung, Semester
                    WHERE Auslastung.Jahr = Semester.VorsemesterJahr 
                    AND Auslastung.Semester = Semester.VorsemesterSemester
                    AND Auslastung.Grund in ('E','F','Z')
                    AND Semester.aktuell = true)"
        ),
        'check_Konsistenz' =>
        array(
            "SELECT distinct Import_LVA.Lehrer,
                Import_LVA.Fach AS 'Fach',
                Import_LVA.Klasse,
                Import_LVA_GW.DozKurz AS 'DozKurz (GW)',
                Import_LVA_GW.Fach AS 'Fach (GW)',
                Import_LVA_GW.Studg AS 'Studg (GW)'
                FROM Import_LVA, Import_LVA_GW
                WHERE Import_LVA.Lehrer = Import_LVA_GW.DozKurz
                AND Import_LVA.Fach = Import_LVA_GW.Fach
                AND Import_LVA.Klasse <> Import_LVA_GW.Studg",
            "SELECT distinct Import_LVA.Lehrer,
                Import_LVA.Fach AS 'Fach',
                Import_LVA.Klasse,
                Import_LVA_OT.DozKurz AS 'DozKurz (ÖT)',
                Import_LVA_OT.Fach AS 'Fach (ÖT)',
                Import_LVA_OT.Studg AS 'Studg (ÖT)'
                FROM Import_LVA, Import_LVA_OT
                WHERE Import_LVA.Lehrer = Import_LVA_OT.DozKurz
                AND Import_LVA.Fach = Import_LVA_OT.Fach
                AND Import_LVA.Klasse <> Import_LVA_OT.Studg"
        ),
        'vorhandene_LVA' =>
        array(
            "SELECT LVA.Jahr, LVA.Semester, LVA.FachKurz, LVA.Studiengang, Import_LVA.Fach, Import_LVA.Klasse 
                FROM LVA AS LVA,
                Import_LVA AS Import_LVA, 
                Semester AS Semester 
                WHERE LVA.FachKurz = Import_LVA.Fach 
                AND LVA.Studiengang = Import_LVA.Klasse 
                AND LVA.Jahr = Semester.Jahr 
                AND LVA.Semester = Semester.Semester 
                AND LVA.FachKurz IS NOT NULL 
                AND LVA.Studiengang IS NOT NULL 
                AND LVA.Jahr IS NOT NULL 
                AND LVA.Semester IS NOT NULL 
                AND Semester.aktuell = True",
            "SELECT LVA.Jahr, LVA.Semester, LVA.FachKurz, LVA.Studiengang, Import_LVA_GW.Fach, Import_LVA_GW.Studg 
                FROM Semester AS Semester, 
                    LVA AS LVA, 
                    Import_LVA_GW AS Import_LVA_GW 
                WHERE Semester.Jahr = LVA.Jahr 
                    AND Semester.Semester = LVA.Semester 
                    AND LVA.FachKurz = Import_LVA_GW.Fach 
                    AND LVA.Studiengang = Import_LVA_GW.Studg 
                    AND LVA.Jahr IS NOT NULL 
                    AND LVA.Semester IS NOT NULL 
                    AND LVA.FachKurz IS NOT NULL 
                    AND LVA.Studiengang IS NOT NULL 
                    AND Semester.aktuell = TRUE",
            "SELECT LVA.Jahr, LVA.Semester, LVA.FachKurz, LVA.Studiengang, Import_LVA_OT.Fach, Import_LVA_OT.Studg 
                FROM LVA AS LVA, 
                    Semester AS Semester, 
                    Import_LVA_OT AS Import_LVA_OT 
                WHERE LVA.Jahr = Semester.Jahr 
                    AND LVA.Semester = Semester.Semester 
                    AND LVA.Studiengang = Import_LVA_OT.Studg 
                    AND LVA.FachKurz = Import_LVA_OT.Fach 
                    AND LVA.Jahr IS NOT NULL 
                    AND LVA.Semester IS NOT NULL 
                    AND LVA.FachKurz IS NOT NULL 
                    AND LVA.Studiengang IS NOT NULL 
                    AND Semester.aktuell = TRUE"
        ),
        'fehlende_Faecher' =>
        array(
            "SELECT distinct Import_LVA.Fach 
                FROM Import_LVA AS Import_LVA 
                    LEFT OUTER JOIN Fach AS Fach ON Import_LVA.Fach = Fach.Kurz 
                WHERE Fach.Kurz IS NULL
                ORDER BY Fach",
            "SELECT DISTINCT ID, Import_LVA_GW.Fach, Import_LVA_GW.LVA
                FROM Import_LVA_GW AS Import_LVA_GW 
                    LEFT OUTER JOIN Fach AS Fach ON Import_LVA_GW.Fach = Fach.Kurz 
                WHERE Fach.Kurz IS NULL",
            "SELECT DISTINCT ID, Import_LVA_OT.Fach, Import_LVA_OT.LVA 
                FROM Import_LVA_OT AS Import_LVA_OT 
                    LEFT OUTER JOIN Fach AS Fach ON Import_LVA_OT.Fach = Fach.Kurz 
                WHERE Fach.Kurz IS NULL"
        ),
        'vorhandene_Beteiligungen' =>
        array(
            "SELECT Import_LVA.Fach, Import_LVA.Klasse, Import_LVA.Lehrer, Beteiligung.Fach, Beteiligung.Studiengang, Beteiligung.DozentKurz 
                FROM Beteiligung
                    LEFT OUTER JOIN Import_LVA ON Beteiligung.Fach = Import_LVA.Fach 
                        AND Beteiligung.Studiengang = Import_LVA.Klasse 
                        AND Beteiligung.DozentKurz = Import_LVA.Lehrer, 
                    Semester AS Semester 
                WHERE Semester.Jahr = Beteiligung.Jahr
                    AND Semester.Semester = Beteiligung.Semester 
                    AND Import_LVA.Fach IS NOT NULL 
                    AND Import_LVA.Klasse IS NOT NULL 
                    AND Import_LVA.Lehrer IS NOT NULL 
                    AND Semester.aktuell = TRUE;"
        ),
        'SWS_vortragen' =>
        array(
            "update LVA AS LVAneu, LVA AS LVAalt, Semester AS Semester, `aktuelles Semester`AS aktSemester 
					set LVAneu.SWS = LVAalt.SWS
					WHERE LVAneu.SWS = 0
						AND Semester.Semester = aktSemester.Semester
						AND Semester.Jahr = aktSemester.Jahr 
						AND LVAneu.Jahr = Semester.Jahr
						AND LVAneu.Semester = Semester.Semester
						AND LVAalt.Jahr = Semester.VorsemesterJahr
						AND LVAalt.Semester = Semester.VorsemesterSemester
						AND LVAalt.FachKurz = LVAneu.FachKurz
						AND LVAalt.Studiengang = LVAneu.Studiengang",
            "update LVA AS LVAneu, LVA AS LVAalt, Semester AS Semester, Semester AS Vorsemester, `aktuelles Semester`AS aktSemester 
					set LVAneu.SWS = LVAalt.SWS
					WHERE LVAneu.SWS = 0
						AND Semester.Semester = aktSemester.Semester
						AND Semester.Jahr = aktSemester.Jahr
						AND Vorsemester.Jahr = Semester.VorsemesterJahr
						AND Vorsemester.Semester = Semester.VorsemesterSemester
						AND LVAneu.Jahr = Semester.Jahr
						AND LVAneu.Semester = Semester.Semester
						AND LVAalt.Jahr = Vorsemester.VorsemesterJahr
						AND LVAalt.Semester = Vorsemester.VorsemesterSemester
						AND LVAalt.FachKurz = LVAneu.FachKurz
						AND LVAalt.Studiengang = LVAneu.Studiengang"
        ),
        'SWS_checken' =>
        array(
            "SELECT *
					FROM  LVA 
					WHERE SWS = 0.0"
        ),
        'LVA_importieren' =>
        array(
            "INSERT into LVA (Jahr,Semester, FachKurz, Studiengang, SWS)
					(SELECT DISTINCT aktSemester.Jahr, aktSemester.Semester, Import.Fach, Import.Klasse, 0.0
					FROM Import_LVA AS Import, `aktuelles Semester` AS aktSemester
					WHERE not exists (
						SELECT * FROM  LVA 
						WHERE Import.Fach = LVA.FachKurz 
							AND Import.Klasse = LVA.Studiengang 
							AND aktSemester.Jahr = LVA.Jahr 
							AND aktSemester.Semester = LVA.Semester
					)
					GROUP BY aktSemester.Jahr, aktSemester.Semester, Import.Fach, Import.Klasse
					ORDER BY Import.Fach, Import.Klasse)",
            "INSERT into LVA (Jahr, Semester, FachKurz, Studiengang, SWS, Kommentar)
					(SELECT distinct aktSemester.Jahr, aktSemester.Semester, Import.Fach, Import.Studg, 0.0, Import.Bemerkungen
					FROM  Import_LVA_GW AS Import LEFT OUTER JOIN LVA ON Import.Fach = LVA.FachKurz AND Import.Studg = LVA.Studiengang, `aktuelles Semester` AS aktSemester
					WHERE not exists (
						SELECT * FROM  LVA 
						WHERE Import.Fach = LVA.FachKurz AND Import.Studg = LVA.Studiengang AND aktSemester.Jahr = LVA.Jahr AND aktSemester.Semester = LVA.Semester
					)
					GROUP BY aktSemester.Jahr, aktSemester.Semester, Import.Fach, Import.Studg, Import.SWS
					ORDER BY Import.Fach)",
            "INSERT into LVA (Jahr, Semester, FachKurz, Studiengang, SWS, Kommentar)
					(SELECT distinct aktSemester.Jahr, aktSemester.Semester, Import.Fach, Import.Studg, 0.0, Import.Bemerkungen
					FROM  Import_LVA_OT AS Import LEFT OUTER JOIN LVA ON Import.Fach = LVA.FachKurz AND Import.Studg = LVA.Studiengang, `aktuelles Semester` AS aktSemester
					WHERE not exists (
						SELECT * FROM  LVA 
						WHERE Import.Fach = LVA.FachKurz AND Import.Studg = LVA.Studiengang AND aktSemester.Jahr = LVA.Jahr AND aktSemester.Semester = LVA.Semester
					)
					GROUP BY aktSemester.Jahr, aktSemester.Semester, Import.Fach, Import.Studg, Import.SWS
					ORDER BY Import.Fach)"
        ),
        'fehlende_LVA' =>
        array(
            "SELECT DISTINCT Import.Fach, Import.Klasse 
					FROM Import_LVA AS Import
					where
						(SELECT FachKurz FROM  LVA, `aktuelles Semester` AS aktSemester
						WHERE LVA.Jahr = aktSemester.Jahr 
						AND LVA.Semester = aktSemester.Semester
						AND Import.Fach = LVA.FachKurz 
						AND Import.Klasse = LVA.Studiengang)
					is null
					OR
						(SELECT Studiengang FROM  LVA, `aktuelles Semester` AS aktSemester
						WHERE LVA.Jahr = aktSemester.Jahr 
						AND LVA.Semester = aktSemester.Semester
						AND Import.Fach = LVA.FachKurz 
						AND Import.Klasse = LVA.Studiengang)
					is null",
            "SELECT DISTINCT Import.Fach, Import.Studg 
					FROM Import_LVA_OT AS Import
					where
						(SELECT FachKurz FROM  LVA, `aktuelles Semester` AS aktSemester
						WHERE LVA.Jahr = aktSemester.Jahr 
						AND LVA.Semester = aktSemester.Semester
						AND Import.Fach = LVA.FachKurz 
						AND Import.Studg = LVA.Studiengang)
					is null
					OR
						(SELECT Studiengang FROM  LVA, `aktuelles Semester` AS aktSemester
						WHERE LVA.Jahr = aktSemester.Jahr 
						AND LVA.Semester = aktSemester.Semester
						AND Import.Fach = LVA.FachKurz 
						AND Import.Studg = LVA.Studiengang)
					is null",
            "SELECT DISTINCT Import.Fach, Import.Studg 
					FROM Import_LVA_GW AS Import
					where
						(SELECT FachKurz FROM  LVA, `aktuelles Semester` AS aktSemester
						WHERE LVA.Jahr = aktSemester.Jahr 
						AND LVA.Semester = aktSemester.Semester
						AND Import.Fach = LVA.FachKurz 
						AND Import.Studg = LVA.Studiengang)
					is null
					OR
						(SELECT Studiengang FROM  LVA, `aktuelles Semester` AS aktSemester
						WHERE LVA.Jahr = aktSemester.Jahr 
						AND LVA.Semester = aktSemester.Semester
						AND Import.Fach = LVA.FachKurz 
						AND Import.Studg = LVA.Studiengang)
					is null"
        ),
        'fehlende_Lehrverpflichtungen' =>
        array(
            "SELECT distinct Import.Lehrer AS 'Lehrer(Dozent)'
					FROM Import_LVA AS Import
						LEFT OUTER JOIN `aktuelle Lehrverpflichtung` AS Lehrverpflichtung 
						ON Import.Lehrer = Lehrverpflichtung.DozKurz
					WHERE Lehrverpflichtung.DozKurz IS NULL 
					ORDER BY Import.Lehrer ASC",
            "SELECT DISTINCT Import.DozKurz AS DozKurz, Import.Status AS Status
					FROM Import_LVA_OT AS Import
						LEFT OUTER JOIN `aktuelle Lehrverpflichtung` AS Lehrverpflichtung 
						ON Import.DozKurz = Lehrverpflichtung.DozKurz 
						AND Import.Status = Lehrverpflichtung.Status 
					WHERE Lehrverpflichtung.DozKurz IS NULL 
					ORDER BY Import.DozKurz ASC",
            "SELECT DISTINCT Import.DozKurz AS DozKurz, Import.Status AS Status
					FROM Import_LVA_GW AS Import
						LEFT OUTER JOIN `aktuelle Lehrverpflichtung` AS Lehrverpflichtung 
						ON Import.DozKurz = Lehrverpflichtung.DozKurz 
						AND Import.Status = Lehrverpflichtung.Status 
					WHERE Lehrverpflichtung.DozKurz IS NULL 
					ORDER BY Import.DozKurz ASC"
        ),
        'Beteiligungen_importieren' =>
        array(
            "delete FROM  Beteiligung using Beteiligung, `aktuelles Semester` AS aktSemester 
					WHERE Beteiligung.Jahr = aktSemester.Jahr
						AND Beteiligung.Semester = aktSemester.Semester",
            /* Mastertabelle Stundenplan */
            "INSERT into Beteiligung (DozentKurz, Status, Jahr, Semester, Fach, Studiengang, T, K, B, istBetreuung)
					SELECT distinct Import_LVA.Lehrer, 
						(SELECT max(Status) FROM  Lehrverpflichtung, `aktuelles Semester` AS aktSemester  
						WHERE DozKurz =  Lehrer 
						AND Lehrverpflichtung.Jahr = aktSemester.Jahr 
						AND Lehrverpflichtung.Semester = aktSemester.Semester
						), 
						Jahr, Semester, Fach, Klasse, 1, 1.0, 1.0, false 
					FROM  Import_LVA, `aktuelles Semester`",
			/*
			 * Tabelle wird zur Zeit nicht gepflegt
			 * 
            "INSERT into Beteiligung (DozentKurz, Status, Jahr, Semester, Fach, Studiengang, T, K, B, istBetreuung)
					SELECT DozKurz, 'Prof', aktSemester.Jahr, aktSemester.Semester, Fach, Studiengang, 1.0, K, 0.3, true
					FROM  Import_Betreuung, `aktuelles Semester` AS aktSemester",
			 */
            /* Korrekturen aus den Departments */
            "delete FROM  Beteiligung using Beteiligung, `aktuelles Semester`AS aktSemester, Import_LVA_GW AS Import
					WHERE Beteiligung.Jahr = aktSemester.Jahr 
						AND Beteiligung.Semester = aktSemester.Semester
						AND Beteiligung.DozentKurz = Import.DozKurz
						AND Beteiligung.Status = Import.Status
						AND Beteiligung.Fach = Import.Fach
						AND Beteiligung.Studiengang = Import.Studg",
            "INSERT into Beteiligung (DozentKurz, Status, Jahr, Semester, Fach, Studiengang, T, K, B, istBetreuung)
					SELECT DozKurz, Status, Jahr, Semester, Fach, Studg, T, K, 1.0, false 
						FROM  Import_LVA_GW, `aktuelles Semester` AS aktSemester 
						WHERE DozKurz <> '' AND Fach <> '' AND Studg <> '' ",
            "delete FROM  Beteiligung using Beteiligung, `aktuelles Semester`AS aktSemester, Import_LVA_OT AS Import
					WHERE Beteiligung.Jahr = aktSemester.Jahr 
						AND Beteiligung.Semester = aktSemester.Semester
						AND Beteiligung.DozentKurz = Import.DozKurz
						AND Beteiligung.Status = Import.Status
						AND Beteiligung.Fach = Import.Fach
						AND Beteiligung.Studiengang = Import.Studg",
            "INSERT into Beteiligung	(DozentKurz, Status, Jahr, Semester, Fach, Studiengang, T, K, B, istBetreuung)
					SELECT DozKurz, Status, Jahr, Semester, Fach, Studg, T, K, 1.0, false 
						FROM  Import_LVA_OT, `aktuelles Semester` AS aktSemester 
						WHERE DozKurz <> '' AND Fach <> '' AND Studg <> '' "
        /*
          INSERT into Beteiligung (DozentKurz, Status, Jahr, Semester, Fach, Studiengang, T, K, B, istBetreuung)
          SELECT Name, `LB`, Jahr, Semester, Fach, Studg, T, K, 1.0, false
          FROM  Import_LVA_LB, `aktuelles Semester` AS aktSemester
          WHERE Name <> '' AND Fach <> '' AND Studg <> ''
         */
        ),
        'Studiengaenge_zuordnen' =>
        array(
            "DELETE FROM Zuordnung 
                USING Zuordnung, `aktuelles Semester`AS aktSemester
                WHERE Zuordnung.Jahr = aktSemester.Jahr
                AND Zuordnung.Semester = aktSemester.Semester",
            "INSERT into Zuordnung (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BBT' FROM  LVA,`aktuelles Semester` 
                    WHERE Studiengang like '%B%' AND not Studiengang like '%S%' AND Studiengang not like '%xB%' AND Studiengang not like '%O%' 
                        AND LVA.Jahr=`aktuelles Semester`.Jahr AND LVA.Semester=`aktuelles Semester`.Semester
                    ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BUT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%U%' AND not Studiengang like '%S%' AND Studiengang not like '%xU%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BVT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%V%' AND not Studiengang like '%S%' AND Studiengang not like '%xV%'
                    AND LVA.Jahr=`aktuelles Semester`.Jahr AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BMT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%M%' 
                    AND not Studiengang like '%S%' 
                    AND Studiengang not like '%xM%' 
                    AND Studiengang<> 'MPH' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BRE' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%R%' 
                    AND Studiengang not like '%xR%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BHC' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%C%' 
                    AND Studiengang not like '%xC%' 
                    AND Studiengang not like '%O%'
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BÖT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%O%' AND Studiengang not like '%xO%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BGS' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%G%' AND Studiengang not like '%SG%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MFS' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S_F%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MHS' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%MHS%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MBT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%B%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MBE' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%M%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MUT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%U%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MVT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%V%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MPB' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%P%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MRE' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%E%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MHS' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S%H%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'GL' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%Le%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'AT' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%AT%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'EH' FROM  LVA,`aktuelles Semester` 
                WHERE (Studiengang like '%EH%' or Studiengang like '%E/H%') 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'STx' FROM  LVA,`aktuelles Semester` 
                WHERE (Studiengang like '%STx%' or Studiengang like '%T/H%') 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MPH' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%MPH%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'BWI' FROM  LVA,`aktuelles Semester` 
                WHERE FachKurz like 'x%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'MWI' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%SW%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang",
            "INSERT into Zuordnung
                (Jahr,Semester,FachKurz,Text,Studiengang)
                SELECT LVA.Jahr,LVA.Semester,LVA.FachKurz, LVA.Studiengang, 'EMMaH' FROM  LVA,`aktuelles Semester` 
                WHERE Studiengang like '%S1A%' 
                    AND LVA.Jahr=`aktuelles Semester`.Jahr 
                    AND LVA.Semester=`aktuelles Semester`.Semester
                ORDER BY FachKurz, Studiengang"		
        ),
        'fehlende_Zuordnung' =>
        array(
            "SELECT `LVA`.*, `Zuordnung`.`Studiengang` 
                FROM `Zuordnung` AS `Zuordnung` 
                    RIGHT OUTER JOIN `LVA` AS `LVA` ON `Zuordnung`.`Jahr` = `LVA`.`Jahr` 
                        AND `Zuordnung`.`Semester` = `LVA`.`Semester` 
                        AND `Zuordnung`.`FachKurz` = `LVA`.`FachKurz` 
                        AND `Zuordnung`.`Text` = `LVA`.`Studiengang` 
                WHERE `Zuordnung`.`Studiengang` IS NULL
                ORDER BY `LVA`.`Jahr` DESC, `LVA`.`Semester` DESC"
        )
    );
}
