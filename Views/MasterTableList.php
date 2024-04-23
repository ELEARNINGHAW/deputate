<?php
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';

session_start();

$dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);

$master = filter_input(INPUT_GET, 'master');
$detail = filter_input(INPUT_GET, 'detail');
$relation = filter_input(INPUT_GET, 'relation');

$aktuellesSemester = $_SESSION['aktuellesSemester'];
$dbTable = $_SESSION['tables'][$master];
$dbRelation = $_SESSION['relations'][$relation];
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        $query_list = "SELECT " . $dbTable->get_rows() . " FROM " . $dbTable->get_table();
        if ($dbTable->get_order()) {
            $query_list .= ' ORDER BY ' . implode(',', $dbTable->get_order());
        }

        $result = $dbConnect->query($query_list);
        $ID = $dbTable->get_ID();

//		$nID = array();
//		foreach ($ID as $id) {
//			$nID[] = pg_field_num($result, '"'.$id.'"');
//		}
        ?>
        <p><b><?= $dbTable->get_header() ?>,</b> <?= $result->num_rows; ?> Datensätze gefunden.</p>

        <a id='top'></a>
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
            <tr>
                <th>  </th><th> Nr. </th>
                <?php
                $types = array();
                foreach ($result->fetch_fields() as $field) {
                    echo "<th> $field->name </th>";
                    $types[$field->name] = $field->type;
                }
                ?>
            </tr>
            <?php
            foreach ($result as $j => $row) {
                $values = array();
                $ID = $dbRelation->get_keys();
                if ($ID != NULL) {
                    foreach ($ID as $key) {
                        $values[] = rawurlencode($row[$key]); // für Sonderzeichen in den Werten
                    }
                    $anchor = substr($values[0], 0, 1);
                }
                ?>
                <tr>
                    <td><a href='#top'>🔝</a></td>
                    <?php
                    if (count($values) > 0) {
                        echo "<td><a id=$anchor></a>" .
                        "<a href=TableList.php?table=$detail&selection=" . implode(",", $values) .
                        "&relation=$relation target=unten>$j</a></td>";
                    }

                    foreach ($row as $name => $field) {
                        switch ($types[$name]) {
                            case MYSQLI_TYPE_BIT:
                                if ($field == 0) {
                                    echo ("<td> false </td>");
                                } else if ($field == 1) {
                                    echo ("<td> true </td>");
                                } else {
                                    echo ("<td> - $field - </td>");
                                }
                                break;
                            default:
                                echo ("<td> $field </td>");
                        }
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </table>
    </body>
</html>
