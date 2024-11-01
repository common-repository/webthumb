=== WPF-WebThumb ===
Tags: thumbnails, links, web site, web images
Contributors: faina09
Requires at least: 3.3.2
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create thumbnails of any web page using a choice of webthumb API services: WebToPicture, bluga.net, PagePeeper, ShrinkTheWeb.


== Description ==
WPF-WebThumb create thumbnails of any web page.

You can choice a free service between:

1. <a href="http://webthumb.bluga.net/home" target="_blank">bluga.net</a>: you can register free of charge to have up to 100 thumbnails fetched per month; paying a fee this limit can be removed.

2. <a href="http://pagepeeker.com/" target="_blank">PagePeeper</a> may be use with no registration, but the thumbnail generated is branded with their logo; if you register you can get 100,000 shots/month with no logo.

3. <a href="http://www.shrinktheweb.com/" target="_blank">ShrinkTheWeb</a> returns a not-cacheable image and requires registration, free service has limited IP access. (not reccomanded)

4. <a href="http://www.shrinktheweb.com/" target="_blank">ShrinkTheWeb2</a> returns a cacheable image and requires registration, very fast.

5. <a href="http://www.webtopicture.com/" target="_blank">WebToPicture</a> is completely free, not requires registration nor is branded, but has very slow response time.

You can use a shortcode to place the thumbnail images anywhere in your posts or articles.
A multi-widget is also available.
It is possible to select the size of each thumbnail and some other options.
Using a suitable template, may click on the thumbnail to go to the site and/or display a bigger thumbnail, etc.

Currently in beta, works with PHP 5.2.12 or higher: please report any issue you find, and any feature you want. I'll try to fix the firsts and to implement the seconds!

Very close to a first release (no know bugs, if found any please ask me for support!)

Info and more samples at <a href="http://faina09.it/category/wp-plugins/wpfwebthumb/">WPF-WebThumb developer's site</a>

== Installation ==
1. Unzip and place the 'webthumb' folder in your 'wp-content/plugins' directory.
2. Activate the plugin.
3. Click the 'WPF-WebThumb' link in the WordPress setting menu, configure it in the Settings tab and save (step REQUIRED).
4. Use a shortcode [wpf-webthumb] (or legacy [webthumb]) to display thumbnail. See Help tab for more info.
5. You can also drag-and-drop one or more widget.

NOTE:

* You need to register at <a href="http://webthumb.bluga.net/home" target="_blank">bluga.net webthumb API</a> service in order to obtain the APIKEY needed by the plugin. The registration is free.

* No registration is needed by the plugin to use <a href="http://pagepeeker.com/" target="_blank">PagePeeper</a> service.

* You need to register at <a href="http://www.shrinktheweb.com" target="_blank">ShrinkTheWeb</a> service in order to obtain the ACCESSKEY needed by the plugin. The registration is free.


== Frequently Asked Questions ==
= Is it free? =
Yes! The plugin is free. You can register for free and use with no charge the service to grab the thumbnails at one of the services, that are also free.

= The plugin is not working! What can I do? =
In the 0.x version there is a 'debug' feature activated that generates hidden HTML code.

In your browser rigth click and 'display source code' or similar to view HTML, then search for '<!-- start of WPF-WebThumb ...': here you can find useful info. There is one debug info for common includes and one for each image you try to display.

If you cannot fix, please send me copy of this code, or the URL of the page that is not working. You can use WP plugin support page. I'll be happy to help you!

= What webthumb API service should I use? =
There are pros and cons for the possible choices:

* *bluga.net* requires a registration, you have 100 free shots per month. More if you pay.

* *pagepeeper* may be use with no registration, but the thumbnail generated is branded with their logo; if you register you can get 100,000 shots/month with no logo (this feature not implemented jet)

* *shrinktheweb* is very fast but requires registration and clicking on the image redirect you to a intermediate page in shrinktheweb.com. The image caching is available only to premium account and is not implemented by the plugin.

* *shrinktheweb caching* image is now available for free users too! Just use *'shrinktheweb (img cache)'* option.

* *Web2Picture* is completely free, but has very slow response time (hours? days?) if thumb does not exists. So it is add as an option before grab the thumbs with any other service.

