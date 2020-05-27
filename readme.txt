=== delicious-plus ===
Contributors: kemayo
Donate link: http://davidlynch.org/blog/donate
Tags: delicious, widget
Requires at least: 2.2
Tested up to: 2.6.1
Stable tag: 1.2.1

Makes available a widget to display your recent links tagged with delicious.

== Description ==

Produces output very similar to [delicious's linkrolls script](http://delicious.com/help/linkrolls), but in a way that isn't guaranteed to mess with most themes.

Allows you to customize:

* The number of links to display
* Whether the link's description is shown, if you provided one
* Whether the link's tags are shown
* Whether the link's favicon is shown
* Showing only links with certain tags
* And the ever-popular widget title

== Frequently Asked Questions ==

= Isn't this really similar to the sample widget? =

Yes.  But the sample widget provided by Automattic in the initial widgets plugin release didn't do what I wanted.  I took that widget and made it much more configurable and feature-laden.

= Can I style the widget's appearance? =

You should have all the hooks for CSS styling that you need.  The widget produces html with the following layout:

`
div#deliciousplus-box
 ul
  li
   img
   a.deliciousplus-post
   span.deliciousplus-description
   span.deliciousplus-tags
    a
    a
    a
`

With slight variations, depending on which options you have enabled.

== Installation ==

1. Upload `deliciousplus.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the `Widgets` menu to place the widget within your layout

== Screenshots ==

1. Example of the widget, displayed within the [Simplr](http://www.plaintxt.org/themes/simplr/) theme, with all options enabled.

== History ==

* 1.2.1: Bump the Wordpress version and get rid of some legacy mentions of
 "del.icio.us".
* 1.2: Update for the annoyingly backwards-incompatible nature of the new
  delicious.com design and v2 feeds api.
* 1.1: Allow multiple instances of the widget.
* 1.0: Initial release.
