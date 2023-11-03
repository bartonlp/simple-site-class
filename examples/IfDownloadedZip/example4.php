<?php

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
SimpleErrorClass::setDevelopment(true);

$S = new $_site->className($_site);

// The $h object has information that is passed to the getPageTopBottom() function.  
$S->title = "Example 4"; // The <title>
$S->banner = "<h1>Example Four</h1>"; // This is the banner.
$S->defaultCss = "../css/style.css";

// There is more information about the mysql functions at https://bartonlp.github.io/site-class/ or
// in the docs directory.

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

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>Here are some entries from the 'test' table.</p>
$tbl
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="../phpinfo.php">PHPINFO</a>
$footer
EOF;
