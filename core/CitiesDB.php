<?php
class CitiesDB extends Sqlite3 {
     function __construct($db=NULL) {
          if (!is_null($db)) {
               $this->open('db/cities.db');
          } else {
               $this->open($db);
          }
    }
}
?>
