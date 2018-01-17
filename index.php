<?php
declare(strict_types=1);

include_once 'core/init.php';
include_once 'core/Bioengine.php';
include 'db/countrycodes.php';

if (!isset($isocodes)) {
     echo "Isocodes not set";
}

$engine = new Bioengine($db, $isocodes);
$css_files = array("style-1.1.css");
$page_title = "Auragrama Personalizado";

include 'assets/common/header.php';

echo $engine->displayForm();

include 'assets/common/footer.php';
?>
