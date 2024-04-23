<?php

function getAktuellesSemester() {
    $dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);
    $result = $dbConnect->query("SELECT * FROM `aktuelles Semester`");

    if ($result->num_rows != 1) {
        die("$result->num_rows aktuelle Semester gefunden!");
    }
    return $result->fetch_array(MYSQLI_ASSOC);
}

function getAktuellesVorsemester() {
    $dbConnect = new DBConnect($_SESSION['userID'], $_SESSION['passwd'], $_SESSION['database']);
    $result = $dbConnect->query("SELECT * FROM `aktuelles Vorsemester`");

    if ($result->num_rows != 1) {
        die("$result->num_rows aktuelle Vorsemester gefunden!");
    }
    return $result->fetch_array(MYSQLI_ASSOC);
}
