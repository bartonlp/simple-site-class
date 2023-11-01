<?php

$_site = require_once(getenv("SITELOADNAME"));
$S = new SimpleSiteClass($_site);

// Get the info in $S

$CLASS = print_r($S, true);

$S->title = "Example 1"; // The <title>
$S->banner = "<h1>Example One</h1>"; // This is the banner.
$S->defaultCss = "../css/style.css";
// Add some css.
$S->css =<<<EOF
pre { font-size: 8px; }
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<pre>\$S: $CLASS</pre>
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="../phpinfo.php">PHPINFO</a>
$footer
EOF;
