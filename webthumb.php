<?php
/*
  Plugin Name: WPF-WebThumb
  Plugin URI: http://wordpress.org/extend/plugins/webthumb/
  Description: Create thumbnails of any web page using a choice of webthumb API services:
  <a href="http://www.webtopicture.com/" target="_blank">WebToPicture</a>, <a href="http://webthumb.bluga.net/home" target="_blank">bluga.net</a>,
  <a href="http://pagepeeker.com/" target="_blank">PagePeeper API</a>, <a href="http://www.shrinktheweb.com/" target="_blank">ShrinkTheWeb</a>.
  Version: 0.27
  Author: faina09
  Author URI: http://profiles.wordpress.org/faina09
  License: GPLv2 or later
 */

require_once 'wpf-grabthumb.php';
require_once 'wpf-webthumb_widget.php';

define( 'WEBTHUMB_VER', '0.27' );
define( 'MAXGRABS', 3 );

if ( is_admin() ) {
    // add webthumb page to admin menu
    add_action( 'admin_menu', 'webthumb_menu' );
    // add a Settings link to the Plugins page
    add_filter( 'plugin_action_links', 'webthumb_plugin_action_links', 10, 2 );
}
// add webthumb common style and jscript
add_action( 'wp_head', 'webthumb_head' );
// define shortcode
add_shortcode( 'webthumb', 'webthumb_shortcode' );
add_shortcode( 'wpf-webthumb', 'webthumb_shortcode' );
// Load i18n language support
load_plugin_textdomain( 'webthumb', false, basename( dirname( __FILE__ ) ) . '/languages' );
// define widget
add_action( 'widgets_init', 'webthumb_register_widgets' );
function webthumb_register_widgets()
{
    register_widget( 'WebThumbWidget' );
}

function webthumb_plugin_action_links( $links, $file )
{
    if ( $file == plugin_basename( __FILE__ ) ) {
        $links[] = '<a href="options-general.php?page=webthumb">' . __( 'Settings', 'webthumb' ) . '</a>';
    }
    return $links;
}

function webthumb_menu()
{
    add_options_page( 'WPF-WebThumb settings', 'WPF-WebThumb', 'manage_options', 'webthumb', 'webthumb_configure' );
}

