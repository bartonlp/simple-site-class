<?php
// example using dbTables

function callback($class) {
  switch($class) {
    case "SimpleSiteClass":
      require(__DIR__ . "/../../includes/$class.php");
      break;
    default:
      require(__DIR__ . "/../../includes/database-engines/$class.class.php");
      break;
  }
}

if(spl_autoload_register("callback") === false) exit("Can't Autoload");

ErrorClass::setDevelopment(true);

require(__DIR__ . "/../../includes/database-engines/helper-functions.php");

$_site = json_decode(stripComments(file_get_contents("./mysitemap.json")));

$S = new SimpleSiteClass($_site);
$T = new dbTables($S);

// Pass some info to getPageTopBottom method
$S->title = "Example 5"; // Goes in the <title></title>
$S->banner = "<h1>Example Five</h1>"; // becomes the <header> section
// Add some local css to but a border and padding on the table 
$S->css = <<<EOF
main table * {
  padding: .5em;
  border: 1px solid black;
}
EOF;

$bot = ($S->isBot($S->agent)) ? "true" : "false";

[$top, $footer] = $S->getPageTopBottom();

// create a table from the memberTable
$sql = "select * from $S->masterdb.logagent where site='Examples' order by lasttime limit 5";
$tbl = $T->maketable($sql)[0];

echo <<<EOF
$top
<main>
<p>BOTS=$bot</p>
<h3>Create a html table from the logagent database table</h3>
<p>$sql</p>
<p>The logagent table follows:</p>
$tbl
</main>
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="../phpinfo.php">PHPINFO</a>
$footer
EOF;
