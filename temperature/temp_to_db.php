#!/usr/bin/php

<?php
$mysqli = new mysqli("localhost", "admin", "admin", "meas");

$filename="/sys/kernel/tsic/temp";

if ($fh = fopen($filename, "r")) {
    $value = fread($fh, 20);

    if (!$mysqli->query("INSERT INTO temperature (temp) VALUES ({$value})")) {
    	echo "Inser failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    $mysqli->close();

    fclose($fh);
}
?>