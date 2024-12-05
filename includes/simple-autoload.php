<?php
// Auto load classes for SiteClass

namespace bartonlp\simple_autoload;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_NOTICE);

define("SITELOAD_VERSION", "1.1.5simple-autoload-pdo"); // BLP 2024-12-05 - changed namespace to simple_autoload and version to simple-autoload-pdo
define("SITECLASS_DIR", __DIR__);

function getSiteloadVersion() {
  return SITELOAD_VERSION;
}

function _callback($class) {
  $class = preg_replace("~^Simple~", "", $class);

  switch($class) {
    case "SiteClass":
      require(__DIR__."/$class.class.php");
      break;
    default:
      require(__DIR__."/database-engines/$class.class.php");
      break;
  }
}

require(__DIR__."/database-engines/simple-helper-functions.php");

if(spl_autoload_register("\bartonlp\simple_autoload\_callback") === false) exit("Can't Autoload");

\SimpleErrorClass::setDevelopment(true);

date_default_timezone_set('America/New_York'); // Done here and in dbPdo.class.php constructor.

$mydir = dirname($_SERVER['SCRIPT_FILENAME']);

if($__VERSION_ONLY) {
  return SITELOAD_VERSION;
} else {
  return findsitemap(); // BLP 2024-10-30 - use ne findsitmap() function borowed from siteload.php with modifications
}

// Find the mysitemap.json. $mydir is a global. This is borrowed from siteload.php with
// modification.

function findsitemap() {
  global $mydir;

  if(file_exists($mydir . "/mysitemap.json")) {
    // BLP 2023-08-17 - use the stripComments() from the helperfunctions.php

    return json_decode(stripComments(file_get_contents($mydir . "/mysitemap.json")));
  } else {
    // If we didn't find the mysitemap.json then have we reached to docroot? Or have we reached the
    // root. We should actually never reach the root.

    if(($_SERVER['DOCUMENT_ROOT'] ?? $S_SERVER['VIRTUALHOST_DOCUMENT_ROOT']) == $mydir || '/' == $mydir) {
      echo <<<EOF
<h1>NO 'mysitemap.json' Found</h1>
<p>To run {$_SERVER['PHP_SELF']} you must have a 'mysitemap.json' somewhere within the Document Root.</p>
EOF;
      error_log("ERROR: siteload.php. No 'mysitemap.json' found in " . getcwd() . " for file {$_SERVER['PHP_SELF']}. DocRoot: $docroot");
      exit();
    }

    // We are not at the root so do $mydir = dirname($mydir). For example if $mydir is

    $mydir = dirname($mydir);

    // Recurse

    return findsitemap();
  }
}
