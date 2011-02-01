<?php
$offset = 60 * 60 * 24 * 30; // 30 days of caching
$expiry = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";

header( 'Content-Type: text/javascript' );
header( 'Cache-Control: public, must-revalidate' );
header( 'Expires: ' . $expiry );
header( 'Content-Encoding: gzip' );

ob_start('ob_gzhandler');
include( 'jquery-1.5.min.js' );
include( 'jquery-bbq.min.js' );
ob_end_flush();