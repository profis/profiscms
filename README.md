Profis CMS
==========

Profis CMS (Content Management System) – is an open source tool for website information management. It is really very easy to use. Software is based on Ext JS (Sencha) and supports all types of databases (MySQL, PostgreSQL, ...). 

Installation
==========

1. Extract ProfisCMS4.zip to the document root of your webpage.
2. After files extraction you'll find "database.sql" file in /install/ directory. This is the database structure that you need to put in your database. The least painful way to do this is using PhpMyAdmin application as described: log in to your database using PhpMyAdmin after that select "Import" tab, then simply choose database.sql file and click "Go". If no errors were shown you can go to the other step.
3. Copy /install/config.default.php to config.php in document root and open it for editing. You must change your database connection settings there. Other settings are optional, but you can also change your timezone, default administrator's language, salt and some other settings. 
4. Everything's done. You can now open Profis CMS by entering http://www.your-site.com/admin/ in your browser and log in using default administrator settings:
Login: admin
Password: admin
Don't forget to change your password or anyone else could easily spell out the default one! You can do this by opening "Users & Permissions" dialog in the menu in the top right corner of the CMS window and entering new password.

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

MySQL Configuration

	Query buffering must not be disabled.
