=== Easy FAQs ===
Contributors: richardgabriel, ghuger
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=V7HR8DP4EJSYN
Tags: faqs, faq widget, faq list, faq submission, frequently asked questions, knowledgebase
Requires at least: 3.0.1
Tested up to: 4.4.2
Stable tag: 1.13.7
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy FAQs is a simple-to-use plugin for adding FAQs (Frequently Asked Questions) to your WordPress Theme, using a shortcode or a widget.

== Description ==

Easy FAQs is an easy-to-use plugin that allows users to add FAQs (Frequently Asked Questions) to the sidebar, as a widget, or to embed them into a Page or Post using the shortcode.  The Easy FAQs plugin also allows you to insert a list of all FAQs or output a Single FAQ. Easy FAQs allows you to include an image with each FAQ - this is a great feature for adding a photo of the FAQ author or other related imagery.

= Easy FAQs is a great plugin for: =
* Adding an FAQ to Your Sidebar
* Adding an FAQ to Your Page
* Outputting a List of FAQs
* Displaying an Image with a FAQ
* Custom Options Allow You to Link Your FAQs to a Custom Page, Such As Linking to your FAQs Page from a Single FAQ
* Its easy to use interface allows you to manage, edit, create, and delete FAQs with no new knowledge

Easy FAQs includes options to set the URL of the Read More Link, whether or not to display the FAQ Image, Custom Excerpt options, and more!  You can set the URL of the FAQs read more links for many purposes - such as directing visitors to the product info page that the faq is about.  Showing an Image next to a FAQ is a great tool!

= Upgrade To Easy FAQs PRO For Advanced Features and Email Support =

Easy FAQs Pro adds awesome new features like accordion-style FAQs and a Question Submission Form, so you can receive new questions right on your website. You'll also have access to one-on-one email support from our staff (just email hello@goldplugins.com anytime).

[Upgrade To Easy FAQs Pro](https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=wp&utm_campaign=desc_upgrade)

= Why Should I Add Frequently Asked Questions (FAQs) to my Website? =

Adding a Frequently Asked Questions (FAQs) section to your website, either as a full page or even just as a sidebar widget, can be a crucial tool to help your customers get the answers they need, fast.  A well written Frequently Asked Questions page can give users the answers they need without reading tedious documentation.  Organized into a Knowledgebase, Frequently Asked Questions can be a one-stop-shop for staff, users, and anyone else who may need answers.

= Premium Support =

The GoldPlugins team does not provide direct support for the Easy FAQs plugin on the WordPress.org forums. One on one email support is available to people who have purchased Easy FAQs Pro only. Easy FAQs Pro also includes accordion-style FAQs and other advanced features including a Question Submission Form. You should [upgrade today!](https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=wp&utm_campaign=desc_upgrade2 "Upgrade to Easy FAQs Pro")

[Upgrade To Easy FAQs Pro](https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=wp&utm_campaign=desc_upgrade2)

= Get Started Using Easy FAQs Today! =

The Easy FAQs plugin is the easiest way to start adding your customer's FAQs, right now!  Click the Download button now to get started.  The Easy FAQs plugin will inherit the styling from your Theme - just install and get to work adding your faqs!

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the contents of `/easy-faqs/` to the `/wp-content/plugins/` directory
2. Activate Easy FAQs through the 'Plugins' menu in WordPress
3. Visit this address for information on how to configure the plugin: https://goldplugins.com/documentation/easy-faqs-documentation/

### Adding a New FAQ ###

Adding a New FAQ is easy!  There are 3 ways to start adding a new FAQ

**How to Add a New FAQ**

1. Click on "+ New" -> FAQ, from the Admin Bar _or_
2. Click on "Add New FAQ" from the Menu Bar in the WordPress Admin _or_
3. Click on "Add New FAQ" from the top of the list of FAQs, if you're viewing them all.

**New FAQ Content**

You have a few things to pay attention to:

* **FAQ Title:** this content will be displayed above your FAQ - typically this is the Question that is being Answered.
* **FAQ Body:** this is the content of your FAQ.  This will be output and displayed below the FAQ Title.
* **Featured Image:** This image is shown to the left of the FAQ's title, as a 50px by 50px thumbnail.
* **FAQ Category:** This is the Category that the FAQ belongs to, if desired.  You can use this to output FAQs from specific categories only, with the shortcode.

### Editing a FAQ ###

 **This is as easy as adding a New FAQ!**

1. Click on "FAQs" in the Admin Menu.
2. Hover over the FAQ you want to Edit and click "Edit".
3. Change the fields to the desired content and click "Update".

### Deleting a FAQ ###

 **This is as easy as adding a New FAQ!**

1. Click on "FAQs" in the Admin Menu.
2. Hover over the FAQ you want to Delete and click "Delete".
  
  **You can also change the Status of a FAQ, if you want to keep it on file.**

### Outputting FAQs ###

