# SimpleSiteClass (version 1+), SimpleDatabase, SimpledbPdo Methods   

---

Note: There are still two branches in the repository on GitHub. The master branch and the pdo branch. You should use the pdo branch.
The master branch still has mysqli code and is not as up-to-date as the pdo branch.

## SimpleSiteClass, SimpleDatabase  

While there are a number of methods for each of the major classes there are really only a small handful you will use on a regular bases.  

* SimpleSiteClass constructor -- If you use getPageTopBottom(). This method extends Database;
* SimpleSiteClass::getPageTopBottom() -- Gets the \<head\> and \<footer\>.
* SimpleDatabase constructor 

I usually have this kind of code at the top of my page:

```php
$_site = require_once getenv("SITELOADNAME");
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

Or like this if you only need to do dbPdo queries.

```php
$_site = require_once getenv("SITELOADNAME");
$S = new SimpleDatabase($_site);
echo "Start<br>";
$S->sql("select 'barton' as fname, 'phillips' as lname from barton.members");
[$fname, $lname] = $S->fetchrow('num');
echo "My name is $fname $lname<br>";
```

There are many more methods in the SimpleSiteClass and SimpleDatabase classes.
Also, the dbPdo is well documented in the SimpledbMysqli.Class.php file.

---

[Example Readme](examplereadme.html)  
[SimpledbTables](dbTables.html)  
[Additional Files](files.html)  
[Index](index.html)

## Contact Me

Barton Phillips : <a href="mailto://bartonphillips@gmail.com">bartonphillips@gmail.com</a>  
Copyright &copy; 2025 Barton Phillips  
Project maintained by [Barton Phillips](https://github.com/bartonlp)  
Last modified January 1, 2025
