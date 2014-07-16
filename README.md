Profis CMS
==========

Profis CMS (Content Management System) – is an open source tool for website information management. It is really very easy to use. Software is based on Ext JS (Sencha) and supports all types of databases (MySQL, PostgreSQL, ...). 

Installation
==========

Open your website http://www.your-site.com/ and follow instructions.

Server Requirements
==========

Software

    Apache
    PHP (version 5.2+)
    MySQL (version 5.1+)

Apache Modules

    mod_rewrite (for .htaccess to work)

PHP Extensions

    mbstring
    mcrypt
    gd (version 2+)
    bcmath
    PDO
    iconv
	filter

MySQL Configuration

	Query buffering must not be disabled.

Troubleshooting
==========
1. If you get ".htaccess: order not allowed here" error, try to comment "order allow,deny" inside "<Files PC_errors.txt>" directive.
