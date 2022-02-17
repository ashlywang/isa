<?php
/**
* This file includes the config file and necessary libraries for each page, it also makes the connection to the database.
*/
/***********************************
* CONFIGURATION
************************************/
// php5 stuff:
ini_set('zend.ze1_compatibility_mode', 'Off');
$defaultTimeZone = 'Canada/Pacific';
if (function_exists('date_default_timezone_set')) { // to avoid problems in php4
    date_default_timezone_set($defaultTimeZone);
}



sessionConfig(); // set up the session
//ob_start();
require_once("config.inc.php");
require_once('adodb5/adodb.inc.php');
//ob_end_clean();

if (1) {
    error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 'On');
} else {
    error_reporting(0);
}

global $configInfo;

/***********************************
* MAIN
************************************/
// set up database connection
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$db = ADONewConnection($configInfo["dbdriver"]); // eg. 'mysql' or 'postgres'
if ($configInfo["debug"]==true) $db->debug = true; 
else $db->debug=false;
//$db->debug = true; 
$db->Connect(
    $configInfo["host"],
    $configInfo["user"],
    $configInfo["pass"],
    $configInfo["dbname"]
);

//Get TWIG started
require_once "$configInfo[vendor]/autoload.php";
$loader = new \Twig\Loader\FilesystemLoader("$configInfo[templates]");
//Enable to allow caching in production environment
//$twig = new \Twig\Environment($loader, ['cache' => "$configInfo[file_root]/cache"]);
$twig = new \Twig\Environment($loader, ['cache'=>false]);

//CAS

require_once $configInfo["phpcas_path"] . '/CAS.php';
phpCAS::setDebug();
phpCAS::setVerbose(true);

phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

$user=phpCAS::getUser();
$configInfo['user']=$user;


$authUser=$db->GetRow("SELECT * FROM `users` WHERE comp_id='$user'");

if($authUser) {

	$configInfo['is_admin']=$authUser['is_admin'];
	$configInfo['visited']=$authUser['visited'];
	$configInfo['is_rpm']=$authUser['is_rpm'];
}
	
//else phpCAS::logoutWithUrl("$configInfo[url_root]/unauthorized.html");


//PHP Mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $configInfo["vendor"] . '/phpmailer/src/Exception.php';
require $configInfo["vendor"] . '/phpmailer/src/PHPMailer.php';
require $configInfo["vendor"] . '/phpmailer/src/SMTP.php';



/**
* @desc Configurates the session and starts it
* @return void
*/
function sessionConfig() {
    global $sessionConfig;
    //session_name($sessionConfig["sessionname"]);
    //session_set_cookie_params( $sessionConfig["sessionexpire"]);
    //ini_set("session.gc_maxlifetime", "18000");
    session_start();
    //setcookie(session_name(), $_COOKIE[session_name()], time()+$sessionConfig["sessionexpire"]);
    // If IP changed, destroy the session
    if ( isset( $_SESSION['REMOTE_ADDR'] ) && $_SESSION['REMOTE_ADDR'] != $_SERVER['REMOTE_ADDR'] ) {
        session_regenerate_id();
        $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    }
    if ( !isset( $_SESSION['REMOTE_ADDR'] ) ) {
        $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    }
}


/**
* @desc Validates if a session is started
* @return true
*/
function sessionLoggedin() {
    $loggedIn = (isset($_SESSION["loggeduser"]) && $_SESSION["loggeduser"]!="") ? true : false;
    return $loggedIn;
}

/**
* @desc Returns the username used to start the session
* @return String with username, otherwise empty string
*/
function sessionLoggedUser() {
	return (isset($_SESSION["loggeduser"])) ? $_SESSION["loggeduser"] : false;
}


/**
*   Used for debugging, returns a nicely formatted version of an array  or object
*
*   @param      array or object     $var        target array or object
*   @return     string                          HTML formatted result
*/
function PrintR($var) {
    echo '<div align="left"><pre>';
    print_r($var);
    echo '</pre></div>';
} // function PrintR



?>
