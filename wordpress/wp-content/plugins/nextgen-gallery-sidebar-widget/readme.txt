=== NextGEN Gallery Sidebar Widget ===

Donate link: http://ailoo.net/about
Tags: image, picture, photo, widgets, gallery, images, nextgen-gallery
Requires at least: 2.8
Tested up to: 3.3.1
Stable tag: 0.4.3

A widget to show NextGEN galleries in your sidebar.

== Description ==

The NextGEN widgets only allow showing of single images, I needed a solution to show links to galleries, so I wrote this widget. You can specify the following parameters:

- Maximum Galleries: the number of galleries you want to show
- Gallery Order: you can select random, date added ascending or date added descending
- Gallery Thumbnail: which image should be taken as thumbail in the sidebar (preview set in NGG, first or random image)
- AutoThumb parameters: if you got [AutoThumb](http://wordpress.org/extend/plugins/autothumb/) installed, the widget will use its functions to resize the image to your needs. Use a string like `w=80&h=80&zc=1` here to show 80x80 square thumbnails.
- Output width/height: if you don't use AutoThumb, the plugin will set the HTML attributes width & height.
- Default Link Id: the widget assumes that you set up pages for each gallery and link the gallery to that page (you can use the NGG Gallery Editor to do this). If a gallery has no link set, it will use the default link (id of a page or post).
- Exclude galleries: exclude galleries by specifying their ID as comma separated list

All development is done on [GitHub](https://github.com/maff/wp-nextgen-gallery-sidebar-widget). If you have ideas, enhancements, etc. feel free to fork the project and send a pull request.

For any issues please use the [Issue Tracker](https://github.com/maff/wp-nextgen-gallery-sidebar-widget/issues).

Templating:

Beginning with version 0.3, you have full control over the widget's output as it is controlled with templates. As the built-in templates would get overwritten on every plugin update, create a new directory called "ngg-sidebar-widget" in your theme's directory and copy the two template files "tpl.outer.html" and "tpl.inner.html" over there to edit them. The outer template is just a wrapper template (useful when you need some additional markup, e.g. when creating a list), the inner template has access to all the values from the gallery and image object (written in a simple templating syntax: {=object.member}). For the most use cases you should only need "gallery.title", "gallery.link" and "image.url".

You will have access to the following variables:

* gallery
    * gid
    * name
    * path
    * title
    * galdesc
    * pageid
    * previewpic
    * author
    * link
* image
    * errmsg
    * error
    * imageURL
    * thumbURL
    * imagePath
    * thumbPath
    * href
    * thumbPrefix
    * thumbFolder
    * galleryid
    * pid
    * filename
    * description
    * alttext
    * imagedate
    * exclude
    * thumbcode
    * name
    * path
    * title
    * pageid
    * previewpic
    * permalink
    * post_id
    * sortorder
    * meta_data
    * gid
    * galdesc
    * author
    * imageHTML
    * thumbHTML
    * url
    * output\_width
    * output\_height
    * output\_width\_tag
    * output\_height\_tag

== Installation ==

1. Upload `ngg-sidebar-widget.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the widget in the widget editor.

== Changelog ==

- 0.4: Add include_galleries option
- 0.3.3.1: Fix bug on widget no displaying galleries when no exclusions are set (bug #59, #60)
- 0.3.3: Fix wrong maximum galleries (bug #58)
- 0.3.2: Image output width fix in template, Cleanup
- 0.3.1: Gallery limit bugfix
- 0.3: Wordpress 2.8+ widget API, gallery exclusion option, templating feature
- 0.2.2: Add gallery_thumbnail option to select thumbnail image (preview, first, random)