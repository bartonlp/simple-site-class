<header>

# SimpleSiteClass

SimpleSiteClass class mini framework for small websites.
</header>

<div id="banner">
<span id="logo"></span>
<a href="https://github.com/bartonlp/simple-site-class" class="button fork"><strong>View On GitHub</strong></a>
<div class="downloads">
  <span>Downloads:</span>
  <ul>
    <li><a href="https://github.com/bartonlp/simple-site-class/zipball/master" class="button">ZIP</a></li>
    <li><a href="https://github.com/bartonlp/simple-site-class/tarball/master" class="button">TAR</a></li>
  </ul>
</div>
</div><!-- end banner -->

<div class="wrapper">
  <nav>
    <ul></ul>
  </nav>
<section>

# SimpleSiteClass Version 1+
  
SimpleSiteClass class mini framework for simple, small websites.

**SimpleSiteClass** is a PHP mini framework for simple, small websites.
It can be esaly combined with other frameworks or templeting engines if
needed. For small websites I feel that frameworks like Laravel or Meteor
etc. are just too much.

This project has several parts that can function standalone or combined.

- Database.class.php : provides a wrapper for several different database
  engines.
- dbPdo.class.php : has the actual PDO functions.
- dbTables.class.php : uses the functionality of Database.class.php to
  make creating tables easy.
- SimpleSiteClass.class.php : tools for making creating a site a little
  easier. The class provides methods to help with headers, banners,
  footers and more.

The following database engine is provided as the following class:

1.  dbPdo.class.php : (rigorously tested) This is the latest PHP
    version of the PDO database engine and will run MySql and Sqlite3 databases.

## Disclamer

To start, this framework is meant for Linux not Windows. I don't use
Windows, like it or have it, so nothing has been tried on Windows servers. Quite
frankly I don't know why anyone would use a Windows server.

I use Ubuntu 22.04 Linux which is a Debian derivative. I have not tried
this package on any distributions that do not evolve from Debian.

## Install

There are several ways to install this project.

### Download The ZIP File

Download the ZIP file from GitHub. Expand it into the /var/www dirctory
that Apache2 creates. In my servers I have /var/www and then have my
virtual hosts off that directory. That way the directory created when
you unzip the download is easily available to all of my virtual hosts.

### Use Composer

If you have Apache or Nginx installed then you should made your project
root somewhere within your DocumentRoot ('/var/www/html' for Apache2 on
Ubuntu). Or if you want to make a seperate Apache virtual host with a
registered domain name you can make your new project in '/var/www'.

Create a directory `mkdir myproject; cd myproject`, this is your project
root directory. Add the following to 'composer.json', just cut and past:

<div class="sourceCode">

``` sourceCode
      
{
    "require": {
        "bartonlp/simple-site-class": "dev-pdo"
    }
}
      
      
```

</div>

Then run

<div class="sourceCode">

``` sourceCode
composer install
```

</div>

**OR** you can just run

<div class="sourceCode">

``` sourceCode
composer
        require bartonlp/simple-site-class:dev-pdo
```

</div>

which will create the 'composer.json' for you and load the package like
'composer install' above.

In your PHP file add
`$_site = require_once($PATH_TO_VENDOR . '/vendor/bartonlp/simple-site-class/includes/siteload.php');`
where '\$PATH_TO_VENDOR' is the path to the 'vendor' directory like './'
or '../' etc.

## Further Documentation

- [SimpledbTables Documentation](dbTables.html)
- [SimpleSiteClass and SimpleDatabase Methods](siteclass.html)
- [Additional Files User by SimpleSiteClass](files.html)

## Contact me

Barton Phillips : <bartonphillips@gmail.com>  
Copyright © 2025 Barton Phillips  
Last modified January 1, 2025
</section>
<footer>

Project maintained by [Barton Phillips](https://github.com/bartonlp)

<span class="small">Hosted on GitHub Pages — Theme by
[mattgraham](https://twitter.com/michigangraham)</span>
</footer>
</div>