= What the 'template' parameter stay for? =
It is possible to use predefined templates (see dir 'templates') or define a custom template to display the thumbnails. Just create webthumbs-n.html in 'templates' dir and refer to is as template=n (n is an integer!)

The predefined templates you can choice between are:

* 0 : is just an *&lt;img /&gt;* tag with attributes src, alt, title, id

* 1 : is a simple box with title on the top (if specified) and url in the bottom (if specified) and the thumbnail in the middle

* 2 : is a small clickable box, useful to have many thumbnails on a row.

see examples at <a href="http://faina09.it/category/wp-plugins/wpfwebthumb/">WPF-WebThumb developer's site</a>

= Why have I a thumbnail with a question mark? =
The plugin poll for an image ready, if it is not in about 1 minute it stops waiting and display a question mark image. Probably refreshing the page the image is then fetched.

= Why have I a thumbnail with a PagePeeper logo only? =
When you request a thumbnail to PagePeeper chances are that it is ready in seconds; if not you can retrieve it later, but in this case an image with PagePeeper logo only is returned and cached.

You should <b>delete</b> the cached image to retrieve the correct one.

= How can I clear the cache? =
You can delete or update file by file using the 'Cache' tab in settings pages, or you can directly browse your site at the directory specified by 'thumbnails dir is:'.

Here you can find images with names that are 'close' ;-) to the domain names you thumbnailed: if you delete one or more of them a new cached image will be saved next time the thumbnail is requested.

== Screenshots ==
1. Setup WPF-WebThumb
2. Some web site images fetched by WPF-WebThumb


== Changelog ==
= 0.27 =
* WP4.0
* Url for WebToPicture changed to api.thumbcreator.com
* PagePeeper updated to API V2: free.pagepeeker.com/v2/
= 0.26 =
* renamed
= 0.25 =
* MAXGRABS=3
= 0.24 =
* css and js register and enqueue, tested with WP 3.5.2
= 0.23 =
* '&' char managed, tested with WP 3.5-beta2
= 0.22 =
* not-dos chars excluded
= 0.21 =
* longer wait for bluga.net, only 3 new grabs/page a time
= 0.20 =
* Tested for WP 3.4.2, small changes
= 0.19 =
* widget class added
= 0.18 =
* max two images refreshed from cache for each page call
= 0.17 =
* add Web2Picture option (completely free, but very slow response time if thumb does not exists)
= 0.16 =
* Shorter timeouts; if timeout display '?' image, try grabb again on refresh
= 0.15 =
* add size dimension to file name; some fixes and reworks
= 0.14 =
* fixes
= 0.13 =
* set cache expire days
= 0.12 =
* wait up to 1 min for PageKeeper image ready
* poll for ShrinkTheWeb image ready, but still no errors managed
= 0.11 =
* fixes
= 0.10 =
* added user-defined templates
* fix not-existent functions
= 0.9 =
* Updated files pot and js.
= 0.8 =
* Refresh grabbed thumbnails, some defaults fixed.
= 0.7 =
* Preview cached thumbnails; can delete each of them. Tabbed setup navigation
= 0.6 =
* ShrinkTheWeb cached images service added (new free service add by the provider!!)
= 0.5 =
* ShrinkTheWeb service added (not cacheable images)
= 0.4 =
* PagePeeper service added
* default size is now selectable
= 0.3 =
* generate better html, link by javascript
* parameter template=0 generate just ref for img tag
= 0.2 =
* added language support and it_IT translation
* added default image if thumbnail cannot be created or retrieved
* fix when the thumbnail cannot be created
= 0.1 =
* Initial release of plugin.
* Please test and report any issue you find, and any feature you want. I'll try to fix the firsts and to implement the seconds!


== Upgrade Notice ==
= 0.23 =
* '&' char managed, tested with WP 3.5-beta2
= 0.22 =
* not-dos chars excluded
= 0.20 =
* Tested for WP 3.4.2, small changes
= 0.19 =
* widget class added
= 0.18 =
* max two images refreshed from cache for each page call
= 0.17 =
* add Web2Picture option
= 0.16 =
* if timeout display '?' image
= 0.15 =
* add size dimension to file name.