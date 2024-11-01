<?php
// require autoloader for Bluga
require_once 'Bluga/Autoload.php';

// default settings
ini_set( 'max_execution_time', 120 );
define( 'WAITMAX', 3 );
define( 'MD5IMG', 'e0ff67603a0259219967e2872363583d' );

// scheduler
function grabthumb( $SERVICE, $APIKEY, $SECRETKEY, $ACCESSKEY, $URL, $IMGFILE, $THUMB_SIZE, $TRYWEB2PIC ) {
	$res = '';
	$ThumbSize = getWebThumSizeCode ( $SERVICE, $THUMB_SIZE );
	if ( $TRYWEB2PIC ) $res = grabthumbWebToPicture( $URL, $IMGFILE, getWebThumSizeCode ( 'webtopicture', $THUMB_SIZE ) );
	if ( !webthumb_endsWith( $res, 'OK' ) ) {
		switch ( $SERVICE ){
			case 'bluganet':
				return $res .= grabthumbBluga( $APIKEY, $URL, $IMGFILE, $ThumbSize );
			break;
			case 'pagepeeker':
				return $res .= grabthumbPageKeeper( $URL, $IMGFILE, $ThumbSize );
			break;
			case 'shrinktheweb':
				return $res .= grabthumbShrinkTheWeb( $ACCESSKEY, $URL, $IMGFILE, $ThumbSize );
			break;
			case 'shrinktheweb2':
				return $res .= grabthumbShrinkTheWeb2( $SECRETKEY, $ACCESSKEY, $URL, $IMGFILE, $ThumbSize );
			break;
			case 'webtopicture':
				return $res .= grabthumbWebToPicture( $URL, $IMGFILE, $ThumbSize );
			break;
			default:
				$res .= 'unknow service'.PHP_EOL;
		}
	}
	return $res;
}

function grabthumbBluga( $APIKEY, $URL, $IMGFILE, $ThumbSize ) {
	$result = '';
	try {
		$result .= 'calling Bluga'.PHP_EOL;
		$webthumb = new Bluga_Webthumb();

		// enable debug to see what xml is being sent
		//$webthumb->debug = true;
		//$result .= ' $APIKEY='.$APIKEY;
		$result .= ' $URL='.$URL.', $IMGFILE='.$IMGFILE.', $ThumbSize='.$ThumbSize.PHP_EOL;
		$webthumb->setApiKey( $APIKEY );
		$job = $webthumb->addUrl( $URL, $ThumbSize, 1024, 768 );
		$job->options->outputType = 'png8'; 
		
		$webthumb->submitRequests();
		$tmout = 0;
		// better to wait longer, because you pay a credit every request and unusual urls seems to be NOT cached, causing a new long time request
		while (!$webthumb->readyToDownload()  && $tmout <= WAITMAX*100) {
			sleep( 10 );
			$tmout = $tmout + 1;
			$result .= 'Checking Job Status #: '.$tmout.PHP_EOL;
			$webthumb->checkJobStatus();
		}
		if ( $tmout > WAITMAX ) return $result.' TIMEOUT'.PHP_EOL;
		$webthumb->fetchToFile( $job, $IMGFILE, $ThumbSize);
		$result .=  'Job Url: http://webthumb.bluga.net/pickup?id='.$job->status->id.PHP_EOL;
		$result .=  'saved file='.$IMGFILE.PHP_EOL;
		if ( !webthumb_endsWith( $IMGFILE, '[big].png' ))
		{
			//always save the 'large' file version
			$ix = strrpos( $IMGFILE, '[' );
			$fn = substr( $IMGFILE, 0, $ix ).'[big].png';
			$webthumb->fetchToFile( $job, $fn, 'large');
			$result .=  'saved file='.$fn.PHP_EOL;
		}
	} catch ( Exception $e ) {
		$result .= 'We got an Exception'.PHP_EOL;
		$result .= $e->getMessage().PHP_EOL;
		$result .= $e->getTraceAsString();
	}
	return $result;
}

