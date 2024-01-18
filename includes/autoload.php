<?php
// Auto load classes
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_NOTICE);

function _callback($class) {
  $class = preg_replace("~^Simple~", "", $class);

  switch($class) {
    case "SiteClass":
      require("$class.class.php");
      break;
    default:
      require("database-engines/$class.class.php");
      break;
  }
}

if(spl_autoload_register("_callback") === false) exit("Can't Autoload");

require("database-engines/simple-helper-functions.php");

SimpleErrorClass::setDevelopment(true);

date_default_timezone_set('America/New_York'); // Done here and in dbPdo.class.php constructor.

define("SITELOAD_VERSION", "1.1.2autoload-pdo"); // BLP 2024-01-14 - fix HTML to HTTP_HOST.
define("SITECLASS_DIR", __DIR__);

if($_SERVER['HTTP_HOST'] == "bartonphillips.org") {
  if(file_exists("/var/www/bartonphillips.org:8000")) $port = ":8000";
  return json_decode(stripComments(file_get_contents("https://bartonphillips.org$port/mysitemap.json")));
} else {
  return json_decode(stripComments(file_get_contents(__DIR__ . "/mysitemap.json")));
}

