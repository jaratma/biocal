<?php

$date = $_POST['date'];
$seq = $_POST['seq'];
$long = $_POST['long'];
$lat = $_POST['lat'];
$ut = $_POST['ut'];

$swargs = "-edir'./lib/ephe' -b" . $date . " -fl -p" . $seq . " -house" . $long . "," . $lat . ",K -ut" . $ut . " -head";
$sweres = shell_exec("./lib/swetest $swargs");
echo $sweres;

?>
