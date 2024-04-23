<?php
include_once 'Classes/classTable.php';
include_once 'Classes/classRelation.php';
include_once 'spezielle Funktionen/setTables.php';
include_once 'spezielle Funktionen/setRelations.php';
include_once 'spezielle Funktionen/setScripts.php';

session_start();

$_SESSION['userID'] = filter_input(INPUT_ENV, 'USER');
$_SESSION['aktuellesSemester'] = NULL;
$_SESSION['attribute'] = 'aktuell';
$_SESSION['deadline'] = '';

setTables();
setRelations();
setScripts();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Deputatsverwaltung Login </title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <h1>Please, log in...</h1>
        <form action="frame.php" method="post">
            <input type="text" name="user" value=""><br>
            <input type="password" name="passwd" value=""><br>
            <label> Produktion <input type="radio" name="db" value="Prod" checked></label>
            <label> Test <input type="radio" name="db" value="Test"></label><br>
            <input type="submit" value="OK">
        </form>
    </body>

</html>
