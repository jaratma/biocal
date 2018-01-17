<?php
declare(strict_types=1);
include 'CitiesDB.php';

$db = new CitiesDB();
if (!$db) {
     echo $db->lastErrorMsg();
}
?>
