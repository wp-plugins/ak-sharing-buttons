=== AK Sharing Buttons ===
Tags: facebook, google, twitter, linkedin, pinterest, share, share buttons, share links
Requires at least: 3.9
Tested up to: 4.2.2
Stable tag: 1.0.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Contributors: colourstheme

Ajax load and append a list of sharing button to single-post, static-page. Ex: facebook, twitter, pinterst, google-plus, linkedin.

== Description ==

Ajax load and append a list of sharing button to single-post, static-page. Ex: facebook, twitter, pinterst, google-plus, linkedin.

After event window.load, your website send an ajax request, and get back a list of socials link. And append it to the end of "the_content".

This plugin only working with is_singular().

== Installation ==

Upload and install AK Sharing Buttons in the same way you'd install any other plugin.

== Screenshots ==

1. Config panel.
2. Style "Classic - with sharing counter".
3. Style "Static links (loading fastest - recommended)".

== Documentation ==

[Documentation](http://colourstheme.com/forums/forum/wordpress/plugin/ak-sharing-buttons/) is available on ColoursTheme.

== Changelog ==
= 1.0.6 (2015.06.30) =
+ only display sharing button on single post.

= 1.0.5 (2015.06.24) =
+ add new style (layout) : Static links (loading fastest - recommended)

= 1.0.4 (2015.06.19) =
+ change screenshots

= 1.0.3 (2015.06.19) =
+ edit description of plugin (readme.txt)

= 1.0.2 (2015.06.19) =
+ rename Contributors "Alex Kalh" by wordpress's id "colourstheme"

= 1.0.1 (2015.06.18) =
+ remove: constant "AKSB_SECURITY_KEY"
+ edit function "add_security_key": replace AKSB_SECURITY_KEY by string "aksb_load_sharing_buttons"
+ add conditional: the sharing button only display if "post_content" is not null.

= 1.0.0 (2015.06.16) =
Release the first version!
