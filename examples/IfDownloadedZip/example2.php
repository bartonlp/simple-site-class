<?php
// The .htaccess file, in this directory has
// SetEnv SITELOADNAME /var/www/vendor/bartonlp/site-class/includes/siteload.php
// You will need the Apache2 setenvif module.
// This gets the siteload.php from the includes directory.

$_site = require_once("special_autoload.php");

// Get the information from the mysitemap.json in the directory above this one.

$SITE = print_r($_site, true);

$eng = $_site->dbinfo->engine = "sqlite";
$dat = $_site->dbinfo->database = "mysqlite.db";

$S = new SimpleDatabase($_site);

// Get the info in $S

$CLASS = print_r($S, true);

echo <<<EOF
<h1>This uses the SimpleDatabase class</h1><p>Using engine=$eng, database=$dat</p>
<p>Because it does not use SimpleSiteClass it can't use \$top or \$footer.</p>
<hr>
<pre>This is the value of the data in mysitemap.json. \$_site: $SITE</pre>
<pre>This is the value of the instantiated class. \$S: $CLASS</pre>
<hr>
<a href="example1.php">Example1</a><br>
<a href="example2.php">Example2</a><br>
<a href="example3.php">Example3</a><br>
<a href="example4.php">Example4</a><br>
<a href="example5.php">Example5</a><br>
<a href="example6.php">Example6</a><br>
<a href="../phpinfo.php">PHPINFO</a>
<hr>
EOF;
