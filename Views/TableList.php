<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();
error_log("TableList - Memory usage: " . number_format(memory_get_usage()) . "\n");

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$docTable = filter_input(INPUT_GET, 'table');
$relation = filter_input(INPUT_GET, 'relation');
$selection = filter_input(INPUT_GET, 'selection') == NULL ? array() : explode(',', filter_input(INPUT_GET, 'selection'));
$columns = filter_input(INPUT_GET, 'columns') == NULL ? array() : explode(',', filter_input(INPUT_GET, 'columns'));
$details = filter_input(INPUT_GET, 'details')==NULL? array(): explode('!', filter_input(INPUT_GET, 'details'));

$aktuellesSemester = $_SESSION['aktuellesSemester'];

$dbTable = $_SESSION['tables'][$docTable];
$dbRelation = $_SESSION['relations'][$relation];
$ID = $dbTable->get_ID();
$where = $dbTable->get_where();
$order = $dbTable->get_order();

$query_list = "SELECT {$dbTable->get_rows()} FROM {$dbTable->get_table()}";
if ($relation) {
    $columns = $dbRelation->get_values();

    // Notausgang: aktuelles Semester gibt es zur Zeit nur in Sekundärtabellen..... :-))
    if (count($aktuellesSemester) > 0) {
        $where[] = "Jahr = $aktuellesSemester[0]";
        $where[] = "Semester = '$aktuellesSemester[1]'";
    }
}

if (count($columns) > 0) {
    if (count($columns) != count($selection)) {
        echo implode(', ', $columns) . " <> " . implode(', ', $selection) . "<br>";
        die(count($columns) . ' <> ' . count($selection) . ' ...schade.');
    }
    for ($j = 0; $j < count($columns); $j++) {
        $where[] = "$columns[$j] = '$selection[$j]'";
    }
}

if (count($where) > 0) {
    $query_list .= " WHERE " . implode(' AND ', $where);
}
if (count($order) > 0) {
    $query_list .= " ORDER BY " . implode(',', $order);
}

$result = $dbConnect->query($query_list);
if (!$result) {
    die('no result!');
}
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> title </title>
    </head>
    <body><a id='top'></a>
        <table>
            <tr>
                <?php if (count($ID) > 0) { ?>
                    <td>
                        <form action="TableDetail.php" method="get">
                            <input type="submit" name="insert" value="Datensatz einfügen">
                            <input type="hidden" name="action" value="insert">
                            <input type="hidden" name="table" value="<?= $docTable ?>">
                            <input type="hidden" name="relation" value="<?= $relation ?>">
                            <input type="hidden" name="selection" value="<?= implode(',', $selection) ?>">
                            <input type="hidden" name="details" value="<?= implode(',', $details) ?>">
                        </form>
					</td>
                <?php } ?>
                <td>
                    <form action="PrintTableList.php" method="get" target="_blank">
                        <input type="submit" name="print" value="Tabelle drucken">
                        <input type="hidden" name="table" value="<?= $docTable ?>">
                        <input type="hidden" name="relation" value="<?= $relation ?>">
                        <input type="hidden" name="selection" value="<?= implode(',', $selection) ?>">
                    </form>
				</td>
        </table>
        <p><b> <?= $dbTable->get_header() ?>,</b> <?= $result->num_rows; ?> Datensätze gefunden.</p>

        <a href="#A">A</a>
        <a href="#B">B</a>
        <a href="#C">C</a>
        <a href="#D">D</a>
        <a href="#E">E</a>
        <a href="#F">F</a>
        <a href="#G">G</a>
        <a href="#H">H</a>
        <a href="#I">I</a>
        <a href="#J">J</a>
        <a href="#K">K</a>
        <a href="#L">L</a>
        <a href="#M">M</a>
        <a href="#N">N</a>
        <a href="#O">O</a>
        <a href="#P">P</a>
        <a href="#Q">Q</a>
        <a href="#R">R</a>
        <a href="#S">S</a>
        <a href="#T">T</a>
        <a href="#U">U</a>
        <a href="#V">V</a>
        <a href="#W">W</a>
        <a href="#X">X</a>
        <a href="#Y">Y</a>
        <a href="#Z">Z</a>

        <table border="1" width="100%">
            <?php
            echo "<tr>";
            echo "<th> </th> <th> Nr. </th>";
            if (count($ID) > 0) {
                echo "<th></th><th></th>";
            }
            $types = array();
            foreach ($result->fetch_fields() as $field) {
                echo "<th> $field->name </th>";
                $types[$field->name] = $field->type;
            }
            echo "</tr>";

            foreach ($result as $j => $row) {
                echo "<tr>";
                echo "<td><a href='#top'>🔝</a></td>";
                $values = array();
                if (count($ID) > 0) {
                    foreach ($ID as $key) {
                        $values[] = $row[$key];
                    }
                    $anchor = substr($values[0], 0, 1);
                    echo "<td><a id= $anchor>$j</a></td>";
                } else {
                    echo "<td> $j </td>";
                }
                if (count($values) > 0) {
                    $selection = filter_input(INPUT_GET, 'selection');
                    echo "<td><a href=TableDetail.php?table=$docTable" .
                    "&relation=$relation" .
                    "&selection=" . rawurlencode($selection) .
                    "&details=" . rawurlencode(implode('!', $values)) .
                    "&action=update>✏</a></td>";
                    echo "<td><a href=TableDetail.php?table=$docTable" .
                    "&relation=$relation" .
                    "&selection=" . rawurldecode($selection) .
                    "&details=" . rawurlencode(implode('!', $values)) .
                    "&action=delete>✄</a></td>";
                }

                foreach ($row as $name => $field) {
                    switch ($types[$name]) {
                        case MYSQLI_TYPE_BIT:
                            if ($field == 0) {
                                echo "<td> false </td>";
                            } else if ($field == 1) {
                                echo "<td> true </td>";
                            } else {
                                echo "<td> - $field - </td>";
                            }
                            break;
                        default:
                            echo "<td> $field </td>";
                    }
                }
                echo "</tr>";
            }
            ?>
        </table>
        <a id="ende"></a>
    </body>
</html>