function grabthumbPageKeeper( $URL, $IMGFILE, $ThumbSize ) {
	$result = '';
	try {
		$result .= 'calling PageKeeper '.PHP_EOL;
		$result .= ' $URL='.$URL.', $IMGFILE='.$IMGFILE.', $ThumbSize='.$ThumbSize.PHP_EOL;

		$options = array(
		CURLOPT_RETURNTRANSFER => true, // return web page
		CURLOPT_HEADER         => false, // don't return headers 
		CURLOPT_FOLLOWLOCATION => false, // follow redirects 
		CURLOPT_AUTOREFERER    => true, // set referer on redirect 
		CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect 
		CURLOPT_TIMEOUT        => 5, // timeout on response 
		CURLOPT_MAXREDIRS      => 0, // stop after 0 redirects 
		); 
		
		$url = 'http://free.pagepeeker.com/v2/thumbs_generated.php?size='.$ThumbSize.'&url='.$URL;
		$result .= 'CURLchk: '.$url.PHP_EOL;
		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$tmout = 0;
		$imgfetched = '';
		while ( !webthumb_startsWith( $imgfetched, '__pp_rd(1' ) && $tmout <= WAITMAX ) {
			$imgfetched = curl_exec( $ch );
			// image ready when 0->1 in $imgfetched like  __pp_rd(1,'http://coste.mypressonline.com');
			$tmout = $tmout + 1;
			$result .= 'CURLcpoll#: '.$tmout.' Res:'.$imgfetched.PHP_EOL;
			sleep( 5 );
		}
		if ( $tmout > WAITMAX ) return $result.' TIMEOUT'.PHP_EOL;
		$url = 'http://free.pagepeeker.com/v2/thumbs.php?size='.$ThumbSize.'&refresh=0&url='.$URL;
		$result .= 'CURLget: '.$url.PHP_EOL;
		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$imagedata = curl_exec( $ch );
		$err = curl_errno( $ch ); // helpful for troubleshooting
		$errmsg = curl_error( $ch );
		curl_close( $ch );
		$result .= ' curl results '.$err.' - '.$errmsg.PHP_EOL;

		$imagedata = @imagecreatefromstring( $imagedata ); // to build as image
		if($imagedata) {
			// save image locally 
			imagejpeg( $imagedata, $IMGFILE, 100 );
		} 
		imagedestroy( $imagedata ); // free up memory (if done with it)
	} catch ( Exception $e ) {
		$result .= 'We got an Exception'.PHP_EOL;
		$result .= $e->getMessage().PHP_EOL;
		$result .= $e->getTraceAsString();
	}
	return $result;
}

function grabthumbShrinkTheWeb( $ACCESSKEY, $URL, $IMGFILE, $ThumbSize ) {
	$result = '<script type="text/javascript">';
	$result .= "stw_pagepix('".$URL."', '".$ACCESSKEY."', '".$ThumbSize."')";
	$result .= '</script>';
	return $result;
}

function grabthumbShrinkTheWeb2( $SECRETKEY, $ACCESSKEY, $URL, $IMGFILE, $ThumbSize ) {
	$result = '';
	try {
		$result .= 'calling ShrinkTheWeb '.PHP_EOL;
		$result .= ' $URL='.$URL.', $IMGFILE='.$IMGFILE.', $ThumbSize='.$ThumbSize.PHP_EOL;

		$options = array(
		CURLOPT_RETURNTRANSFER => true, // return web page
		CURLOPT_HEADER         => false, // don't return headers 
		CURLOPT_FOLLOWLOCATION => false, // follow redirects 
		CURLOPT_AUTOREFERER    => true, // set referer on redirect 
		CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect 
		CURLOPT_TIMEOUT        => 5, // timeout on response 
		CURLOPT_MAXREDIRS      => 0, // stop after 0 redirects 
		); 
		
		//1. request to verify if image is available: stwembed=0 & stwu=(secret key)
		$url = 'http://images.shrinktheweb.com/xino.php?stwembed=0&stwu='.$SECRETKEY.'&stwaccesskeyid='.$ACCESSKEY.'&stwsize='.$ThumbSize.'&stwurl='.$URL;
		$result .= 'CURLchk: '.$url.PHP_EOL;
		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$tmout = 0;
		$imgfetched = '';
		//TODO: Process XML response from server
		//see $resultData = STWWT_fetch_xml_processReturnData($resp['body']);
		$pos = false;
		while ($pos === false && $tmout <= WAITMAX) {
			$imgfetched = curl_exec( $ch ); 
			// image ready when response contains 'delivered'; errors ignored
			$pos = strpos( $imgfetched, 'delivered' );
			$tmout = $tmout + 1;
			$result .= 'CURLcpoll#: '.$tmout.' Res:'.$imgfetched;
			// very basic error management
			if ( strpos( $imgfetched, 'fix_and_retry' ) !== false ) return $result;
			sleep( 5 );
		}
		if ($tmout > WAITMAX) return $result.' TIMEOUT'.PHP_EOL;
		//2. image request: no stwu & stwembed=1
		$url = 'http://images.shrinktheweb.com/xino.php?stwembed=1&stwaccesskeyid='.$ACCESSKEY.'&stwsize='.$ThumbSize.'&stwurl='.$URL;
		$result .= 'CURLget: '.$url.PHP_EOL;
		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$imagedata = curl_exec( $ch );
		$err = curl_errno( $ch ); // helpful for troubleshooting 
		$errmsg = curl_error( $ch ); 
		curl_close( $ch );
		$result .= ' curl results '.$err.' - '.$errmsg.PHP_EOL;

		$imagedata = @imagecreatefromstring( $imagedata ); // to build as image
		if($imagedata) {
			// save image locally 
			imagejpeg( $imagedata, $IMGFILE, 100 );
		} 
		imagedestroy($imagedata); // free up memory (if done with it)
	} catch ( Exception $e ) {
		$result .= 'We got an Exception'.PHP_EOL;
		$result .= $e->getMessage().PHP_EOL;
		$result .= $e->getTraceAsString();
	}
	return $result;
}

