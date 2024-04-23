<?php
#
#	Parameter (post):
#	attribute:	Attribut für Ausdrucke (vorläufug, aktuell, korrigiert)
#	deadline:	Terminangabe für die Rückgabe von Dokumenten
#

include_once 'Classes/classDBConnect.php';
include_once 'Classes/classTable.php';

session_start();

// $dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database'])

$aktuellesSemester = $_SESSION['aktuellesSemester'];
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Import</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <form action="inhalt.php" method="post">
            <input type="hidden" name="Semester" value="<?= implode(',', $_SESSION['aktuellesSemester']) ?>">
            <input type="submit" value="Daten ->>">
        </form>
        <hr>
        <table>
            <tr><td><b>Tabellen</b></td></tr>
            <tr><td><a href="Views/TableList.php?table=Import_LVA_GW" 
                       target="rechts">Import LVA GW</a></td>
            </tr>
            <tr><td><a href="Views/TableList.php?table=Import_LVA_OT" 
                       target="rechts">Import LVA ÖT</a></td>
            </tr>
            <tr><td><a href="Views/TableList.php?table=Import_LVA_LB" 
                       target="rechts">Import LVA Lehrbeauftragte</a></td>
            </tr>
            <tr><td><a href="Views/TableList.php?table=Import_LVA" 
                       target="rechts">Import LVA Profs und WiMis</a></td>
            </tr>
            <tr><td><b>Skripte</b></td></tr>
            <tr><td>Neues Semester</td></tr>
            <tr><td><a href="Views/ScriptWithNoResult.php?querylist=copy_Lehrverpflichtung" 
                       target="rechts">Lehrverpflichtungen übernehmen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithNoResult.php?querylist=copy_Auslastung" 
                       target="rechts">Lehrermäßigungen übernehmen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=check_Konsistenz" 
                       target="rechts">Importe auf Konsistenz prüfen</a></td>
            </tr>
            <tr><td>Doppelte oder fehlende Daten für LVA</td></tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=vorhandene_LVA" 
                       target="rechts">Importe auf vorhandene LVA prüfen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=fehlende_Faecher" 
                       target="rechts">Importe auf fehlende Fächer prüfen prüfen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=vorhandene_Beteiligungen" 
                       target="rechts">Importe auf vorhandene Beteiligungen prüfen</a></td>
            </tr>
            <tr><td>LVA importieren und SWS vortragen</td></tr>
            <tr><td><a href="Views/ScriptWithNoResult.php?querylist=LVA_importieren"
                       target="rechts">LVA importieren</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithNoResult.php?querylist=SWS_vortragen" 
                       target="rechts">SWS in LVA übernehmen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=SWS_checken" 
                       target="rechts">LVA auf SWS prüfen</a></td>
            </tr>
            <tr><td>Doppelte oder fehlende Daten für Beteiligungen</td></tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=fehlende_LVA"
                       target="rechts">Importe auf fehlende LVA prüfen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=fehlende_Lehrverpflichtungen"
                       target="rechts">Importe auf fehlende Lehrverpflichtungen prüfen</a></td>
            </tr>
            <tr><td>Beteiligungen importieren</td></tr>
            <tr><td><a href="Views/ScriptWithNoResult.php?querylist=Beteiligungen_importieren"
                       target="rechts">Beteiligungen importieren</a></td>
            </tr>
            <tr><td>Zuordnung zu Studiengängen</td></tr>
            <tr><td><a href="Views/ScriptWithNoResult.php?querylist=Studiengaenge_zuordnen"
                       target="rechts">Studiengänge zuordnen</a></td>
            </tr>
            <tr><td><a href="Views/ScriptWithTables.php?querylist=fehlende_Zuordnung"
                       target="rechts">LVA auf fehlende Zuordnungen prüfen</a></td>
            </tr>
        </table>
    </body>
</html>
