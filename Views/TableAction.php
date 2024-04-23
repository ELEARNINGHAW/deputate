<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();
error_log("TableAction - Memory usage: " . number_format(memory_get_usage()) . "\n");

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$action = filter_input(INPUT_GET, 'action');
$table = filter_input(INPUT_GET, 'table');
$details = filter_input(INPUT_GET, 'details');
$selection = filter_input(INPUT_GET, 'selection');
$relation = filter_input(INPUT_GET, 'relation');

$dbTable = $_SESSION['tables'][$table];
$dbRelation = $relation == NULL ? NULL : $_SESSION['relations'][$relation];

$selection_decoded = $selection == NULL ? array() : explode(',', $selection);
$details_decoded = $details == Null ? array() : explode('!', $details);

$fields = $_GET;
unset($fields['action']);
unset($fields['table']);
unset($fields['details']);
unset($fields['relation']);
unset($fields['selection']);
unset($fields['button']);

switch ($action) {
    case 'update':
        foreach ($fields as $key => $value) {
            if ($value == '') {
                $fields[$key] = NULL;
            } else {
                $fields[$key] = rawurldecode($value);
            }
        }
        $assignments = array();
        foreach ($fields as $key => $value) {
            $key_decoded = rawurldecode($key);
            if ($value == NULL) {
                $assignments[] = "$key_decoded=NULL";
            } elseif (is_numeric($value)) {
                $assignments[] = "$key_decoded=$value";
            } else {
                $assignments[] = "$key_decoded='$value'";
            }
        }

        $ID = $dbTable->get_ID();
        $conditions = array();
        for ($j = 0; $j < count($ID); $j++) {
            $conditions[] = "$ID[$j] = '$details_decoded[$j]'";
        }

        $query_update = "UPDATE {$dbTable->get_table()}";
        $query_update .= " SET " . implode(', ', $assignments);
        $query_update .= " WHERE " . implode(' AND ', $conditions);

        $result = $dbConnect->query($query_update);
        $query = $query_update;
        break;
    case 'insert':
        foreach ($fields as $key => $value) {
            if ($value == '' || $value == NULL) {
                unset($fields[$key]);
            } else if (is_numeric($value)) {
                $fields[$key] = $value;
            } else {
                $fields[$key] = "'" . rawurldecode($value) . "'";
            }
        }
        $keys = array_keys($fields);
        $values = array_values($fields);
        $query_insert = "INSERT INTO {$dbTable->get_table()}" .
                "(" . implode(", ", $keys) . " ) VALUES (" . implode(", ", $values) . ")";
        $result = $dbConnect->query($query_insert);
        $query = $query_insert;
        break;
    case 'delete':
        $ID = $dbTable->get_ID();
        $conditions = array();
        for ($j = 0; $j < count($ID); $j++) {
            $conditions[] .= "$ID[$j] = '$details_decoded[$j]'";
        }
        $query_delete = "DELETE FROM " . $dbTable->get_table();
        $query_delete .= " WHERE " . implode(' AND ', $conditions);
        $result = $dbConnect->query($query_delete);
        $query = $query_delete;
        break;
    default:
        die('unbekannt Aktion' . $action . '<br>');
}

$redirect = "TableList.php?table=$table&relation=$relation&selection=" . rawurlencode($selection);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>	
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Message</title>
    </head>
    <body>
        <?php
        if ($result) {
            ?>
			<h3>Datensatz erfolgreich geändert.</h3>
			<p>Query: <?= $query ?></p>
            <p>Redirecting to: <?= $redirect ?></p>
            <form action="<?= $redirect ?>" method="post">
                <input type="submit" value="weiter...">
            </form>
            <?php
        } else {
			$error = $dbConnect->error;
            ?>
			<h3>Fehler beim Ändern des Datensatzes!</h3>
			<h4>Änderungen wurden nicht übernommen.</h4>
			<p>Query: <?= $query ?></p>
			<p>Error: <?= $error ?></p>
            <p>Redirecting to: <?= $redirect ?></p>
            <form action="<?= $redirect ?>" method="post">
                <input type="submit" value="Eingabe verwerfen">
            </form>
            <form action="javascript:history.back()">
                <input type="submit" value="nochmal eingeben">
            </form>
            <?php
        }
        ?>
    </body>
</html>
