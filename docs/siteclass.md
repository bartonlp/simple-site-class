# SimpleSiteClass, Database, dbMysqli and dbAbstract Methods (version 1.0.0simple, 3.0.0database, 3.0.0mysql, 1.0.0ab)

---

## SimpleSiteClass, Database and dbAbstract  

While there are a number of methods for each of the major classes there are really only a small handful you will use on a regular bases.  

* SimpleSiteClass constructor -- If you use getPageTopBottom(). This method extends Database;
* SimpleSiteClass::getPageTopBottom() -- Gets the \<head\> and \<footer\>.
* Database constructor -- If you only want to do mysql methods.

I usually have this kind of code at the top of my page:

```php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SimpleSiteClass($_site);
// Set the properties of $S
$S->title = "test page";
$S->banner = "<h1>$S->title</h1>";
$S->css = "h1 { color: red; }";
$S->b_inlineScript = "console.log('this is a test')";
// Get the top and bottom sections.
[$top, $footer] = $S->getPageTopBottom();
// Render the page.
echo <<<EOF
$top
<p>Some test stuff</p>
$footer
EOF;
```

Or like this if you only need to do mysql queries.

```php
$_site = require_once(getenv("SITELOADNAME"));
$S = new Database($_site);
echo "Start<br>";
$S->query("select 'barton' as fname, 'phillips' as lname");
[$fname, $lname] = $S->fetchrow('num');
echo "My name is $fname $lname<br>";
```

There are many more methods in the SimpleSiteClass and Database classes.
Also, the dbMysql is well documented in the dbMysqli.Class.php file.

---

[dbTables](dbTables.html)  
[SimpleSiteClass Methods](siteclass.html)  
[Additional Files](files.html)  
[Index](index.html)

## Contact Me

Barton Phillips : <a href="mailto://bartonphillips@gmail.com">bartonphillips@gmail.com</a>  
Copyright &copy; 2023 Barton Phillips  
Project maintained by [bartonlp](https://github.com/bartonlp)  
Last modified October 29,2023
