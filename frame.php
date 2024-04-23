<?php
include_once 'Classes/classDBConnect.php';

session_start();

$_SESSION['userID'] = filter_input(INPUT_POST, 'user');
$_SESSION['passwd'] = filter_input(INPUT_POST, 'passwd');
$_SESSION['database'] = filter_input(INPUT_POST, 'db');
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Deputatsverwaltung</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <frameset rows="90,*">
        <frame src="header.html" name="oben" />
        <frameset cols=20%,*">
            <frame src="inhalt.php" name="links"/>
            <frame name="rechts"/>
        </frameset>
    </frameset>
</html>