function grabthumbWebToPicture( $URL, $IMGFILE, $ThumbSize ) {
	$result = '';
	try {
		$URL = str_replace( 'http://','',$URL );
		$result .= 'calling WebToPicture '.PHP_EOL;
		$result .= ' $URL='.$URL.', $IMGFILE='.$IMGFILE.', $ThumbSize='.$ThumbSize.PHP_EOL;
		
		$options = array(
		CURLOPT_RETURNTRANSFER => true, // return web page
		CURLOPT_HEADER         => false, // don't return headers 
		CURLOPT_FOLLOWLOCATION => false, // follow redirects 
		CURLOPT_AUTOREFERER    => true, // set referer on redirect 
		CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect 
		CURLOPT_TIMEOUT        => 5, // timeout on response 
		CURLOPT_MAXREDIRS      => 0, // stop after 0 redirects 
		); 
		
		//1. request to verify if 'small' image is available: 
		$url = 'http://api.thumbcreator.com/t.php?url='.$URL.'&s=s';
		$result .= 'CURLchk: '.$url.PHP_EOL;
		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$imgfetched = curl_exec( $ch );
		$md5image = md5($imgfetched);
		$result .= 'md5IMG = '.$md5image.PHP_EOL;
		if (MD5IMG != $md5image) {
			//2. get the imaged of requested size and save it
			$url = 'http://api.thumbcreator.com/t.php?url='.$URL.'&s='.$ThumbSize;
			$result .= 'CURLget: '.$url.PHP_EOL;
			$ch = curl_init( $url );
			curl_setopt_array( $ch, $options );
			$imagedata = curl_exec( $ch );
			$err = curl_errno( $ch ); // helpful for troubleshooting 
			$errmsg = curl_error( $ch ); 
			curl_close( $ch );
			$result .= ' curl results '.$err.' - '.$errmsg.PHP_EOL;
			$imagedata = @imagecreatefromstring( $imagedata ); // to build as image
			if($imagedata) {
				// save image locally 
				imagejpeg( $imagedata, $IMGFILE, 100 );
			} 
			imagedestroy($imagedata); // free up memory (if done with it)
			$result .= 'IMG fetched OK';
		}
		else
			$result .= 'IMG NOT ready... skip w2p'.PHP_EOL;
	} catch ( Exception $e ) {
		$result .= 'We got an Exception'.PHP_EOL;
		$result .= $e->getMessage().PHP_EOL;
		$result .= $e->getTraceAsString();
	}
	return $result;
}

function getWebThumSizeCode ( $SERVICE, $ThumbSize ) {
	$thumbsOptions = array(
			'1' => 'tiny',
			'2' => 'small',
			'3' => 'medium',
			'4' => 'big'
			);
	$thumbsBluga = array(
			'1' => 'small',
			'2' => 'medium',
			'3' => 'medium2',
			'4' => 'large'
			);
	$thumbsPageKeeper = array(
			'1' => 't',
			'2' => 'm',
			'3' => 'l',
			'4' => 'x'
			);
	$thumbsShrinkTheWeb = array(
			'1' => 'tny',
			'2' => 'lg',
			'3' => 'xlg',
			'4' => 'xlg'
			);
	$thumbsWebToPicture = array(
			'1' => 's',
			'2' => 'm',
			'3' => 'l',
			'4' => 'xl'
			);
	$key = array_search( $ThumbSize, $thumbsOptions );
	switch ( $SERVICE ){
		case 'bluganet':
			return $thumbsBluga[$key];
		break;
		case 'pagepeeker':
			return $thumbsPageKeeper[$key];
		break;
		case 'shrinktheweb':
		case 'shrinktheweb2':
			return $thumbsShrinkTheWeb[$key];
		break;
		case 'webtopicture':
			return $thumbsWebToPicture[$key];
		break;
		default:
			return $ThumbSize;
		}
	}
/* reference: thumbnails dimensions for each service used
WebThum bluganet pagekeep shrinkth WebToPic
tiny     80x60    90x68    90x68   150x100 
small   160x120  200x150  200x150  200x150
medium  320x240  400x300  320x240  300x250
big     640x480  480x360  320x240  640x480

bluganet
small - 80x60
medium - 160x120
medium2 - 320x240
large - 640x480
excerpt - 400x150 (taken from the top left corner by default)

pagekeeper
t	Tiny, 90 x 68 pixels
s	Small, 120 x 90 pixels
m	Medium, 200 x 150 pixels
l	Large, 400 x 300 pixels
x	Extra large, 480 x 360 pixels

stwsize
75x56 mcr Tells STW to return the "micro" size.
90x68 tny Tells STW to return the "tiny" size.
100x75 vsm Tells STW to return the "very small" size.
120x90 sm Tells STW to return the "small" size.
200x150 lg Tells STW to return the "large" size.
320x240 xlg Tells STW to return the "extra large" size

WebToPicture
s for small 150x100
m for medium 200x150
l for large 300x250
xl for extra-large 640x480
*/