* To output a Single FAQ, place the shortcode ```[single_faq id="1"]``` in the desired area of the Page or Post Content.  If you view the List of FAQs, you can find the FAQ ID in the Table.
* To output a list of All FAQs, place the shortcode ```[faqs]``` in the desired area of the Page or Post Content.  To display more than one faq, use the shortcode ```[faqs count='3']```, where count is the number of faqs you want displayed.  To display FAQs from a Category, use the shortcode ```[faqs category='your_slug']```.  To control the Order of the FAQs, use the attribute ```[faqs order='ASC']```.  To control the Order By parameter of the FAQs, use the attribute ```[faqs orderby='title']```.  You can find more details [here](https://goldplugins.com/documentation/easy-faqs-documentation/ "Easy FAQs Documentation")
* To display the Featured Image along with FAQs, use the attribute ```show_thumbs='1'```.  This applies to both the single and list shortcodes.
* To control the wording of the Read More Link, use the attribute ```read_more_link_text='Your Text Here'```.  This applies to both the single and list shortcodes.
* To control the destination of the Read More Link, use the attribute ```read_more_link='http://www.yahoo.com'```.  This applies to both the single and list shortcodes.  **NOTE:** be sure you include http:// in your link.
* To output a list of FAQs, grouped by category, use the shortcode ```[faqs-by-category]```.
* To output a list of FAQS, with long answers shortened and linked to full length answers, use the shortcode ```[faqs-by-category]```.
* To output a FAQ in the Sidebar, use the Widgets section of your WordPress Theme, Accessible on the Appearance Menu in the WordPress Admin.  Use the Drop Down menu to select which FAQ is displayed.
* To output a Accordion Style FAQ List, use the shortcode ```[faqs style=accordion]```.  If you want the Accordion List to start with everything collapsed, use the shortcode ```[faqs style=accordion-collapsed]```.  The same attributes, such as count and category, apply from above.  **NOTE:** This feature requires the [Pro version of Easy FAQs](https://goldplugins.com/our-plugins/easy-faqs-details/ "Easy FAQs Pro")
* To output a QuickLinks menu above your FAQ List, use the shortcode attribute```quicklinks=1```.  To control the number of columns quicklinks are dividied into, use the shortcode attribute ```colcount=2```.    To control the scroll offset, in the case that a menu or other element is affecting your scroll position, use the attribute ```[faqs quicklinks=1 scroll_offset=20]```, where 20 means 20 pixels.  This attribute works with both the regular and the accordion style FAQs list.  **NOTE:** This feature requires the [Pro version of Easy FAQs](https://goldplugins.com/our-plugins/easy-faqs-details/ "Easy FAQs Pro")

### Front End FAQ Submission ###

* **NOTE:** This feature requires the [Pro version of Easy FAQs](https://goldplugins.com/our-plugins/easy-faqs-details/ "Easy FAQs Pro")
* Add the shortcode ```[submit_faq]``` to the area of the page you want your form on.
* Any submissions will be added to your FAQs list, on the back end.  Only FAQs that you choose to publish will be displayed publicly.
* E-mail notifications are sent to the site admin on new Frequently Asked Question (FAQ) submission.
* Front End Submission supports Really Simple Captcha for SPAM prevention.  Enable from the Options screen.

### Options ###
* To control the destination of the "Read More" link, set the path in the FAQs Read More Link field.
* To control the wording of the "Read More" link, set the wording in the Read More Link Text field.
* To display any Featured Images that you have attached to your FAQs, check the box next to Show FAQ Image.
* To add any Custom CSS, to further modify the output of the plugin, input the CSS in the textarea labeled Custom CSS.  You do not need to include the opening or closing <style> tags, treat it like you're inside a CSS file.

== Frequently Asked Questions ==

= Help!  I need more information! =

OK!  We have a great page with some helpful information here: https://goldplugins.com/documentation/easy-faqs-documentation/

= Hey!  How do I allow my visitors to submit faqs? =

Great question!  With the Pro version of the plugin, you can do this with our front end form that is output with a shortcode!  FAQs will show up as pending on the Dashboard, for admin moderation.  Visit here to purchase the Pro version: https://goldplugins.com/our-plugins/easy-faqs-details/

= Ack!  This FAQs Plugin is too easy to use! Will you make it more complicated? =

Never!  Easy is in our name!  If by complicated you mean new and easy to use features, there are definitely some on the horizon!

== Screenshots ==

1. This is the Add New FAQ Page.
2. This is the List of FAQs - from here you can Edit or Delete a FAQ.
3. This is the Easy FAQs Categories Page.
4. This is the Easy FAQs Basic Settings Page.
5. This is the Easy FAQs Shortcode Generator.
6. This is the Easy Import & Export Page.
7. This is the Easy Recent Searches Page.
8. This is the Easy FAQs Help & Instructions Page.
9. This is the Easy FAQs List Widget.
10. This is the Easy Single FAQ Widget.

== Changelog ==
= 1.13.7 =
* Addresses issue with Genesis Framework causing double FAQ titles to be displayed.

= 1.13.6 =
* Address improperly formatted accordion and ordering attributes in shortcodes generated by editor widgets.

= 1.13.5 =
* Admin style update.

= 1.13.4 =
* Adds Editor Widget Shortcode Generators.
* Minor fixes, cleanup.

= 1.13.3 =
* Fix various PHP notices in the admin on higher versions of PHP
* Adds more translatable strings.

= 1.13.2 =
* Compatible with WP 4.4.2
* Adds Custom Excerpt Link options and allows linking to Single FAQs

= 1.13.1 =
* Cleans up options screen.
* Fixes issue with FAQ post data not being reset causing errors on some sites.

= 1.13 =
* Adds 100+ new themes and a theme selection screen with Preview.

= 1.12 =
* Adds Options to control Labels, Description, and display of FAQ submission form fields.

= 1.11.3 =
* Adds category_order and category_orderby params to faqs_by_category shortcode

= 1.11.2 =
* Updates widgets in preparation of WordPress 4.3.

= 1.11.1 =
* Updates Widget CSS and Forms to improve Usability.
* Updates Accordion CSS to prevent flash of FAQs as JS loads.
* Updates Widgets to use PHP5 style constructors, in preparation for WP 4.3.

= 1.11 =
* Feature: Adds FAQs Search, List, and Submit Widgets.

= 1.10 =
* Feature: Update to be translatable.

= 1.9.4 =
* Fix: Single widget now allows you to pick from more than just 10 FAQs
* Fix: Update the CSV Import / Export to function better, security patch

= 1.9.3 =
* Fix: Respect HTTPS when enqueuing fonts, fix broken HTML in admin, clearly label Pro features

= 1.9.2 =
* Fix: Adds scroll_offset attribute to quicklinks, handles bookmarks correctly

= 1.9.1 =
* Fix: Fix geolocation on search log for situations such as CloudFlare.

= 1.9 =
* Pro Feature: Adds FAQ Search Log Functionality

= 1.8 =
* Pro Feature: Adds FAQ Search Functionality
* Pro Feature: Adds FAQ Import and Export Functionality

= 1.7.6 =
* Fix: Addresses issue where rewrite rules weren't automatically flushed, causing 404s when viewing single FAQs.

= 1.7.5 =
* Fix: Address issue with captcha functionality on question submission form.

= 1.7.4 =
* Fix: Address some PHP notices from Bikeshed.

= 1.7.3 =
* Fix: address 400 error generated when a Google Font wasn't selected in the Typography options.
* Update compatibility to WP 4.1.1

= 1.7.2 =
* Addresses user reported bugs.

= 1.7.1 =
* Adds 'Settings' and 'Support' links to plugin page

= 1.7 =
* Adds shortcode generator.

= 1.6 =
* Adds typography options for Questions, Answers and Read More Links
* Adds filters

= 1.5.1 =
* Fix: address JS error.

= 1.5 =
* Feature: Adds Return To Top function on QuickLinks output.
* Updates Compatibility to WordPress 4.0.1
* Various Bug Fixes.

= 1.4.4 =
* Feature: Adds ability to specify categories when using the [faqs-by-category] shortcode
* Fix: addresses issue with single_faq shortcode ignoring style attribute

= 1.4.3 =
* Fix: address some HTML validation issues.

= 1.4.2 =
* Fix: address issue with final quicklinks column not being closed if the last column didn't have 5 questions.

= 1.4.1 =
* Fix: address issue with incorrect number of columns of quicklinks being displayed.

= 1.4 =
* Adds support for new Pro feature, Quicklinks.
* Cleans up some functions.

= 1.3.4 =
* Updates readme.txt

= 1.3.3 =
* Updates compatibility to WP 4.0.

= 1.3.2  =
* Minor registration update.
* Updates compatibility to WP 3.9.2.

= 1.3.1 =
* Minor Feature: output notice if Captcha isn't functional.

= 1.3 =
* Feature: adds support for captcha on submission form.
* Feature: adds support for notification e-mails on submission form.

= 1.2.3.1 =
* Fix: address issues with FAQ submission form.

= 1.2.3 =
* Update: adds ability to default FAQ accordion to being collapsed.
* Feature: adds category list shortcode.

= 1.2.2.2 =
* Fix: Address issue with content translation plugins and double content output.
* Fix: Update compatibility to WP 3.8.2

= 1.2.2.1 =
* Fix: Address issue with taxonomy and CPT slug conflicting when trying to view single faqs (ref #2189, thanks mralexweber!)

= 1.2.2 =
* Feature: extends shortcode to allow full control over output, including overriding global options such as image display, read more text, and read more display.
* Feature: extends widget to allow full control over output, including overriding global options such as image display, read more text, and read more display.
* Fix: address issue with single FAQ shortcode not outputting the correct FAQ.
* Fix: address issue with FAQ Read More link not displaying when global option is set.
* Fix: addresses CSS issues preventing certain styles from being applied correctly.

= 1.2.1 =
* Feature: adds ability to control Order and OrderBy parameters via the shortcode.

= 1.2 =
* Fix: update deprecated functions in widgets.
* Feature: Adds accordion style FAQ lists to Pro version.
* Update: fixes poorly structured HTML and some validation issues.

= 1.1 =
* Feature: Adds FAQ Categories and ability to list FAQs on a per category basis.

= 1.0 =
* Released!

== Upgrade Notice ==

* 1.13.7: Genesis compatibility update.