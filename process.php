<?php
declare(strict_types=1);
include_once 'core/CitiesDB.php';
include_once 'core/BioEngine.php';
$db = new CitiesDB('/db/cities.db');

define('ACTIONS', array(
        'calc' => array(
                'object' => 'Bioengine',
                'method' => 'processForm',
                'header' => 'Location: .' ),
            )
    );

$use_array = ACTIONS[$_POST['action']];
$obj = new $use_array['object']($db);
$method = $use_array['method'];

$msg = $obj->$method();

if (is_array($msg)) {
    echo json_encode($msg);
    //header($use_array['header']);
} else {
    die ($msg);
}

//if ( TRUE === $msg = $obj->$method()) {
//        header($use_array['header']);
//        exit;
//} else {
//    var_dump($msg);
//}

//function __autoload($class_name)
//{
//    $filename = 'core/'
//        . $class_name . '.php';
//    if ( file_exists($filename) ) {
//        include_once $filename;
//    }
//}
?>
