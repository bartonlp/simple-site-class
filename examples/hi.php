<?php
// This file will not run if you downloaded the zip file.
// This should run if you are using my standard configuration and then used composer to install the
// classes.
// My standard configuration is:
// /var/www
// /var/www/vendor created with composer.
// /var/www/html
// You moved the examples directory to /var/www/html

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->title = "Example HI";
$S->banner = "<h1>I am HI</h1>";
[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<a href="hi.php">Hi</a><br>
<a href="phpinfo.php">PHPINFO</a>
$footer
EOF;

