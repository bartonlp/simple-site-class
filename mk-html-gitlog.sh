#!/bin/bash
# !!! You need pandoc: sudo apt-get install pandoc

css='
<style>
div.sourceCode {
  background-color: #EEF3E2;
  border-left: 10px solid gray;
  padding-left: 5px;
}
code {
  background-color: #EEF3E2;
}
</style>
';

# Make .html files from .md files
pagetitle="Main Readme file";
/usr/bin/pandoc -Vpagetitle="$pagetitle" -Vmath="$css" -s -f gfm -t html5 README.md -o README.html

# Create 'git log >~/www/gitlog
git log --all > ~/www/gitlog

# now move into the docs directory and do those html files

cd docs

pagetitle="dbTables";
/usr/bin/pandoc -Vpagetitle="$pagetitle" -Vmath="$css" -s -f gfm -t html5 dbTables.md -o dbTables.html
pagetitle="SiteClass Methods";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" -Vmath="$css" -s siteclass.md -o siteclass.html
pagetitle="Additional Files";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" -Vmath="$css" -s files.md -o files.html
pagetitle="examples";
/usr/bin/pandoc -f gfm -t html5 -Vpagetitle="$pagetitle" -Vmath="$css" -s ../examples/README.md -o examples.html


