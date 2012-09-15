What is jelix-techinfo-debugbar ?
==============================

This project is a plugin for [Jelix](http://jelix.org) PHP framework. It allows you to log some technical informations for your page (memory usage and limit, phpinfo() stuff, gJConfig content ...).

This is an debugbar plugin.



Installation
============

Under Jelix default configuration, create an "debugbar" directory in your project's "plugins" directory.
Clone this repository in that directory.
Add "techinfoslog" to your "plugins" entry in the "debugbar" section of your config file (defaultconfig.ini.php or entry point's config.ini.php).

Note that you should have your app plugin directory in your modulesPath (defaultconfig.ini.php or entry point's config.ini.php) to get it working.
The value should be at least :

    modulesPath="app:modules/"




