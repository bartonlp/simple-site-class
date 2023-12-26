<?php

$_site = require_once("special_autoload.php");

$S = new SimpleSiteClass($_site);

// The $h object has information that is passed to the getPageTopBottom() function.  
$S->title = "Example 4"; // The <title>
$S->banner = "<h1>Example Four</h1><p>Using engine=".$S->dbinfo->engine.", database=".$S->dbinfo->database."</p>"; // This is the banner.
$S->defaultCss = "../css/style.css";

$sql = "create table if not exists $S->masterdb.test (`name` varchar(20), `date` datetime, `lasttime` datetime)";
$S->sql($sql);
for($i=0; $i<5; $i++) {
  $name = "A-name$i";
  $S->sql("insert into $S->masterdb.test (name, date, lasttime) values('$name', now(), now())");
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
<p>We are using the SimpledbTables class.</p>
$tbl
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="example6.php">Example6</a><br>
<a href="../phpinfo.php">PHPINFO</a>
$footer
EOF;
