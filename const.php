<?php
/**
 * You need to define this constants in your application index file
 */
if(!defined('ENV')) define('ENV', 'dev'); // environment
if(!defined('APP_DIR')) define('APP_DIR', __DIR__ . '/../../app'); // web root dir
if(!defined('SRC_DIR')) define('SRC_DIR', __DIR__ . '/../../src'); // sources of application
if(!defined('LIB_DIR')) define('LIB_DIR', __DIR__ . '/../../vendor'); // vendors dir
if(!defined('VAR_DIR')) define('VAR_DIR', __DIR__ . '/../../var'); // directory for files
if(!defined('TMP_DIR')) define('TMP_DIR', '/tmp'); // tmp directory
if(!defined('PUBLIC_DIR')) define('PUBLIC_DIR', APP_DIR .'/public'); // directory for static files
if(!defined('PUBLIC_PATH')) define('PUBLIC_PATH', '/public'); // web path to static files
if(!defined('SECRET_SALT')) define('SECRET_SALT', 'bun'); // secret sal for hashing
if(!defined('PROJECT_ENCODING')) define('PROJECT_ENCODING', 'utf-8'); // encoding of your application
if(!defined('UNICODE')) define('UNICODE', 'utf-8'); // unicode encoding, no need to redefine :-)

if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC'); // your application timezone
}