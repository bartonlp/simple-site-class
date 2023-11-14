<?php
// example using SimpledbTables, and isBot() and noTrack

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

SimpleErrorClass::setDevelopment(true);

require(__DIR__ . "/../../includes/database-engines/simple-helper-functions.php");

$_site = json_decode(stripComments(file_get_contents("./mysitemap.json")));

// *******************************************************************
// Refresh this page and see if the logagent table count row changes.
// Then comment the line out and the count will change.
// You can also change the "noTrack": false to true.
// Note: the value in $_site takes presidence over the mysitemap.json.
// *******************************************************************
$_site->noTrack = true; // Disable logagent tracking
// *******************************************************************

$S = new SimpleSiteClass($_site);
$T = new SimpledbTables($S);

// Pass some info to getPageTopBottom method
$S->title = "Example 5"; // Goes in the <title></title>
$S->banner = "<h1>Example Five</h1>"; // becomes the <header> section
$S->defaultCss = "../css/style.css";
// Add some local css to but a border and padding on the table 
$S->css = <<<EOF
main table * {
  padding: .5em;
  border: 1px solid black;
}
EOF;

$bot = ($S->isBot($S->agent)) ? "Yes" : "No";

[$top, $footer] = $S->getPageTopBottom();

$sql = "create table if not exists $S->masterdb.test (`name` varchar(20), `date` datetime, `lasttime` datetime)";
$S->query($sql);
for($i=0; $i<5; $i++) {
  $name = "A-name$i";
  $S->query("insert into $S->masterdb.test (name, date, lasttime) values('$name', now(), now())");
}

$sql = "select * from $S->masterdb.test order by lasttime";

// For more information on dbTables you can look at the source or the documentation in the docs
// directory on on line at https://bartonlp.github.io/site-class/

$T = new SimpledbTables($S);
$tbl = $T->maketable($sql, ['attr'=>['id'=>'table1', 'border'=>'1']])[0];

$S->query("drop table $S->masterdb.test");

$S->query("select count from $S->masterdb.logagent where site='Examples' and ip='$S->ip' and agent='$S->agent' order by lasttime");
$count = $S->fetchrow('num')[0];

echo <<<EOF
$top
<hr>
<main>
<p>Are you a BOTS? $bot.</p>
<p>The value of logarent count=$count</p>
<h3>Create a html table from the logagent database table</h3>
<p>$sql</p>
<p>The test table follows:</p>
$tbl
</main>
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="example6.php">Example6</a><br>
<a href="../phpinfo.php">PHPINFO</a>
<hr>
$footer
EOF;