function webthumb_admin_tabs( $current )
{
    $tabs = array ('cache' => 'Cache', 'settings' => 'Settings', 'help' => "Help");
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $tabs as $tab => $name ) {
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=webthumb&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

function webthumb_head()
{
    echo '<!-- start of WPF-WebThumb head ver.' . WEBTHUMB_VER . ' --></p>' . PHP_EOL;
    wp_register_style( 'webthumb', plugins_url( '/templates/webthumb-common.css', __FILE__ ) );
    wp_enqueue_style( 'webthumb' );
    wp_register_script( 'webthumb', plugins_url( '/templates/webthumb-common.js', __FILE__ ) );
    wp_enqueue_script( 'webthumb' );
    wp_register_script( 'pagepix', 'http://www.shrinktheweb.com/scripts/pagepix.js' );
    wp_enqueue_script( 'pagepix' );
    echo '<!-- end of WPF-WebThumb head --></p>' . PHP_EOL;
    global $webthumb_count;
    global $webthumb_regrabcount;
    global $webthumb_grabs;
    $webthumb_count = 0;
    $webthumb_regrabcount = 0;
    $webthumb_grabs = 0;
}

function webthumb_shortcode( $atts )
{
    //$wt_debug = true;
    global $webthumb_count;
    global $webthumb_regrabcount;
    global $webthumb_grabs;
    $subdir = '/webthumb/';
    $upload_dir = wp_upload_dir();
    $uploadsPath = str_replace( array ('/', '\\'), DIRECTORY_SEPARATOR, $upload_dir['basedir'] . $subdir );
    $uploadsUrl = $upload_dir['baseurl'] . $subdir;
    $options = get_option( 'webthumb' );
    $service = $options['service'];
    $apikey = $options['apikey'];
    $acckey = $options['acckey'];
    $seckey = $options['seckey'];
    $agemax = $options['agemax'];
    $sizedef = $options['size'];
    $tryweb2 = $options['tryweb2'];
    extract( shortcode_atts( array (
        'url' => 'http://faina09.it/',
        'size' => $sizedef,
        'showurl' => $options['showurl'],
        'showsml' => $options['showsml'],
        'label' => '',
        'wtdebug' => false,
        'template' => 1
                    ), $atts ) );
    // NOT usable chars " * / : < > ? \ [ ] |
    $nouse = array ('/', '.', '?', '*', ':', '<', '>', '\\', '[', ']', '|', '&', '#038;');
    $filename = str_replace( $nouse, '_', str_replace( 'http://', '', $url ) ) . '[' . $size . '].png';
    $url = str_replace( '#038;', '', $url );
    // set unique id for the webthumb object
    $webthumb_count = $webthumb_count + 1;
    // load Template
    $webthumb_template = file_get_contents( dirname( __FILE__ ) . '/templates/webthumbs-' . (string) $template . '.html' );
    $webthumb_debug = '';
    $webthumb_debug .= ' tryW2P=' . $tryweb2;
    $webthumb_debug .= ' service=' . $service;
    $webthumb_debug .= ' url=' . $url;
    $webthumb_debug .= ' size=' . $size;
    $webthumb_debug .= ' showurl=' . $showurl;
    $webthumb_debug .= ' showsml=' . $showsml;
    $webthumb_debug .= ' label=' . $label . PHP_EOL;

    if ( !( strpos( $url, 'http://' ) === 0) ) {
        $url = 'http://' . $url;
    }
    $file = $uploadsPath . $filename;

    /* debug http://www.onlineconversion.com/unix_time.htm
      $cacheDays = $agemax;
      if ($cacheDays >= 0 && file_exists($file)) {
      $cacheTimeoutDate = time() - (86400 * $cacheDays);
      // File is still within cache date, so use cached file
      $webthumb_debug .= 'filetime='.filemtime( $file ).' > cacheTimeoutDate='.$cacheTimeoutDate;
      if (filemtime( $file ) > $cacheTimeoutDate) {
      $webthumb_debug .= ' No cache refresh';
      }
      }
      debug ^^^^^^ */

    // old cache update only for the first 2 images for each page request
    $regrab = false;
    if ( webthumb_cache_timeout( $file, $agemax ) ) {
        $webthumb_regrabcount = $webthumb_regrabcount + 1;
        if ( $webthumb_regrabcount < 2 ) {
            $webthumb_debug .= ' regrab old cache ';
            $regrab = true;
        }
    }
    if ( !file_exists( $file ) || $regrab ) {
        $webthumb_debug .= ' File to grab: ' . $file . ' from URL:' . $url . PHP_EOL;
        if ( 'shrinktheweb' == $service ) {
            return grabthumb( $service, '', '', $acckey, $url, $file, $size, false ); // WARNING: returns
        } else {
            if ( $webthumb_grabs < MAXGRABS ) {
                $webthumb_grabs = $webthumb_grabs + 1;
                $webthumb_debug .= grabthumb( $service, $apikey, $seckey, $acckey, $url, $file, $size, $tryweb2 );
                //$webthumb_debug .= grabthumb($service, $apikey, $seckey, $acckey, $url, $file, $sizedef, $tryweb2); //always grab the default size
            }
        }
        // now try again to access the file:
        if ( !file_exists( $file ) ) {
            $fn = plugins_url( 'images/noimg.png', __FILE__ );
        } else {
            $fn = $uploadsUrl . $filename;
        }
    } else {
        $webthumb_debug .= ' File exists: ' . $file . PHP_EOL;
        $fn = $uploadsUrl . $filename;
    }

    if ( $showsml ) {
        $onmouseover = "webthumb_bigImg(this)";
        $onmouseout = "webthumb_normalImg(this)";
    } else {
        $onmouseover = "#";
        $onmouseout = "#";
    }

    $urlbot = "";
    if ( $showurl ) {
        $urlbot = $url;
    }
    //if ( !$wtdebug ) $webthumb_debug = '';

    $webthumb_placeholders = array (
        '{VERSION}',
        '{webthumb_count}',
        '{webthumb_debug}',
        '{url}',
        '{label}',
        '{onmouseover}',
        '{onmouseout}',
        '{filename}',
        '{urlbot}'
    );
    $webthumb_replacements = array (
        WEBTHUMB_VER,
        $webthumb_count,
        $webthumb_debug,
        $url,
        $label,
        $onmouseover,
        $onmouseout,
        $fn,
        $urlbot
    );

    return str_replace( $webthumb_placeholders, $webthumb_replacements, $webthumb_template );
}

function webthumb_configure()
{
    ?>
    <link rel="Stylesheet" type="text/css" href="<?php bloginfo( 'url' ); ?>/wp-content/plugins/webthumb/css/wpf-webthumb.css" /> 
    <div class="name"><h1><?php _e( 'WPF-WebThumb settings', 'webthumb' ); ?></h1></div>
    <div class="wrap">
        <?php
        $subdir = '/webthumb/';
        $upload_dir = wp_upload_dir();
        $uploadsPath = str_replace( array ('/', '\\'), DIRECTORY_SEPARATOR, $upload_dir['basedir'] . $subdir );
        $uploadsUrl = $upload_dir['baseurl'] . $subdir;
        if ( !( file_exists( $uploadsPath ) && is_dir( $uploadsPath ) ) ) {
            if ( !@mkdir( $uploadsPath, 0755, true ) ) {
                echo '<div class="error" style="padding:10px">';
                _e( "Can\'t create", 'webthumb' );
                echo ' <b>' . $uploadsPath . '</b> ';
                _e( 'folder. Please create it and make it writable!', 'webthumb' );
                echo '</div>';
            }
        }
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'help';
        webthumb_admin_tabs( $tab );

        switch ( $tab ) {
            case "cache":
                include 'wpf-webthumb_admin_cache.php';
                break;
            case "settings":
                include 'wpf-webthumb_admin_settings.php';
                break;
            case "help":
                ?>
                <h3><?php _e( 'Usage', 'webthumb' ); ?></h3>
                <p><?php _e( 'Choice the service to use, enter the keys for the service that require them and save the configuration.', 'webthumb' ); ?></p>
                <p><?php _e( 'You should use shortcode. Simply put <strong>[wpf-webthumb]</strong> in post or page content.', 'webthumb' ); ?></p>
                <p><?php _e( 'You can use the following parameters for the shortcode <strong>[wpf-webthumb]</strong>, that override previous settings:', 'webthumb' ); ?></p>
                <ul>
                    <li><?php _e( '<strong>url</strong>=url-to-grab (default: http://coste.mypressonline.com/wp/)', 'webthumb' ); ?></li>
                    <li><?php _e( '<strong>size</strong>=tiny|small|medium|big', 'webthumb' ); ?></li>
                    <li><?php _e( '<strong>showurl</strong>=1|0 (default: 1=yes)', 'webthumb' ); ?></li>
                    <li><?php _e( '<strong>showsml</strong>=1|0 (default: 0=no)', 'webthumb' ); ?></li>
                    <li><?php _e( '<strong>label</strong>=label to show (if any)', 'webthumb' ); ?></li>
                    <li><?php _e( '<strong>template</strong>=N (default=1, see FAQ)', 'webthumb' ); ?></li>
                </ul>
                <p><?php _e( 'Code usage sample: <strong>[wpf-webthumb url=faina09.it/ size=small showsml=yes label="faina09 WordPress site" template=3]</strong>', 'webthumb' ); ?></p>
                <h3><?php _e( 'Cache tab usage', 'webthumb' ); ?></h3>
                <p><?php _e( 'In the Cache tab it is possible to review, delete and update the thumbnails.', 'webthumb' ); ?></p>
                <p><?php _e( 'The update will use the saved configuration. Note that is not possible to save thumbnails if the service is "ShrinkTheWeb (jscript)".', 'webthumb' ); ?></p>
                <p><?php _e( 'Since the URL is NOT stored anyway, you MUST edit the proposed URL to have the "Update thumbnail" link works correctly.', 'webthumb' ); ?></p>
                <p><?php _e( 'When a file in the cache is older than the maximun days set, it will be fetched again (only 2 new images for each page request).', 'webthumb' ); ?></p>
                <?php
                break;
        }
        ?>
        <script type="text/javascript" language="javascript" src="<?php bloginfo( 'url' ); ?>/wp-content/plugins/webthumb/css/wpf-webthumb.js?ver=123"></script>
        <hr/><i><?php _e( 'WPF-WebThumb and its creator are not affiliated with bluga.net, PagePeeker, ShrinkTheWeb or WebToPicture in any way; if you find any other service of interest please leave a message!', 'webthumb' ); ?></i>
    </div>
    <?php
}

// common functions
if ( !function_exists( 'webthumb_startsWith' ) ):
    function webthumb_startsWith( $haystack, $needle )
    {
        $length = strlen( $needle );
        return (substr( $haystack, 0, $length ) === $needle);
    }

endif;

if ( !function_exists( 'webthumb_endsWith' ) ):
    function webthumb_endsWith( $haystack, $needle )
    {
        $length = strlen( $needle );
        if ( $length == 0 ) {
            return true;
        }
        return (substr( $haystack, -$length ) === $needle);
    }

endif;

if ( !function_exists( 'webthumb_cache_timeout' ) ):
    function webthumb_cache_timeout( $file, $cacheDays )
    {
        if ( $cacheDays == -1 ) {
            return false;
        }
        if ( $cacheDays >= 0 && file_exists( $file ) ) {
            $cacheTimeoutDate = time() - (86400 * $cacheDays);
            // File is still within cache date, so use cached file
            if ( filemtime( $file ) > $cacheTimeoutDate ) {
                return false;
            }
        }
        // delete old and fetch new file
        if ( file_exists( $file ) ) {
            unlink( $file );
        }
        return true;
    }

endif;
    