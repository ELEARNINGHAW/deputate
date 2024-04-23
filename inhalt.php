<?php
include_once 'Classes/classDBConnect.php';
include_once 'Classes/classTable.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$inputRequest = filter_input(INPUT_POST, 'Semester');
#$inputRequest = $_REQUEST['Semester'];
if ($inputRequest != NULL) {
    $aktuellesSemester = explode(',', $inputRequest);
} else {
    $aktuellesSemester = array();
}
$_SESSION['aktuellesSemester'] = $aktuellesSemester;
$self = filter_input(INPUT_SERVER, 'PHP_SELF');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">

<html>
    <head>
        <title>Inhaltsverzeichnis</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <table>
            <tr>
                <td><form action="import.php" method="post">
                        <input type="submit" value="<<- Importe">
                    </form>
                </td>
                <td><form action="reports.php" method="post">
                        <input type="submit" value="Berichte ->>">
                    </form>
                </td>
            </tr>
        </table>
        <form action="<?= $self ?>" method="post">
            <b>Semester: </b> <?= implode(' ', $aktuellesSemester) ?> <br>
            <select name="Semester" size="1">
                <option value=""></option>
                <?php
                $dbTable = $_SESSION['tables']['Semester'];
                $query_string = "SELECT " . $dbTable->get_rows() . " FROM " . $dbTable->get_table() . " ORDER BY " . implode(', ', $dbTable->get_order());
                $result = $dbConnect->query($query_string);
                if ($result) {
                    while ($row = $result->fetch_array()) {
                        echo "<option ";
                        if (count($aktuellesSemester) > 0) {
                            if ($aktuellesSemester[0] == $row['Jahr'] && $aktuellesSemester[1] == $row['Semester']) {
                                echo "selected ";
                            }
                        }
                        echo "value=" . $row['Jahr'] . "," . $row['Semester'] . ">" . $row['KurzText'] . "</option>";
                    }
                }
                ?>
            </select>
            <input type="submit" value="übernehmen">
        </form>
        <table>
            <tr><td><b>Dozent</b></td></tr>
            <tr><td><a href="Views/TableList.php?table=Dozent" 
                       target="rechts">Dozent</a></td></tr>
            <tr><td><a href="Views/TableReport.php?table=Dozent" 
                       target="rechts">Dozenten mit Reports</a></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=Dozent&detail=Beteiligung&relation=DozentBeteiligung"
                       target="rechts">Dozenten mit Beteiligungen</a></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=Dozent&detail=Auslastung&relation=DozentAuslastung"
                       target="rechts">Dozenten mit Entlastungen</a></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=Dozent&detail=Lehrverpflichtung&relation=DozentLehrverpflichtung"
                       target="rechts">Dozenten mit Lehrverpflichtungen</a></td></tr>
            <!--
            <tr><td><a href="Views/MasterDetailFrame.php?master=Dozent&detail=Saldo&relation=DozentSaldo3"
                       target="rechts">Dozenten mit Saldo</a></td></tr>
            -->

            <tr><td><b>Bilanz</b></td></tr>
            <tr><td><a href="Views/TableList.php?table=Bilanz"
                       target="rechts">Bilanz</a></td></tr>
            <!--
            <tr><td><a href="Views/MasterDetailFrame.php?master=Bilanz&detail=Saldo3&relation=BilanzSaldo3" 
                       target="rechts">Bilanzen und Salden</a></td></tr>
            -->
            <tr><td><a href="Views/MasterDetailFrame.php?master=Bilanz&detail=Zeitkonto&relation=BilanzZeitkonto"
                       target="rechts">Bilanzen und Zeitkonten</a></td></tr>

            <tr><td><b>Fach</b></td></tr>
            <tr><td><a href="Views/TableList.php?table=Fach" 
                       target="rechts">Fach</a></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=Fach&detail=LVA&relation=FachLVA"
                       target="rechts">Fächer mit LVA</a></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=Fach&detail=Beteiligung&relation=FachBeteiligung"
                       target="rechts">Fächer mit Beteiligungen</a></td></tr>

            <tr><td><b>Lehrveranstaltung</b></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=LVA&detail=Beteiligung&relation=LVABeteiligung"
                       target="rechts">LVA mit Beteiligungen</a></td></tr>
            <tr><td><a href="Views/MasterDetailFrame.php?master=LVA&detail=Zuordnung&relation=LVAZuordnung"
                       target="rechts">LVA mit Zuordnungen</a></td></tr>

            <tr><td><b>Sonstiges</b></td></tr>
            <tr><td><a href="Views/TableList.php?table=Lehrverpflichtung" 
                       target="rechts">Lehrverpflichtung</a></td></tr>
            <tr><td><a href="Views/TableList.php?table=Semester" 
                       target="rechts">Semester</a></td></tr>
            <tr><td><a href="Views/TableList.php?table=Studiengang" 
                       target="rechts">Studiengang</a></td></tr>
            <tr><td><a href="Views/TableList.php?table=Auslastungsgrund" 
                       target="rechts">Entlastungsgrund</a></td></tr>

            <tr><td><b>Berechnungen</b></td></tr>
            <tr><td> <a href="Berechnungen/startBerechnungen.php" 
                        target="rechts">Salden und Zeitkonto erstellen</a></td></tr>

        </table>
        <table>
            <tr><td><b>local User: </b></td><td><?= filter_input(INPUT_SERVER, 'User') ?></td></tr>
            <tr><td><b>DB User: </b></td><td><?= $dbConnect->get_databaseUName() ?></td></tr>
            <tr><td><b>Database: </b></td><td><?= $dbConnect->get_databaseName() ?></td></tr>
            <tr><td><b>Host: </b></td><td><?= $dbConnect->get_databaseHost() ?></td></tr>
            <tr><td><b>Port: </b></td><td><?= $dbConnect->get_databasePort() ?></td></tr>
            <tr><td><b>Server: </b></td><td><?= filter_input(INPUT_SERVER, 'SERVER_NAME') ?></td></tr>
            <tr><td><b>Client: </b></td><td><?= filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?></td></tr>
        </table>
    </body>
</html>
