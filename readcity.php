<?php
require_once("core/CitiesDB.php");
$db = new CitiesDB('/db/cities.db');

if(!empty($_POST["keyword"])) {
    $query ="SELECT Name FROM cities WHERE Name LIKE '" . $_POST["keyword"] . "%' AND Country='". $_POST["country"] . "' LIMIT 0,6";
    $stmt = $db->prepare($query);
    $result= $stmt->execute();
    if (!empty($result)) {
        ?>
        <ul id='country-list'>
        <?php
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            ?>
            <li onclick="selectCity('<?php echo $row['Name'];?>')"><?php echo $row['Name'];?></li>
            <?php } ?>
        </ul>
        <?php } }
?>
