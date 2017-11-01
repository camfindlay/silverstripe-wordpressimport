#Wordpress Import Module
[![Build Status](https://travis-ci.org/camfindlay/silverstripe-wordpressimport.png?branch=master)](https://travis-ci.org/camfindlay/silverstripe-wordpressimport)

##Maintainer Contacts
* Cam Findlay (Nickname: camfindlay) <cam (at) silverstripe.com>
* Damian Mooyman (Nickname: tractorcow) <damian (dot) mooyman (at) gmail (dot) com>


##Requirements
* mod_rewrite (optional, if you need to cater for old incoming links and are using Apache)
* SilverStripe Framework & CMS 3.1.x
* silverstripe/blog 1.*
* silverstripe/comments

##Installation Instructions

    composer require camfindlay/silverstripe-wordpressimport

This module currently it only works with silverstripe/blog 1.x.

WORKAROUND: If you have installed silverstripe 2.x, downgrade to 1.x:

    composer require silverstripe/blog 1.x

After importing the wordpress .xml file you can safely upgrade to silverstripe/blog 2.x again:

    composer require silverstripe/blog 2.x

NOTE: After upgrading from blog 1.x to 2.x don't forget to run dev/tasks/BlogMigrationTask as indicated in the silverstripe/blog documentation.

WARNING: downgrade was only tested on an empty blog, if you have already entered any content on your blog, this may have some unexpected results!

###Usage Overview

### Images 
Default - It will change any links to uploaded images and 
files in your posts that follow the convention 
"http://yourdomain.com/wp-content/uploads/yyyy/mm/filesname.jpg" 
to "http://yourdomain.com/assets/Uploads/yyyy/mm/filesname.jpg" 
which allows you to migrate you uploaded images 
and files over to SilverStripe assets folder while maintaining 
images in your posts.

To configure your project to handle other file path conventions 
update your mysite/_config/config.yml file with the following regex code:

    BlogImport:
      ImageReplaceRegx: /YOUR REGEX/
  
  for example: 

    BlogImport:
      ImageReplaceRegx: /(http[s?]:\/\/[\w\.\/]+)?\/files\//i
  
The module will check to apply default or config settings  

###Optional Rewriting
Add this in your .htaccess file to port old 
wordpress posts in the form /yyyy/mm/name-of-post/
 to new SilverStripe /blog/name-of-post convention.


    RewriteRule ^[0-9]{4}/[0-9]{2}/(.*)$ /blog/$1 [R,L]


##Known issues:
1. Content can lose a lot of the formatting coming from Wordpress.
1. Perhaps parsing the content through a nl2br might help?
1. Image captions need to be catered for and styled otherwise they end up looking like un-parse shortcodes.
1. Currently only works with silverstripe/blog 1.x.

