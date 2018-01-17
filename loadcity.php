<?php
require_once("core/CitiesDB.php");
$db = new CitiesDB('/db/cities.db');

if(!empty($_POST["keyword"])) {
    $query ="SELECT * FROM cities WHERE Name='" . $_POST["keyword"] . "' AND Country='". $_POST["country"] . "'";
    $stmt = $db->prepare($query);
    $result= $stmt->execute();

    $row = $result->fetchArray(SQLITE3_ASSOC);
    if(!empty($row)) {
        echo json_encode($row);
    } else {
        echo "No records found for ";
        echo $query;
    }

}
?>
