<?php
// Auto load classes

function _callback($class) {
  switch($class) {
    case "SiteClass":
      require("$class.class.php");
      break;
    default:
      $class = preg_replace("~^Simple~", "", $class);
      require("database-engines/$class.class.php");
      break;
  }
}

if(spl_autoload_register("_callback") === false) exit("Can't Autoload");

require("database-engines/simple-helper-functions.php");

SimpleErrorClass::setDevelopment(true);

date_default_timezone_set('America/New_York'); // Done here and in dbPdo.class.php constructor.

define("SITELOAD_VERSION", "1.1.1autoload-pdo"); // BLP 2023-08-11 - add static $mysitemap
define("SITECLASS_DIR", __DIR__);

if($_SERVER['HTML_HOST'] == "bartonphillips.org") {
  if(file_exists("/var/www/bartonphillips.org:8000") $port = ":8000";
  return json_decode(stripComments(file_get_contents("https://bartonphillips.org$port/mysitemap.json")));
} else {
  return json_decode(stripComments(file_get_contents(__DIR__ . "/mysitemap.json")));
}

