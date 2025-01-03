<?php
// Function to search for the mysitemap.json
// We pass the $mydir to the function. This is from 'SCRIPT_FILENAME' which has the absolute path
// to the target with the full DOCUMENT_ROOT plus the directory path from the docroot to the
// target.
// For example DOCUMENT_ROOT + /path/target
// So we take this and if we do not find the files at first we do a $mysite = dirname($mysite).

namespace bartonlp\simple_siteload;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_NOTICE);

define("SITELOAD_VERSION", "1.0.4simple-siteload-pdo"); // BLP 2024-12-05 - changed namespace to simple_autoload and version to simple-autoload-pdo
define("SITECLASS_DIR", __DIR__);

require_once("/var/www/vendor/autoload.php");
require_once("/var/www/vendor/bartonlp/simple-site-class/includes/database-engines/simple-helper-functions.php");

// BLP 2024-12-05 - Add getSiteVersion() from simple-autoload.php.
   
function getSiteloadVersion() {
  return SITELOAD_VERSION;
}

// If we only want the version info $__VERSION is set. We do this in whatisloaded.class.php.
// It can also be done to get the versions of beacon.php and tracker.php.

if($__VERSION_ONLY) return SITELOAD_VERSION;

if(!class_exists("getinfo")) {
  class getinfo {
    private $docroot;
    private $mydir;
    private $_site;

    public function __construct() {
      //error_log("siteload simple-site-class");

      // Now check to see if we have a DOCUMENT_ROOT or VIRTUALHOST_DOCUMENT_ROOT.
      // If we DON't we will use PWD which should be and if SCRIPT_FILENAME is not dot (.)
      // then we add it to PWD.
      // This is for CLI files. For regular PHP via apache we just use the ROOT.

      if(!$_SERVER['DOCUMENT_ROOT'] && !$_SERVER['VIRTUALHOST_DOCUMENT_ROOT']) {
        // This is a CLI program
        // Is SCRIPT_FILENAME an absolute path?

        if(strpos($_SERVER['SCRIPT_FILENAME'], "/") === 0) {
          // First character is a / so absoulte path
          $mydir = dirname($_SERVER['SCRIPT_FILENAME']);
        } else {
          // SCRIPT_FILENAME is NOT an absolute path
          // Use PWD and then look at SCRIPT_FILENAME
          $mydir = $_SERVER['PWD'];
          // If SCRIPT_FILENAME start with a dot (.) then we are in the target dir so do nothing.
          // Else we use the dirname() and append it to mydir.

          if(($x = dirname($_SERVER['SCRIPT_FILENAME'])) != '.') {
            $mydir .= "/$x";
          }
        }
      } else {
        // Normal apache program
        // The SCRIPT_FILENAME is always an absolute path

        $mydir = dirname($_SERVER['SCRIPT_FILENAME']);
        $this->docroot = $_SERVER['DOCUMENT_ROOT'] ?? $S_SERVER['VIRTUALHOST_DOCUMENT_ROOT'];
      }
      $this->mydir = $mydir;

      $this->_site = json_decode($this->findsitemap());
      
      // Set the siteloadVersion and siteClassDir

      $this->_site->siteloadVersion = SITELOAD_VERSION;
      $this->_site->siteClassDir = SITECLASS_DIR;

      if($mode = $this->_site->errorMode) {
        if($mode->development === true) { // true we are in development
          \SimpleErrorClass::setDevelopment(true); // Do this first because it sets NoEmailErrs to true.
        }

        // If this is true set it if it is false unset it but if it is null don't do anything! 

        if($mode->noEmail === true) { // true means let there be emails
          \SimpleErrorClass::setNoEmail(true); 
        } elseif($mode->noEmail === false) { // only if false, a null does nothing here.
          \SimpleErrorClass::setNoEmail(false); 
        } 

        if($mode->noHtml === true) { 
          \SimpleErrorClass::setNoHtml(true); // NO HTML Tags
        }

        if($mode->noOutput === true) {
          \SimpleErrorClass::setNoOutput(true);
        }

        if($mode->noBacktrace === true) {
          \SimpleErrorClass::setNobacktrace(true);
        }

        if($mode->errLast == true) {
          \SimpleErrorClass::setErrlast(true);
        }
      }
    }

    // getVersion
    
    public static function getVersion() {
      return SITELOAD_VERSION;
    }

    // Private findsitemap
    
    private function findsitemap() {
      $mydir = $this->mydir;

      if(file_exists($mydir . "/mysitemap.json")) {
        // BLP 2023-08-17 - use the stripComments() from the helperfunctions.php
        
        return stripComments(file_get_contents($mydir . "/mysitemap.json"));
      } else {
        // If we didn't find the mysitemap.json then have we reached to docroot? Or have we reached the
        // root. We should actually never reach the root.

        if($this->docroot == $mydir || '/' == $mydir) {
          error_log("ERROR: siteload.php. No 'mysitemap.json' found in " . getcwd() . " for file {$_SERVER['PHP_SELF']}, DocRoot: $mydir");
          return json_encode(["nomysitemap"=>"NO_MYSITEMAP"]); // BLP 2023-10-29 - We could not find a mysitemap.json
        }

        // We are not at the root so do $mydir = dirname($mydir). For example if $mydir is

        $this->mydir = dirname($mydir);

        // Recurse

        return $this->findsitemap();
      }
    }

    // Public getSite
    // This is the getter for the private $this->_site;

    public function getSite() {
      return $this->_site;
    }
  }
}

$_site = (new getinfo())->getSite();

// BLP 2022-01-12 -- If $_site is NULL that means the json_decode() failed.

if(is_null($_site)) {
  echo <<<EOF
<h1>JSON ENCODING ERROR</h1>
<p>Check mysitemap.json for an ilegal construct!</p>
EOF;

  // BLP 2022-01-12 -- try to make error log message more helpful
  
  if(is_string($ret)) {
    error_log("ERROR: siteload.php. Return form findsitemap() is a string. However json_decode() returned NULL. JSON ENCODING ERROR");
  } else {
    error_log("ERROR: siteload.php. Return from findsitemap() is NOT a string. SOMETHING WENT WRONG SOMEWHERE");
  }
  exit();
}

// BLP 2023-10-29 - If we did not find mysitemap.json then $_site is a very bair bones stdClass
// with: ['nositemap'=>'NO_SITEMAP', 'siteloadVersion'=SITELOAD_VERSION,
// 'siteClassDir'=>SITECLASS_DIR];

return $_site;
