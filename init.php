<?php

if (!defined('CFCF7_VERSION')) define( 'CFCF7_VERSION', '1.0.1' );
if (!defined('CFCF7_REQUIRED_WP_VERSION')) define( 'CFCF7_REQUIRED_WP_VERSION', '4.1' );
if (!defined('CFCF7_PLUGIN')) define( 'CFCF7_PLUGIN', __FILE__ );
if (!defined('CFCF7_PLUGIN_BASENAME')) define( 'CFCF7_PLUGIN_BASENAME', plugin_basename( CFCF7_PLUGIN ) );
if (!defined('CFCF7_PLUGIN_NAME')) define( 'CFCF7_PLUGIN_NAME', trim( dirname( CFCF7_PLUGIN_BASENAME ), '/' ) );
if (!defined('CFCF7_PLUGIN_DIR')) define( 'CFCF7_PLUGIN_DIR', untrailingslashit( dirname( CFCF7_PLUGIN ) ) );

if (!defined('CFCF7_LOAD_JS')) define('CFCF7_LOAD_JS', true);
if (!defined('CFCF7_LOAD_CSS')) define('CFCF7_LOAD_CSS', true);

if (!defined('CFCF7_REGEX_MAIL_GROUP')) define( 'CFCF7_REGEX_MAIL_GROUP', '@\[[\s]*([a-zA-Z_][0-9a-zA-Z:._-]*)[\s]*\](.*?)\[[\s]*/[\s]*\1[\s]*\]@s');


function cfcf7_plugin_path( $path = '' ) {
    return path_join( CFCF7_PLUGIN_DIR, trim( $path, '/' ) );
}

function cfcf7_plugin_url( $path = '' ) {
    $url = plugins_url( $path, CFCF7_PLUGIN );
    if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
        $url = 'https:' . substr( $url, 5 );
    }
    return $url;
}

require_once CFCF7_PLUGIN_DIR.'/cfcf7.php';
require_once CFCF7_PLUGIN_DIR.'/cfcf7-options.php';