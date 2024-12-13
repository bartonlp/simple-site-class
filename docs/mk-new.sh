#!/bin/bash
# !!! You need pandoc: sudo apt-get install pandoc

# Make .html files from .md files
echo "index";
pagetitle="index";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" --css=stylesheets/styles.css --standalone index.md -o index.html

echo "dbTables";
pagetitle="dbTables";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" --css=stylesheets/pandoc.css --standalone dbTables.md -o dbTables.html
echo "simplesiteclass";
pagetitle="SimpleSiteClass Methods";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" --css=stylesheets/pandoc.css --standalone siteclass.md -o siteclass.html
pagetitle="Additional Files";
echo "files";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" --css=stylesheets/pandoc.css --standalone files.md -o files.html
pagetitle="examplereadme";
echo "examplereadme";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" --css=stylesheets/pandoc.css --standalone examplereadme.md -o examplereadme.html
