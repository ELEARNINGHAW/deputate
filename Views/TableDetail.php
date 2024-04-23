<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();
error_log("TableDetail - Memory usage: " . number_format(memory_get_usage()) . "\n");
#var_dump($_GET);

$table = filter_input(INPUT_GET, 'table');
$relation = filter_input(INPUT_GET, 'relation');
$selection = filter_has_var(INPUT_GET, 'selection') ? explode(',', filter_input(INPUT_GET, 'selection')) : array();
$details = filter_has_var(INPUT_GET, 'details') ? explode('!', filter_input(INPUT_GET, 'details')) : array();
$action = filter_input(INPUT_GET, 'action');

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$dbTable = $_SESSION['tables'][$table];
$dbRelation = $_SESSION['relations'][$relation];

$aktuellesSemester = $_SESSION['aktuellesSemester'];

$nColumns = 3;

//	var_dump($_GET['details']);
//	var_dump($details);
//	var_dump($selection);

function showOptionlist(array $optionlist, $key, $selection = NULL) {
    echo "<td>$key</td><td><select name=$key>";
    foreach ($optionlist as $value) {
        if ($value == trim($selection)) {
            echo "<option selected value=$value> $value";
        } else {
            echo "<option value=$value> $value";
        }
        echo "</option>";
    }
    echo "</select>";
    echo $selection ? "Auswahl: /$selection/" : "" . " </td>";
    return;
}

function showUplinklist(Relation $uplink, $row) {
    global $dbConnect;
    $keys = array_keys($uplink->get_relation());
    $values = array_values($uplink->get_relation());
    $uplinktable = $uplink->get_master()->get_table();
    $keysString = implode(", ", $keys);

    $uplinkQuery = "SELECT $keysString FROM $uplinktable";
//					if ($aktuellesSemester[0] != NULL) {
//						$uplinkQuery .= ' WHERE "Jahr" = '."'".$aktuellesSemester[0]."' AND ".'"Semester" = '."'".$aktuellesSemester[1]."'";
//					}
    $uplinkQuery .= " ORDER BY $keysString";
    $uplinkResult = $dbConnect->query($uplinkQuery);
    $valuesString = implode(", ", $values);
    $valuesStringURL = rawurlencode($valuesString);
    $selection = $row[$values[0]];

    if (count($values) == 1) {  // TODO: mehrwertige Felder
        echo "<td>$valuesString</td><td><select name=$valuesStringURL>";
        foreach ($uplinkResult as $uplinkRow) {
            $optionString = implode(", ", $uplinkRow);
            $optionStringURL = rawurlencode($optionString);
            if ($uplinkRow[$keys[0]] == $row[$values[0]]) { // TODO: mehrwertige Felder
                echo "<option selected value=$optionStringURL> $optionString";
            } else {
                echo "<option value=$optionStringURL> $optionString";
            }
            echo '</option>';
        }
        echo "</select>";
        echo $selection ? "Auswahl: /$selection/" : "" . " </td>";
    }
}

$allRelations = $_SESSION['relations'];
$uplinks = array();
foreach ($allRelations as $name => $uplink) {
    if ($uplink->get_detail() == $dbTable) {
//				echo $name.': '.var_dump($uplink->get_relation()).'<br>';
        $uplinks[] = $uplink;
    }
}


$ID = $dbTable->get_ID();
$query_detail = "SELECT {$dbTable->get_rows()} FROM {$dbTable->get_table()}";
if ($action == 'insert') {
    $query_detail .= " WHERE $ID[0] = NULL";
} else {
    $conditions = array();
    for ($j = 0; $j < count($details); $j++) {
        $conditions[] = $ID[$j] . " = '$details[$j]'";
    }
    if (count($conditions) > 0) {
        $query_detail .= " WHERE " . implode(' AND ', $conditions);
    }
    if ($dbTable->get_order()) {
        $query_detail .= " ORDER BY " . implode(',', $dbTable->get_order());
    }
}

//echo var_dump($query_detail)."<br>";

$result = $dbConnect->query($query_detail);
$row = $result->fetch_array(MYSQLI_ASSOC);

$fields = array();
while ($field_info = $result->fetch_field()) {
    $fields[$field_info->name] = $field_info;
}

