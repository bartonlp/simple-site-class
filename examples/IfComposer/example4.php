<?php

$_site = require_once(getenv("SITELOADNAME"));
$S = new SimpleSiteClass($_site);

// The $h object has information that is passed to the getPageTopBottom() function.  
$S->title = "Example 4"; // The <title>
$S->banner = "<h1>Example Four</h1>"; // This is the banner.
$S->defaultCss = "../css/style.css";

// Lets do some database stuff

$sql = "select * from $S->masterdb.logagent where lasttime>=now() - interval 5 minute and site='Examples' order by lasttime";

// For more information on dbTables you can look at the source or the documentation in the docs
// directory on on line at https://bartonlp.github.io/site-class/

$T = new dbTables($S);
$tbl = $T->maketable($sql, ['attr'=>['id'=>'table1', 'border'=>'1']])[0];

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>Here are some entries from the 'tracker' table for the last 5 minutes for the 'Examples' site.</p>
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
