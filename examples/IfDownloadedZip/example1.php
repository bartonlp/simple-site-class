<?php
// This example will log into the logagent table unless noTrack is set to true in mysitemap.json

$_site = require_once("special_autoload.php");

$S = new SimpleSiteClass($_site);

// Get the info in $S

$CLASS = print_r($S, true);

$S->title = "Example 1"; // The <title>
$S->banner = "<h1>Example One</h1><p>Using engine=".$S->dbinfo->engine.", database=".$S->dbinfo->database."</p>"; // This is the banner.
$S->defaultCss = "../css/style.css";// Add some css.
$S->css =<<<EOF
pre { font-size: 8px; }
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<pre>This is the value of the instantiated class. \$S: $CLASS</pre>
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
