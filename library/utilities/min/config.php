<?php
/**
 * Configuration for "min", the default application built with the Minify
 * library
 *
 * @package Minify
 */


/**
 * Set to true to log messages to FirePHP (Firefox Firebug addon).
 * Set to false for no error logging (Minify may be slightly faster).
 * @link http://www.firephp.org/
 *
 * If you want to use a custom error logger, set this to your logger
 * instance. Your object should have a method log(string $message).
 *
 * cache system does not have error logging yet.
 */
$min_errorLogger = false;


/**
 * To allow debugging, you must set this option to true.
 *
 * Once true, you can send the cookie minDebug to request debug mode output. The
 * cookie value should match the URIs you'd like to debug. E.g. to debug
 * /min/f=file1.js send the cookie minDebug=file1.js
 * You can manually enable debugging by appending "&debug" to a URI.
 * E.g. /min/?f=script1.js,script2.js&debug
 *
 * In 'debug' mode, Minify combines files with no minification and adds comments
 * to indicate line #s of the original files.
 */

// Only allow debug if we are in debug mode in ClientExec
include('../../../config.php');

if (defined('DEBUG') && DEBUG) {
    $min_allowDebugFlag = true;
} else {
    $min_allowDebugFlag = false;
}

/**
 * Allow use of the Minify URI Builder app. If you no longer need
 * this, set to false.
 **/
$min_enableBuilder = false;

/**
 * For best performance, specify your temp directory here. Otherwise Minify
 * will have to load extra code to guess. Some examples below:
 */

//removed as some configurations with Cache setup are not properly configured... we should have another setting in config
//stating to use APC
/*
if ( function_exists('apc_fetch') ) {
    require 'lib/Minify/Cache/APC.php';
    $min_cachePath = new Minify_Cache_APC();
} else */
if ( isset($_ENV['OS']) && strpos( $_ENV[ "OS" ], "Win" ) !== false ) {
    $min_cachePath = mb_substr(__FILE__, 0, strlen(__FILE__) - 32)."\\uploads\\cache";
} else {
    $min_cachePath = mb_substr(__FILE__, 0, strlen(__FILE__) - 32)."/uploads/cache";
}


/**
 * Leave an empty string to use PHP's $_SERVER['DOCUMENT_ROOT'].
 *
 * On some servers, this value may be misconfigured or missing. If so, set this
 * to your full document root path with no trailing slash.
 * E.g. '/home/accountname/public_html' or 'c:\\xampp\\htdocs'
 *
 * If /min/ is directly inside your document root, just uncomment the
 * second line. The third line might work on some Apache servers.
 */

// Try and calculate the documentRoot using the best method that works for most people.
$min_documentRoot = mb_substr(__FILE__, 0, strlen(__FILE__) - 15);
$min_documentRoot  = str_replace("/library/utilities", "", $min_documentRoot);
$min_documentRoot  = str_replace("\\library\\utilities", "", $min_documentRoot);

// Some people have issues with the above method, so compare the first 10 chars with that of document_root to find out if they are the same. This should solve the issue for one or two users wereby the above root returns a totally different path than document_root does.
if(mb_substr($min_documentRoot, 10) != mb_substr($_SERVER['DOCUMENT_ROOT'], 10)) {
	$min_documentRoot = mb_substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - 32);
}

/**
 * Cache file locking. Set to false if filesystem is NFS. On at least one
 * NFS system flock-ing attempts stalled PHP for 30 seconds!
 */
$min_cacheFileLocking = true;


/**
 * Combining multiple CSS files can place @import declarations after rules, which
 * is invalid. Minify will attempt to detect when this happens and place a
 * warning comment at the top of the CSS output. To resolve this you can either
 * move the @imports within your CSS files, or enable this option, which will
 * move all @imports to the top of the output. Note that moving @imports could
 * affect CSS values (which is why this option is disabled by default).
 */
$min_serveOptions['bubbleCssImports'] = false;


/**
 * Cache-Control: max-age value sent to browser (in seconds). After this period,
 * the browser will send another conditional GET. Use a longer period for lower
 * traffic but you may want to shorten this before making changes if it's crucial
 * those changes are seen immediately.
 *
 * Note: Despite this setting, if you include a number at the end of the
 * querystring, maxAge will be set to one year. E.g. /min/f=hello.css&123456
 */