switch ($action) {
    case 'insert':
        echo "<p>Bitte füllen Sie die folgenden Felder aus; die Daten werden eingefügt, wenn Sie auf OK klicken.</p>";
        $row = array();
        foreach ($fields as $field_name => $field_type) {
            switch ($field_name) {
                case 'Jahr':
                    $row[$field_name] = $aktuellesSemester[0];
                    break;
                case 'Semester':
                    $row[$field_name] = $aktuellesSemester[1];
                    break;
                default:
                    $row[$field_name] = NULL;
                    break;
            }
        }
        if ($dbRelation) {
            $columns = $dbRelation->get_values();
            if (count($columns) != count($selection)) {
                echo implode(', ', $columns) . ' <> ' . implode(', ', $selection) . '<br>';
                die('columns ' . count($columns) . ' <> selection ' . count($selection) . ' ...schade.');
            }
            foreach ($row as $field_name => $field_value) {
                $j = array_search($field_name, $columns);
                if (is_numeric($j)) {
                    $row[$field_name] = $selection[$j];
                }
            }
        }
        break;
    case 'update':
        echo "<p>Bitte geben Sie Ihre Änderungen in die folgenden Felder ein; die Änderungen werden durchgeführt, wenn Sie auf OK klicken.</p>";
        assert($result->num_rows == 1, "More than one data set.");
        break;
    case 'delete':
        echo "<p>Der folgende Datensatz wird unwiderruflich gelöscht, sobald Sie auf OK Klicken.</p>";
        assert($result->num_rows == 1, "More than one data set.");
        break;
    default:
        die("<p>Unbekannte Aktion $action für diesen Datensatz.</p>");
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <h3><?= $dbTable->get_header() ?></h3>
        <form action="TableAction.php" method="get">
            <table><tbody>
                    <?php
                    $uplinkFields = array();
                    foreach ($uplinks as $uplink) {
                        $values = array_values($uplink->get_relation());
                        if (count($values) == 1) {  // TODO: mehrwertige Felder
                            foreach ($values as $value) {
                                $uplinkFields[$value] = $uplink;
                            }
                        }
                    }

                    $optionlists = $dbTable->get_optionlist();

                    echo '<tr>';

                    $field_number = 1;
                    foreach ($row as $field_name => $field_value) {
                        $field_type = $fields[$field_name]->type;
                        $field_name_URL = rawurlencode($field_name);  // sonst wird das Leerzeichen durch _ ersetzt.
                        if (array_key_exists($field_name, $uplinkFields)) {
                            showUplinklist($uplinkFields[$field_name], $row);
                        } else if ($optionlists != NULL && array_key_exists($field_name, $optionlists)) {
                            showOptionlist($optionlists[$field_name], $field_name, $field_value);
                        } else {
                            switch ($field_type) {
                                case MYSQLI_TYPE_BIT:
                                    echo "<td>$field_name:</td>";
                                    switch ($field_value) {
                                        case 1:
                                            echo "<td><input type='radio' name='$field_name_URL' value='1' checked='checked'> ja ";
                                            echo "<input type='radio' name='$field_name_URL' value='0'> nein </td>";
                                            break;
                                        case 0:
                                            echo "<td><input type='radio' name='$field_name_URL' value='1'> ja ";
                                            echo "<input type='radio' name='$field_name_URL' value='0' checked='checked'> nein </td>";
                                            break;
                                        default:
                                            echo "<td><input type='radio' name='$field_name_URL' value='1'> ja ";
                                            echo "<input type='radio' name='$field_name_URL' value='0'> nein </td>";
                                            break;
                                    }
                                    break;
                                default:
                                    echo "<td>$field_name:</td>";
                                    echo "<td><input type='text' size=50 name='$field_name_URL' value='$field_value'></td>";
                                    break;
                            }
                        }
                        if ($field_number % $nColumns == $nColumns - 1) {
                            echo '</tr> <tr>';
                        }
                        $field_number++;
                    }
                    echo '</tr>';
                    ?>
                </tbody>
            </table>
            <input type="hidden" name="table" value="<?= $table ?>">
            <input type="hidden" name="relation" value="<?= $relation ?>">
            <input type="hidden" name="details" value="<?= filter_input(INPUT_GET, 'details') ?>">
            <input type="hidden" name="selection" value="<?= filter_input(INPUT_GET, 'selection') ?>">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="submit" name="button" value="OK">
        </form>
        <form action="javascript:history.back()">
            <input type="submit"  value="cancel">
        </form>
    </body>
</html>

