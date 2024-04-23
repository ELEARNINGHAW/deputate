<?php
session_start();

$id = filter_input(INPUT_GET, 'id');
$page = filter_input(INPUT_GET, 'page');
$self = filter_input(INPUT_SERVER, 'PHP_SELF');
error_log($id);
assert($_SESSION[$id], 'No data in _SESSION!');
$data = $_SESSION[$id];
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ReportEnvelope</title>
    </head>
    <table width="100%">
        <tr>
            <td>
                <form action="<?= $self ?>" method="get">
                    Dokument: 
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <?php
                    foreach ($data as $i => $buf) {
                        error_log($i);
                        echo '<input type="submit" name="page" value="' . $i . '"';
                    }
                    ?>
                </form>
            </td>
        </tr>
        <tr>
            <td>
                <iframe height="900pt" width="100%" src="ReportSingle.php?id=<?= $id ?>&page=<?= $page ?>"/>
            </td>
        </tr>
</html>
