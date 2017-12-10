=== mTouch Quiz ===
Contributors: gmichaelguy
Donate link: http://gmichaelguy.com/quizplugin/go/donate/
Tags: quiz, question, answer, test, touch, education, learning, elearning
Requires at least: 3.0
Tested up to: 4.4
Stable tag: 3.1.3

mTouch Quiz lets you add quizzes to your site. This plugin was designed with learning, touch friendliness and versatility in mind.


== Description ==

Create a multiple choice quiz (or exam). This plugin was written with learning and mobility in mind.  The quiz interface is touch friendly. You can: specify hints based on answer selection; give a detailed explanation of the solution; choose multiple correct answers; specify when the correct answers are displayed; specify if a question may be attempted only once or many times; specify point values for each question; include customized start and finish screens; randomly order questions and/or answers; and more.  This plugin was built by pillaging the Quizzin plugin written by Binny V A, but please do not blame him for my ruining his plugin!

== Installation ==
Best way to install is via the "Add New Plugins" feature inside your WordPress dashboard. Or...

1. Download the zipped file.
1. Extract and upload the contents of the folder to /wp-contents/plugins/ folder
1. Go to the Plugin management page of WordPress admin section and enable the 'mTouch Quiz' plugin
1. Go to the mTouch Quiz Management page (in a new group) to create or edit the quiz.
1. Add the shortcode (and any options) [mtouchquiz #] to a post to display your quiz!

== Frequently Asked Questions ==

= Where can I find the most up to date version of the FAQ? =
You can find the most up to date version at the [ homepage of mTouch Quiz Plugin](http://gmichaelguy.com/quizplugin "mTouch Quiz Plugin Site")



== Screenshots ==

1. A Typical Question on a Desktop Browser
2. A Typical Question After a Selection on a Desktop Browser
3. A Typical Question on a Mobile Browser (Landscape) (using WPTouch plugin on iPhone 4)
4. A Typical Question on a Mobile Browser (Portrait) (using WPTouch plugin on iPhone 4)
5. List view for easy navigation of longer quizzes

== Changelog ==

= 3.1.3 =
* Corrected several potential security vulnerabilities. Thanks to David Sopas @dsopas for very kindly pointing them out and suggesting effective solutions.
* Corrected french translation. Merci Véronique Vellard.
* Several bug fixes.

= 3.1.2 =
* Interface improvements including larger next/back arrows.
* List screen now color-codes questions based on correctness/completeness.
* Now requires an administrator to access all dashboard features of the plugin, by popular demand.
* Several bug fixes.

= 3.1.0 =
* Interface improvements including larger next/back arrows.
* List screen now color-codes questions based on correctness/completeness.
* Now requires an administrator to access all dashboard features of the plugin, by popular demand.
* Several bug fixes.

= 3.0.7 =
* Change to one GET statement for security improvement.

= 3.0.6 =
* Half off Summer Sale. Use coupon code SUMMER for 50% off all premium plugin features. (Limited time offer)
* Fixed text translation warning (Thanks Urs)

= 3.0.5 =
* Bug fixes with scoring and explanation display for certain configurations.
* Included Slovak Translation (Thanks Branco Radenovich of http://webhostinggeeks.com/blog )

= 3.0.4 =
* Bug fixes

= 3.0.3 =
* Compatibility with WP 3.5
* Updated French Translation (Thanks Capronnier Luc)

= 3.0.2 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Fixed a bug not reporting multiple correct answers properly in Gravity Forms. (Thanks Joel!)

= 3.0.2 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Improved Italian translation (Thanks Marco De Veglia)
* A few CSS tags went missing in 3.0.1

= 3.0.1 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Improved Italian translation (Thanks Marco De Veglia)
* All CSS quiz layouts.
* Small CSS Tweaks from 3.0.0

= 3.0.0 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Improved Italian translation (Thanks Marco De Veglia)

= 2.5.7 =
* Bug fix for timer clipping part of the questions and %%QUIZ_NAME%% variable not displaying.

= 2.5.6 =
* Bug fix for timer clipping part of the questions and %%QUIZ_NAME%% variable not displaying.

= 2.5.5 =
* Bug fixes dealing with timer and gravity forms premium plugins recent issues related to upgrades in WPTouch and Gravity Forms.

= 2.5.4 =
* Small CSS Tweaks for bolding on hover
* Changed behavior of hint showing on questions with multiple correct answers
* Added an autosubmit feature for Gravity Forms. autosubmit=on shortcode will automatically submit the form after hitting "Get Results." You may not have required fields, however, since the user will not have time to input them before the form is submitted.
* Small tweaks to other areas for improved performance

= 2.5.3 =
* If you update to WP 3.3, you must update to this version!

= 2.5.2 =
* jQuery 1.7/ WP 3.3 compatibility update. Removed reliance on unmaintained jQuery Tools and implemented own scrolling.

= 2.5.1 =
* New Dashboard Results Storage. [ Screenshot here.](http://gmichaelguy.com/mtq_dash.png "Dashboard Results Screenshot"). Adds ability to store individual quiz scores, percents, etc as separate fields in Gravity Forms Addon for enhanced dashboard storage. Importable form now available to help set up Dashboard Results.
* Fixed issue with Contact Form 7 showing 404 error when using add on and version 3.0. You must upgrade to Contact Form 7 3.0+ if you are using the CF7 addon.
* Updated German Translation. Thanks to Philipp Jochimsen.

= 2.5.0 =
* New Dashboard Results Storage. [ Screenshot here.](http://gmichaelguy.com/mtq_dash.png "Dashboard Results Screenshot"). Adds ability to store individual quiz scores, percents, etc as separate fields in Gravity Forms Addon for enhanced dashboard storage. Importable form now available to help set up Dashboard Results.
* Added shortcode showstamps=end to force the quiz to show "Correct/Incorrect" indicators at the end of the quiz even if correct answers aren't shown.
* Added %%TIME_USED%% and %%TIME_ALLOWED%% variables for use in the final screen.
* Fixed CSS width property for explanations causing it to cut off.


= 2.4.2 =
* Bugfix for javascript. Update to this version!
* Added Greek Translation. Thanks to Nikolaos Vavoulas.

= 2.4.1 =
* Added many features all by request of paying supporters.
* Updated pot file for translation of strings used in add ons

= 2.4.0 =
* Lots of features added all by request of paying supporters.
* Enhanced Functionality of the Email Forms Feature. Question text and time used now included in email.
* Added an option to allow multiple opportunities to answer question, but not award any partial credit using shortcode option scoring=1
* There's always some bug fixes, and more inserted.
* Added Japanese Translation. Thanks to Glen Charles Rowell http://a4jp.com & http://agreatdream.com

= 2.3.3 =
* Fixed upgrade issue due to previous reliance on activation hook.

= 2.3.2 =
* Bugfix with stamping questions wrong that are correct.
* Added Romanian translation. Thanks Richard Vencu http://richardconsulting.ro/

= 2.3.1 =
* You may need to deactivate and then reactivate your plugin after install. All data will remain intact.
* Added a [ timer](http://gmichaelguy.com/quizplugin/go/timer/ "mTouch Quiz Timer Addon").
* Added ability to submit results of quiz using [ Contact Form 7 addon](http://gmichaelguy.com/quizplugin/go/cf7/ "mTouch Quiz Contact Form 7 Addon").
* Entire answer text is now selectable instead of just the A, B, C buttons (by popular request!)
* Added new shortcode to select which form is used to submit results (formid).
* Added new shortcode to automatically move to the next question once it is completed. (autoadvance)
* Added new shortcode options to control which questions are selected (firstq, lastq)
* Added formal Dutch translation, as spoken in The Netherlands. Thanks to Sybrand Frietema de Vries.
* Incremented Version Number
* Attempted a fix for an upgrade bug requiring deactivation/reactivation for proper functionality.
* Bug fixes with scoring and stamping of questions with multiple answers or when no answers are supposed to be indicated.
* Minor cosmetic tweaks to dashboard interface.

= 2.3.0 =
* Added a [ timer](http://gmichaelguy.com/quizplugin/go/timer/ "mTouch Quiz Timer Addon").
* Added ability to submit results of quiz using [ Contact Form 7 addon](http://gmichaelguy.com/quizplugin/go/cf7/ "mTouch Quiz Contact Form 7 Addon").
* Entire answer text is now selectable instead of just the A, B, C buttons (by popular request!)
* Added new shortcode to select which form is used to submit results (formid).
* Added new shortcode to automatically move to the next question once it is completed. (autoadvance)
* Added new shortcode options to control which questions are selected (firstq, lastq)
* Bug fixes with scoring and stamping of questions with multiple answers or when no answers are supposed to be indicated.
* Minor cosmetic tweaks to dashboard interface.

= 2.2.4 =
* Compatibility with WordPress 3.2
* More features coming soon!
* Bug fix for title in add on. Thanks to Dave Hale.
* Added Hebrew translation. Thanks to Shakhar Pelled.
* Added neutral Spanish translation. Thanks to Eduardo Aranda http://www.sinetiks.com/

= 2.2.3 =
* Corrected minimized scripts not being used
* Changed output of source code by removing some brackets which could cause issues with other plugins
* Added Swedish translation. Thanks to Andreas Lundgren.
* Added Turkish translation. Thanks to Can Kirca.


= 2.2.2 =
* Bugfix for additional issues with blank values showing up when editing existing quizzes. BTW, if you don't hit "save" with all the fields blank, your existing data does not disappear. If you hit "save" it will all save as blank.

= 2.2.1 =
* Bugfix for blank values showing up when editing existing quizzes
* Added status page for email quiz results setup

= 2.2.0 =
* Added support for emailed quiz results.
* Bugfixes

= 2.1.8 =
* Bumped to show compatibility with 3.1
* Corrected an error with the offset and singlequestion shortcode option
* Changed name of a few functions to try to prevent conflicts with other instances of jquery tools

= 2.1.7 =
* Changed one span to a div which was creating an extra close span
* Added Polish Translation thanks to spopielares
* Added German Translation thanks to Wolfgang Huber
* Partial Russian Translation thanks to 3dyuriki

= 2.1.6 =
* Added a closing form tag. Should fix inability to comment on pages continaing quizzes. Thanks yuriki.
* Updated some CSS and javascript in the proofread mode.

= 2.1.5 =
* Fixes a conflict for users already using the scroll libraries in this plugin.

= 2.1.4 =
* Again, Fixed a bug where new quizzes aren't created. Double Oops!

= 2.1.3 =
* Fixed a bug where new quizzes aren't created. Oops!

= 2.1.2 =
* Fixed a bug with apostrophe's in titles causing it to truncate
* Added Italian language translation. Thanks Stefano.


= 2.1.1 =
* Fixed a bug which could result in some questions not being displayed if you had previously deleted questions
* Added version number to style files to prevent old cached versions from being displayed instead of new ones


= 2.1.0 =
* Improved UI. Questions now scroll instead of just flashing from question to question. Thanks to jQuery Tools Scrollable scripts.
* Added minified CSS and javascript files
* Changed namespace for CSS to a shorter name. If you made customized CSS changes, you will need to update your CSS after install.
* Added new shortcode options to eliminate certain parts of the quiz that are displayed. See the shortcode reference page on the plugin website for full details.
* Added new shortcode options to control question selection.
* Eliminated the shortcode option to display more than 1 question at a time. The choice is now 1 question or all questions. This is due to new scrolling option limitations. If enough people cry about this maybe I'll find a way to make this work too, but I doubt it.
* Cleaned up some of the HTML. More changes in the future.
* Added Estonian Translation. Thanks to Martin Orn.

= 2.0.4 =
* Single character changed. What a difference a byte makes?

= 2.0.3 =
* Fixed bug with multiple quiz implementation. Please update even if only using a single quiz at a time.

= 2.0.2 =
* Another bug with multiple quizzes

= 2.0.1 =
* Fixed bug with missing CSS and java

= 2.0.0 =
* Same as 1.1. Changed due to typo in version number preventing upgrades.

= 1.1 =
* Several new UI features added.
* Now includes a handy list view for easy navigation of longer quizzes. This is "on" by default. To turn it off, include "list=off" in the shortcode arguments.
* New proofreading mode that displays all questions and all answers for easy reading. Simply include in the shortcode "proofread=on"
* Display more than one question at a time. For example: use display=4 in the shortcode to show 4 questions at once.
* Now you can include as many quizzes on a page as you'd like, each with different options if you desire. One note is that the new proofreading option is global. Turn it on for one, and it'll likely result in them all being in proofreading mode.
* Updated graphics. Originals are now included inside the images directory in the original subdirectory as well.
* CSS has been better namespaced and tidyed up a bit. It's still a bit of a mess in places.
* French translation provided kindly by Jean-Michel Meyer. Many thanks!
* Bug fixes
* New bugs added


= 1.06 =
* Bugfix issue where questions would be stamped correct/wrong even though answers were supposed to be hidden until the end.
* Updated Czech language files (again thanks to Tomas Hubka!)

= 1.05 =
* Bugfixes
* Enhanced international support by removing all words from images. Now translating the pot file creates a complete translation.
* Added a Portuguese for Brazil translation by Daniel Oliveira.

= 1.04 =
* Fixed a typo causing a fatal error when calling the isnumeric function. Should have been is_numeric. My apologies.
* Includes a complete text Czech Translation by Tomas Hubka

= 1.03 =
* Skipped this number

= 1.02 =
* Fixed the international support. Thanks to Tomas Hubka for the assistance. He has also kindly sent a partial Czech translation.
* Fixed an IE formatting issue.

= 1.01 =
* Hopefully added international support for translators.

= 1.0 =
* Initial release

== Upgrade Notice ==

= 3.1.2 =
* Interface improvements including larger next/back arrows.
* List screen now color-codes questions based on correctness/completeness.
* Now requires an administrator to access all dashboard features of the plugin, by popular demand.
* Several bug fixes.

= 3.1.0 =
* Interface improvements including larger next/back arrows.
* List screen now color-codes questions based on correctness/completeness.
* Now requires an administrator to access all dashboard features of the plugin, by popular demand.
* Several bug fixes.

= 3.0.7 =
* Change to one GET statement for security improvement.

= 3.0.6 =
* Half off Summer Sale. Use coupon code SUMMER for 50% off all premium plugin features. (Limited time offer)
* Fixed text translation warning (Thanks Urs)

= 3.0.5 =
* Bug fixes with scoring and explanation display for certain configurations.
* Included Slovak Translation (Thanks Branco Radenovich of http://webhostinggeeks.com/blog )

= 3.0.4 =
* Bug fixes

= 3.0.3 =
* Compatibility with WP 3.5
* Updated French Translation (Thanks Capronnier Luc)

= 3.0.2 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Fixed a bug not reporting multiple correct answers properly in Gravity Forms. (Thanks Joel!)

= 3.0.2 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Improved Italian translation (Thanks Marco De Veglia)
* A few CSS tags went missing in 3.0.1

= 3.0.1 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Improved Italian translation (Thanks Marco De Veglia)
* All CSS quiz layouts.


= 3.0.0 =
* COLOR THEMES!!
* Enhancement to premium email results feature so that results message now includes explanation text and hint text when included.
* Improved Italian translation (Thanks Marco De Veglia)

= 2.5.7 =
* Bug fix for timer clipping part of the questions and %%QUIZ_NAME%% variable not displaying.

= 2.5.6 =
* Bug fix for timer clipping part of the questions and %%QUIZ_NAME%% variable not displaying.

= 2.5.5 =
* Bug fixes dealing with timer and Gravity Forms premium plugins recent issues related to upgrades in WPTouch and Gravity Forms.

= 2.5.4 =
* Small CSS Tweaks for bolding on hover
* Changed behavior of hint showing on questions with multiple correct answers
* Added an autosubmit feature for Gravity Forms. autosubmit=on shortcode will automatically submit the form after hitting "Get Results." You may not have required fields, however, since the user will not have time to input them before the form is submitted.
* Small tweaks to other areas for improved performance

= 2.5.3 =
* If you update to WP 3.3, you must update to this version!

= 2.5.2 =
* jQuery 1.7/ WP 3.3 compatibility update. Removed reliance on unmaintained jQuery Tools and implemented own scrolling.

= 2.5.1 =
* New Dashboard Results Storage. [ Screenshot here.](http://gmichaelguy.com/mtq_dash.png "Dashboard Results Screenshot"). Adds ability to store individual quiz scores, percents, etc as separate fields in Gravity Forms Addon for enhanced dashboard storage. Importable form now available to help set up Dashboard Results.
* Fixed issue with Contact Form 7 showing 404 error when using add on and version 3.0. You must upgrade to Contact Form 7 3.0+ if you are using the CF7 addon.
* Updated German Translation. Thanks to Philipp Jochimsen.

= 2.5.0 =
* New Dashboard Results Storage. Adds ability to store individual quiz scores, percents, etc as separate fields in Gravity Forms Addon for enhanced dashboard storage. Importable form now available to help set up Dashboard Results.
* Added shortcode showstamps=end to force the quiz to show "Correct/Incorrect" indicators at the end of the quiz even if correct answers aren't shown.
* Added %%TIME_USED%% and %%TIME_ALLOWED%% variables for use in the final screen.
* Fixed CSS width property for explanations causing it to cut off.

= 2.4.2 =
* Bugfix for javascript. Update to this version!
* Added Greek Translation. Thanks to Nikolaos Vavoulas.

= 2.4.1 =
* Added many features all by request of paying supporters.
* Updated pot file for translation of strings used in add ons

= 2.4.0 =
* Added many features all by request of paying supporters.

= 2.3.3 =
* Small activation bug fix affecting some users.

= 2.3.2 =
* Bugfix with stamping questions wrong that are correct.
* Added Romanian translation. Thanks Richard Vencu http://richardconsulting.ro/

= 2.3.1 =
* You may need to deactivate and then reactivate your plugin after install. All data will remain intact.
* Added a [ timer](http://gmichaelguy.com/quizplugin/go/timer/ "mTouch Quiz Timer Addon").
* Added ability to submit results of quiz using [ Contact Form 7 addon](http://gmichaelguy.com/quizplugin/go/cf7/ "mTouch Quiz Contact Form 7 Addon").
* Entire answer text is now selectable instead of just the A, B, C buttons (by popular request!)
* Added new shortcode to select which form is used to submit results (formid).
* Added new shortcode to automatically move to the next question once it is completed. (autoadvance)
* Added new shortcode options to control which questions are selected (firstq, lastq)
* Added formal Dutch translation, as spoken in The Netherlands. Thanks to Sybrand Frietema de Vries.
* Incremented Version Number
* Attempted a fix for an upgrade bug requiring deactivation/reactivation for proper functionality.
* Bug fixes with scoring and stamping of questions with multiple answers or when no answers are supposed to be indicated.
* Minor cosmetic tweaks to dashboard interface.

= 2.3.0 =
* Added a [ timer](http://gmichaelguy.com/quizplugin/go/timer/ "mTouch Quiz Timer Addon").
* Added ability to submit results of quiz using [ Contact Form 7 addon](http://gmichaelguy.com/quizplugin/go/cf7/ "mTouch Quiz Contact Form 7 Addon").
* Entire answer text is now selectable instead of just the A, B, C buttons (by popular request!)
* Added new shortcode to select which form is used to submit results (formid).
* Added new shortcode to automatically move to the next question once it is completed. (autoadvance)
* Added new shortcode options to control which questions are selected (firstq, lastq)
* Bug fixes with scoring and stamping of questions with multiple answers or when no answers are supposed to be indicated.
* Minor cosmetic tweaks to dashboard interface.

= 2.2.4 =
* Compatibility with WordPress 3.2
* More features coming soon!
* Bug fix for title in add on. Thanks to Dave Hale.
* Added Hebrew translation. Thanks to Shakhar Pelled.
* Added neutral Spanish translation. Thanks to Eduardo Aranda http://www.sinetiks.com/ 

= 2.2.3 =
* Corrected minimized scripts not being used
* Changed output of source code by removing some brackets which could cause issues with other plugins
* Added Swedish translation. Thanks to Andreas Lundgren.
* Added Turkish translation. Thanks to Can Kirca.

= 2.2.2 =
* Bugfix for additional issues with blank values showing up when editing existing quizzes. BTW, if you don't hit "save" with all the fields blank, your existing data does not disappear. If you hit "save" it will all save as blank.

= 2.2.1 =
* Bugfix for blank values showing up when editing existing quizzes
* Added status page for email quiz results setup

= 2.2.0 =
* Added support for emailed quiz results.
* Bugfixes

= 2.1.8 =
* Bumped to show compatibility with 3.1
* Corrected an error with the offset and singlequestion shortcode option
* Changed name of a few functions to try to prevent conflicts with other instances of jquery tools
* Known Existing Bugs Exterminated
* New Bugs Populated

= 2.1.7 =
* Changed one span to a div which was creating an extra close span
* Added Polish Translation thanks to spopielares
* Added German Translation thanks to Wolfgang Huber
* Partial Russian Translation thanks to 3dyuriki

= 2.1.6 =
* Added a closing form tag. Should fix inability to comment on pages continaing quizzes. Thanks yuriki.
* Updated some CSS and javascript in the proofread mode.
* Known Existing Bugs Exterminated
* New Bugs Populated

= 2.1.5 =
* Fixes a conflict for users already using the scroll libraries in this plugin.

= 2.1.4 =
* Again, Fixed a bug where new quizzes aren't created. Double Oops!

= 2.1.3 =
* Fixed a bug where new quizzes aren't created. Oops!

= 2.1.2 =
* Fixed a bug with apostrophe's in titles causing it to truncate
* Added Italian language translation. Thanks Stefano.


= 2.1.1 =
* Fixed a bug which could result in some questions not being displayed if you had previously deleted questions
* Added version number to style files to prevent old cached versions from being displayed instead of new ones


= 2.1.0 =
* Improved UI. Questions now scroll instead of just flashing from question to question. Thanks to jQuery Tools Scrollable scripts.
* Added minified CSS and javascript files
* Changed namespace for CSS to a shorter name. If you made customized CSS changes, you will need to update your CSS after install.
* Added new shortcode options to eliminate certain parts of the quiz that are displayed. See the shortcode reference page on the plugin website for full details.
* Added new shortcode options to control question selection.
* Eliminated the shortcode option to display more than 1 question at a time. The choice is now 1 question or all questions. This is due to new scrolling option limitations. If enough people cry about this maybe I'll find a way to make this work too, but I doubt it.
* Cleaned up some of the HTML. More changes in the future.
* Added Estonian Translation. Thanks to Martin Orn.

= 2.0.4 =
* Single character changed. What a difference a byte makes?

= 2.0.3 =
* Fixed bug with multiple quiz implementation. Please update even if only using a single quiz at a time.

= 2.0.2 =
* Another bug with multiple quizzes

= 2.0.1 =
* Fixed bug with missing CSS and java

= 2.0.0 =
* Same as 1.1. Changed due to typo in version number preventing upgrades.

= 1.1 =
* Several new UI features added.
* Now includes a handy list view for easy navigation of longer quizzes. This is "on" by default. To turn it off, include "list=off" in the shortcode arguments.
* New proofreading mode that displays all questions and all answers for easy reading. Simply include in the shortcode "proofread=on"
* Display more than one question at a time. For example: use display=4 in the shortcode to show 4 questions at once.
* Now you can include as many quizzes on a page as you'd like, each with different options if you desire. One note is that the new proofreading option is global. Turn it on for one, and it'll likely result in them all being in proofreading mode.
* Updated graphics. Originals are now included inside the images directory in the original subdirectory as well.
* CSS has been better namespaced and tidyed up a bit. It's still a bit of a mess in places.
* French translation provided kindly by Jean-Michel Meyer. Many thanks!
* Bug fixes
* New bugs added


= 1.06 =
* Bugfix issue where questions would be stamped correct/wrong even though answers were supposed to be hidden until the end.
* Updated Czech language files (again thanks to Tomas Hubka!)

= 1.05 =
* Bugfixes
* Enhanced international support by removing all words from images. Now translating the pot file creates a complete translation.
* Added a Portuguese for Brazil translation by Daniel Oliveira.

= 1.04 =
* Fixed a typo causing a fatal error when calling the isnumeric function. Should have been is_numeric. My apologies.
* Includes a complete text Czech Translation by Tomas Hubka

= 1.03 =
* Skipped this number

= 1.02 =
* Fixed the international support. Thanks to Tomas Hubka for the assistance. He has also kindly sent a partial Czech translation.
* Fixed an IE formatting issue.

= 1.01 =
* Hopefully added international support for translators.

= 1.0 =
* Initial release
