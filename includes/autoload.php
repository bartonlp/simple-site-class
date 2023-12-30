<?php
// Auto load classes

function callback($class) {
  //echo "1: $class<br>";
  switch($class) {
    case "SimpleSiteClass":
      //echo "2: $class<br>";
      require("$class.php");
      break;
    default:
      $class = preg_replace("~^Simple~", "", $class);
      //echo "3: $class<br>";
      require("database-engines/$class.class.php");
      break;
  }
}

if(spl_autoload_register("callback") === false) exit("Can't Autoload");

require("database-engines/simple-helper-functions.php");

SimpleErrorClass::setDevelopment(true);

date_default_timezone_set('America/New_York'); // Done here and in dbPdo.class.php constructor.

return json_decode(stripComments(file_get_contents("https://bartonphillips.org/mysitemap.json")));


