=== Better Categories Images ===
Contributors: namncn
Donate link: https://namncn.com/donate/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: category, taxonomy, images, term field.
Tested up to: 4.9.6
Requires PHP: 5.6.3

The Better Categories Images Plugin allow you to add image with any category or taxonomy.

== Description ==

The Better Categories Images Plugin allow you to add image with any category or taxonomy.

Use:

`
+ $thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true ); \\ [get_term_meta](https://developer.wordpress.org/reference/functions/get_term_meta/)
+ $image        = wp_get_attachment_image( $thumbnail_id, 'full' ); \\ [wp_get_attachment_image](https://developer.wordpress.org/reference/functions/wp_get_attachment_image/)
`

== Installation ==

=== From within WordPress ===

1. Visit 'Plugins > Add New'
2. Search for 'Better Categories Images'
3. Activate Better Categories Images from your Plugins page.

=== Manually ===

1. Upload the `better-categories-images` folder to the `/wp-content/plugins/` directory
2. Activate the Better Categories Images plugin through the 'Plugins' menu in WordPress
3. Go to "after activation" below.

== Frequently Asked Questions ==

You'll find answers to many of your questions on (https://namncn.com/plugins/better-categories-images/).

== Screenshots ==

== Changelog ==

= 1.0.3 =
* Fix readme.txt.

= 1.0.2 =
* Fix bug can't exclude tax.

= 1.0.1 =
* fix bugs.

= 1.0.0 =
* First release.
