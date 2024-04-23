<?php
include_once 'Classes/classDBConnect.php';
include_once 'Classes/classTable.php';
include_once 'Classes/classRelation.php';

session_start();


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <body>
        No connection to database <?=$_SESSION['database']?> 
        with port <?=$_SESSION['port']?> established.
    </body>    
</html>

