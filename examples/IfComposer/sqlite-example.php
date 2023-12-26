<?php
// example using SimpledbTables, and isBot() and noTrack

$_site = require_once(getenv("SITELOADNAME"));
SimpleErrorClass::setDevelopment(true);
$_site->dbinfo->engine = "sqlite";
$_site->dbinfo->database = "../IfDownloadedZip/mysqlite.db";

//vardump("dbinfo", $_site->dbinfo);

$S = new SimpleSiteClass($_site);
$T = new SimpledbTables($S);

// Pass some info to getPageTopBottom method
$S->title = "Example 5"; // Goes in the <title></title>
$S->banner = "<h1>Sqlite Example</h1><p>Using engine=".$S->dbinfo->engine.", database=".$S->dbinfo->database."</p>";
$S->defaultCss = "../css/style.css";
// Add some local css to but a border and padding on the table 
$S->css = <<<EOF
main table * {
  padding: .5em;
  border: 1px solid black;
}
EOF;

$bot1 = ($S->isBot($S->agent)) ? "Yes" : "No";
$bot2 = ($S->isBot("I-am-a-BOT")) ? "Yes" : "No";

[$top, $footer] = $S->getPageTopBottom();

$sql = "create table if not exists test (`name` varchar(20), `date` datetime, `lasttime` datetime)";
$S->sql($sql);
for($i=0; $i<5; $i++) {
  $name = "A-name$i";
  $S->sql("insert into test (name, date, lasttime) values('$name', datetime('now'), datetime('now'))");
}

$sql = "select * from test order by lasttime";

// For more information on dbTables you can look at the source or the documentation in the docs
// directory on on line at https://bartonlp.github.io/site-class/

$T = new SimpledbTables($S);
$tbl = $T->maketable($sql, ['attr'=>['id'=>'table1', 'border'=>'1']])[0];

$S->sql("drop table test");

$S->sql("select count from logagent where site='Examples' and ip='$S->ip' and agent='$S->agent' order by lasttime");
$count = $S->fetchrow('num')[0];

echo <<<EOF
$top
<hr>
<main>
<p>Your IP=$S->ip, User Agent String=$S->agent.<br>
Are you a BOT? $bot1.</p>
<p>If your User Agent String were 'I-am-a-BOT' then,<br>
Are you a BOT? $bot2.</p>
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
<a href="sqlite-example.php">Sqlite Example</a><br>
<a href="../phpinfo.php">PHPINFO</a>
<hr>
$footer
EOF;
