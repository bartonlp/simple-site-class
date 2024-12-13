# Example Read Me

**INPORTANT NOTE:** you can disable the log function by either setting ```$_site->noTrack=true``` or adding ```"noTrack":true,``` to the mysitemap.json.
Doing either of those things will keep the **SimpleSiteClass** from using the *logagent* table to log visitors. 
This will also mean that example2.php programs will not work.

There are two example programs in the *examples* directory. *example1.php* is a simple program that shows how to get information from the *mysitemap.json*
file, instantiate the SimpleSiteClass framework, and set several variables like *title*, *banner* and *css*;
then get the page top and bottom and finally *echo* the page. The file is pretty well commented and there are several things you can change.

The second example, *exampe2.php* is a little more complicated as it uses a database that collects some limited user information. 
The database is named *barton* and its table is *logagent*. The example shows how to query the database using an auxiliary class *SimpledbTables* to 
make the task of building a table simpler.

Both examples can be run via ```php -S localhost:3000```. This should be run from the *simple-site-class* directory, 
that is the directory above the *examples* directory. Once you have run the **PHP** program, you can use a browser and goto 
```localhost:3000/examples/example1.exe?home=/home/<your home directory>;```. Replace *&lt;your home directory&gt;* with the name of the directory
you loaded *SimpleSiteClass* into. 

You can install the repository either by going to GitHub ([https://github.com/bartonlp/simple-site-class](https://github.com/bartonlp/simple-site-class)) and
getting the path from the **Code** button and then clone the repository onto your system by doing ```git clone <repository>;```. Replace
*&lt;repository&gt;* with the info from the **Code** button.

You can also download a *zip* file from the repository.

If you have an Apache server running on your system, you can use *composer* to fetch the repository into a directory called *vendor*. 
The repository will be at *vendor/bartonlp/simple-site-class*. Copy the *examples* directory to your running domain directory.
I like to put the *vendor* directory at */var/www*. Apache creates this directory and usually puts an *html* directory under it for your web code.
Put the *examples* directory in *html* (or whatever you have called your website).

The two example programs are set up to use **PHP PDO** with a *sqlite* database. The database will be called *barton* in the *examples* directory.
You should have **PHP 8+**, along with the *pdo-sqlite* and *pdo* extensions. If you would like to use **MySql** instead, comment out the lines
in the example programs that read ```$_site->dbinfo->engine = "sqlite"```.

Both example programs require the file *mysitemap.json* which has information about the framework. The file is pretty well commented as it is not
a real **JSON** file. I remove the comments in the loading process and then do a *json_decode()*, so the **JSON** code after the comment removal 
must be valid **JSON** code or the loader will output an error message.

There is a file called *schema.sql* in the *examples* directory that can be used to create the *barton* database and the *logagent* table.

If you have comments or need help, email me at **bartonphillips@gmail.com**.

Have fun.

---

[SimpledbTables](dbTables.html)  
[SimpleSiteClass Methods](siteclass.html)  
[Additional Files](files.html)  
[Index](index.html)

## Contact Me

Barton Phillips : <a href="mailto://bartonphillips@gmail.com">bartonphillips@gmail.com</a>  
Copyright &copy; 2025 Barton Phillips  
Project maintained by [Barton Phillips](https://github.com/bartonlp)  
Last modified January 1, 2025
