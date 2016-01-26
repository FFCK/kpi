=== wp-FileManager ===
Contributors: anantshri, johannesries 
Tags: change, upload, organize, delete, management, file
Requires at least: 3.2
Tested up to: 3.5.1
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

FileManager for WordPress allows you to easily change, delete, organize and upload files.

== Description ==

WP-Filemanager is your one stop solution for all file management work right from the wordpress admin page.   
   
Following features are present as of now.

*   Create File, Folder 
*   Upload ,Download file 
*   View, Edit files 
*   rename an delete files 
*   Configuration menu inside WP-admin panel 

More features to be added soon.

*   Code editor for script fles 
*   WYSIWYG editors for html files 
*   Image editor for image files


== Upgrade Notice ==

This version fixes a security issue as well as multiple long standing usability bugs, errors and warnings. Upgrade immediately. 


== Installation ==

1.  Extract the zip file and just drop the contents in the wp-content/plugins/ directory
2.  Activate the plugin through the \'Plugins\' menu in WordPress. 
3.  Update configuration (most importantly home_directory) and select relevent options.
4.  Check under the admin section last menu in sidebar will be FileManager.


== Frequently Asked Questions ==

For any questions please use the comments function at <a href=\"http://blog.anantshri.info/projects/wp-filemanager/\" target=\"_blank\" title=\"comment\">this page</a>. for <a href=\"&quot;http://johannesries.de/webwork/wp-filemanager/#respond&quot;\" target=\"_blank\" title=\"comment\">old questions refer this </a>

 
== Screenshots ==
1. WP-FileManager home directory view
2. WP-FileManager configuration panel

== Changelog ==
1.4.0

*   Fix of a Security Issue caused by arbitrary file download vulnerability.
*   View and download of file is now restricted inside wp-admin and hence visible only for admin roles.
*   Added index file on all pages to disable accidental diretory browsing.
*   Support is upped to 3.5.1 but minimum is advanced to 3.2 now. No point supporting so old releases.
*   Added protection on all files to protect from direct access.
*   Added code to prevent overall in function names with other plugin.
*   Codepress support removed as wordpress has stopped supporting it. (might add something later)
*   now supports HTML multiupload : patch submitted by thpani : http://profiles.wordpress.org/thpani/
*   rename function issue resolved.


1.3.0

*   Bug Fixes and stable release.
*   Warning's removed.

1.2.8

*   Download and View errors removed and now working fine.

1.2.6   

*   Codepress Implemented for Editing panel.   
*   plugin  works directly from wp-content folder.   
*   Admin page for plugin started construction.   
*   Hide extension option is added in file browser view.   
   
   
1.2.2 

*   fixed the readme   
*   added Czech translation by Petr Sahula


WP-FileManager bases on PHPFM, GION Icons and original work by Joe Schmoe.