$min_serveOptions['maxAge'] = 86400;


/**
 * If you'd like to restrict the "f" option to files within/below
 * particular directories below DOCUMENT_ROOT, set this here.
 * You will still need to include the directory in the
 * f or b GET parameters.
 *
 * // = shortcut for DOCUMENT_ROOT
 */
//$min_serveOptions['minApp']['allowDirs'] = array('//js', '//css');

/**
 * Set to true to disable the "f" GET parameter for specifying files.
 * Only the "g" parameter will be considered.
 */
$min_serveOptions['minApp']['groupsOnly'] = false;


/**
 * By default, Minify will not minify files with names containing .min or -min
 * before the extension. E.g. myFile.min.js will not be processed by JSMin
 *
 * To minify all files, set this option to null. You could also specify your
 * own pattern that is matched against the filename.
 */
//$min_serveOptions['minApp']['noMinPattern'] = '@[-\\.]min\\.(?:js|css)$@i';

/**
 * If you minify CSS files stored in symlink-ed directories, the URI rewriting
 * algorithm can fail. To prevent this, provide an array of link paths to
 * target paths, where the link paths are within the document root.
 *
 * Because paths need to be normalized for this to work, use "//" to substitute
 * the doc root in the link paths (the array keys). E.g.:
 * <code>
 * array('//symlink' => '/real/target/path') // unix
 * array('//static' => 'D:\\staticStorage')  // Windows
 * </code>
 */
//$min_symlinks = array();


// Seems that Windows doesnt set DOCUMENT_ROOT so we need to make it up ourselves
if(!isset($_SERVER['DOCUMENT_ROOT'])){
    if(isset($_SERVER['SCRIPT_FILENAME'])){
        $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
    } else if(isset($_SERVER['PATH_TRANSLATED'])) {
        $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
    }
}


// Fix for some servers whereby we chopped 1 too many chars, not sure why but this fixes it.
if($_SERVER['DOCUMENT_ROOT'][strlen($_SERVER['DOCUMENT_ROOT'])-1] == '/') { $chopCount = 0; } else { $chopCount = 1; }

if ( isset($_ENV['OS']) && strpos( $_ENV[ "OS" ], "Win" ) !== false ) {
    $predir = rtrim(mb_substr($min_documentRoot, strlen($_SERVER['DOCUMENT_ROOT'])+$chopCount),"\\");
    $min_symlinks = array(
        '//..\\'.$predir => $min_documentRoot
    );
} else {
    $predir = rtrim(mb_substr($min_documentRoot, strlen($_SERVER['DOCUMENT_ROOT'])+$chopCount),"/");
    //echo $_SERVER['DOCUMENT_ROOT']."\n";
    //echo $min_documentRoot."\n";
    //echo $predir;exit;
    $min_symlinks = array(
        '//../'.$predir => $min_documentRoot
    );
}

/**
 * If you upload files from Windows to a non-Windows server, Windows may report
 * incorrect mtimes for the files. This may cause Minify to keep serving stale
 * cache files when source file changes are made too frequently (e.g. more than
 * once an hour).
 *
 * Immediately after modifying and uploading a file, use the touch command to
 * update the mtime on the server. If the mtime jumps ahead by a number of hours,
 * set this variable to that number. If the mtime moves back, this should not be
 * needed.
 *
 * In the Windows SFTP client WinSCP, there's an option that may fix this
 * issue without changing the variable below. Under login > environment,
 * select the option "Adjust remote timestamp with DST".
 * @link http://winscp.net/eng/docs/ui_login_environment#daylight_saving_time
 */
$min_uploaderHoursBehind = 0;

/**
 * Path to Minify's lib folder. If you happen to move it, change
 * this accordingly.
 */
$min_libPath = dirname(__FILE__) . '/lib';

// try to disable output_compression (may not have an effect)
@ini_set('zlib.output_compression', '0');
@ini_set('zlib.output_compression_level', '-9'); // Have to set it to -9 for some reason to totally disable it


if(defined('DISABLE_CACHING') && DISABLE_CACHING) {
	// disable server caching
	$min_cachePath = null;
	// prevent client caching
	$min_serveOptions['maxAge'] = 0;
	$min_serveOptions['lastModifiedTime'] = $_SERVER['REQUEST_TIME'];
}