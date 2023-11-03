<?php
// The .htaccess file, in this directory has
// SetEnv SITELOADNAME /var/www/vendor/bartonlp/site-class/includes/siteload.php
// You will need the Apache2 setenvif module.
// This gets the siteload.php from the includes directory.

function callback($class) {
  switch($class) {
    case "SimpleSiteClass":
      require(__DIR__ . "/../../includes/$class.php");
      break;
    default:
      $class = preg_replace("~Simple~", "", $class);
      require(__DIR__ . "/../../includes/database-engines/$class.class.php");
      break;
  }
}

if(spl_autoload_register("callback") === false) exit("Can't Autoload");

require(__DIR__ . "/../../includes/database-engines/simple-helper-functions.php");

SimpleErrorClass::setDevelopment(true);

$_site = json_decode(stripComments(file_get_contents("./mysitemap.json")));

// Get the information from the mysitemap.json in the directory above this one.

$SITE = print_r($_site, true);

// Instantiate the SiteClass from the className in the json file.

$S = new $_site->className($_site); // You could also do 'new SimpleSiteClass($_site);'

// Get the info in $S

$CLASS = print_r($S, true);

$S->title = "Example2"; // The <title>
$S->banner = "<h1>Example Two</h1>"; // This is the banner.
$S->defaultCss = "../css/style.css";
// Add some css.
$S->css =<<<EOF
pre { font-size: 8px; }
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<pre>This is the value of the data in mysitemap.json. \$_site: $SITE</pre>
<pre>This is the value of the instantiated class. \$S: $CLASS</pre>
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="../phpinfo.php">PHPINFO</a>
<hr>
$footer
EOF;
