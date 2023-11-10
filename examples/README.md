# Examples

**INPORTANT NOTE:** you can disable the log function by either setting ```$_site->noTrack=true``` or adding ```"noTrack":true,``` to the mysitemap.json.
Doing either of those things will keep the **SimpleSiteClass** from using the *logagent* table to log visitors. This will also mean that some of the
example programs will not work.

First you will need to install mysql if you don't already have it. You will need to install the PHP extension for mysql. 
The **SimpleSiteClass** code runs with **PHP 8**, and it may not work with older versions of PHP.

Once you have mysql and the PHP extension you can set up mysql. These examples use a database called *barton* with user *barton*. There is
a file in this directory called *schema.sql*. You can run it in mysql and create the table.

Now that the database is set up you can run the examples.

I have included a couple of examples to show how to use the **SimpleSiteClass**. They are in two seperate directories: *IfComposer* and *IfDownloadedZip*.
If you set up your directory structure as:  
```/var/www/```  
```/var/www/html```  
and used **composer** to create a */var/www/vendor/bartonlp/simple-site-class* directory, you should use *IfComposer* examples.  
If you downloaded the zip file from *https://github.com/bartonlp/simple-site-class* you should use the *IfDownloadedZip* directory. 

In either case, from the *examples* directory in the downloaded location, do:  
```php -S localhost:3000```  
Then open your browser and enter:  
```localhost:3000/IfComposer/example1.php```  
or  
```localhost:3000/IfDownloadedZip/example1.php```

Each example has links to the rest of the examples.

Note, that when running with the PHP server it does not use the apache *.htaccess* file.

You can run both sets of examples from a browser if you have the apache server running on your machine. The *IfComposer* examples should
just work. In the *IfComposer* directory I have a *.htaccess* file which has the *Header set* for the site loader.

The *IfDownloadedZip* examples need to have the *includes* directory, from the zip file, moved to the directory above the *examples* 
directory. Or, you can edit the examples and change the ```__DIR__ . "/../../```, in the 'callback' function at the top of the file, to an absolute path.

Any questions can be directed to Barton Phillips at [bartonphillips@gmail.com](mail-to:bartonphillips@gmail.com)

Have fun

---

[Examples](examplereadme.html)  
[SimpledbTables](dbTables.html)  
[SimpleSiteClass Methods](siteclass.html)  
[Additional Files](files.html)  
[Index](index.html)
## Contact Me
Barton Phillips : [bartonphillips@gmail.com](mailto://bartonphillips@gmail.com)  
Copyright &copy; 2023 Barton Phillips  
Project maintained by [Barton Phillips](https://github.com/bartonlp)  
Last Modified November 10, 2023

