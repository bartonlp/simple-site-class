<?php
// example using dbTables

$_site = require_once(getenv("SITELOADNAME"));
$S = new SimpleSiteClass($_site);
$T = new dbTables($S);

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

$bot = $S->isBot($S->agent) ? "Yes" : "No";

[$top, $footer] = $S->getPageTopBottom();

// create a table from the memberTable
$sql = "select * from $S->masterdb.logagent where site='Examples' order by lasttime limit 5";
$tbl = $T->maketable($sql)[0];

echo <<<EOF
$top
<main>
<p>Are you a BOT? $bot.</p>
<h3>Create a html table from the tracker database table</h3>
<p>$sql</p>
<p>The tracker table follows:</p>
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
