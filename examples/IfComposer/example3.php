<?php
/*
CREATE TABLE `logagent` (
  `site` varchar(25) NOT NULL DEFAULT '',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `agent` text NOT NULL,
  `finger` varchar(50) DEFAULT NULL,
  `count` int DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`site`,`ip`,`agent`(254)),
  KEY `ip` (`ip`),
  KEY `site` (`site`),
  KEY `created` (`created`),
  KEY `lasttime` (`lasttime`),
  KEY `agent` (`agent`(254))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 PACK_KEYS=1;
*/

$_site = require_once(getenv("SITELOADNAME"));
$S = new SimpleSiteClass($_site);

$S->title = "Example 3"; // The <title>
$S->banner = "<h1>Example Three</h1>"; // This is the banner.
$S->defaultCss = "../css/style.css";

// There is more information about the mysql functions at https://bartonlp.github.io/site-class/ or
// in the docs directory.

$sql = "select site, ip, agent, finger, count, created, lasttime from $S->masterdb.logagent where lasttime>=current_date() and site='Examples'";
$S->sql($sql);
while([$site, $ip, $agent, $finger, $count, $created, $lasttime] = $S->fetchrow('num')) {
  $rows .= "<tr><td>$site</td><td>$ip</td><td>$agent</td><td>$finger</td><td>$count</td><td>$created</td><td>$lasttime</td></tr>";
}

// Now here is an easier way using dbTables.
// For more information on dbTables you can look at the source or the documentation in the docs
// directory on on line at https://bartonlp.github.io/site-class/

$T = new dbTables($S);
$tbl = $T->maketable($sql, ['attr'=>['id'=>'table1', 'border'=>'1']])[0];

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<p>Here are the entries from the 'logagent' table for today.</p>
<table border='1'>
<thead>
<tr><th>site</th><th>ip</th><th>agent</th><th>finger</th><th>count</th><th>created</th><th>lasttime</th></tr>
</thead>
<tbody>
$rows
</tbody>
</table>

<p>Same table but with dbTables</p>
$tbl
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
