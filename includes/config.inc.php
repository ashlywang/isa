<?php


//Trevor local development
$configInfos["localhost"]["host"] = '127.0.0.1';
$configInfos["localhost"]["user"] = 'isa';
$configInfos["localhost"]["pass"] = 'rilinc';
$configInfos["localhost"]["dbdriver"] = 'mysqli';
$configInfos["localhost"]["dbname"] = 'isa';
$configInfos["localhost"]["peardir"] = '/usr/lib/php';
$configInfos["localhost"]["debug"] = false	;
$configInfos["localhost"]["url_root"] = 'http://localhost';
$configInfos["localhost"]["file_root"] = '/Users/tjdavis/OneDrive - Simon Fraser University (1sfu)/Sites/isa';
$configInfos["localhost"]["vendor"] = '/Users/tjdavis/OneDrive - Simon Fraser University (1sfu)/Sites/isa/vendor';
$configInfos["localhost"]["templates"] = '/Users/tjdavis/OneDrive - Simon Fraser University (1sfu)/Sites/isa/templates';
$configInfos["localhost"]["phpcas_path"] = '/Users/tjdavis/OneDrive - Simon Fraser University (1sfu)/Sites/isa/vendor/jasig/phpcas';
$configInfos["localhost"]["php_include_path"] = "";


//Production server
$configInfos["vpr-db13.dc.sfu.ca"]["host"] = 'vpr-db13.dc.sfu.ca';
//$configInfos["vpr-db13.dc.sfu.ca"]["user"] = 'trevormon';
//$configInfos["vpr-db13.dc.sfu.ca"]["pass"] = 'ilike!pasta&pizza33%';
$configInfos["vpr-db13.dc.sfu.ca"]["user"] = 'isa';
$configInfos["vpr-db13.dc.sfu.ca"]["pass"] = 'rilincetc';
$configInfos["vpr-db13.dc.sfu.ca"]["dbdriver"] = 'mysqli';
$configInfos["vpr-db13.dc.sfu.ca"]["dbname"] = 'isa';
$configInfos["vpr-db13.dc.sfu.ca"]["peardir"] = '/usr/lib/php';
$configInfos["vpr-db13.dc.sfu.ca"]["debug"] = false;
$configInfos["vpr-db13.dc.sfu.ca"]["url_root"] = 'http://vpr-db13.dc.sfu.ca/isa';
$configInfos["vpr-db13.dc.sfu.ca"]["file_root"] = '/var/www/html/isa';
$configInfos["vpr-db13.dc.sfu.ca"]["vendor"] = '/var/www/html/isa/vendor';
$configInfos["vpr-db13.dc.sfu.ca"]["templates"] = '/var/www/html/isa/templates';
$configInfos["vpr-db13.dc.sfu.ca"]["phpcas_path"] = '/var/www/html/isa/vendor/jasig/phpcas';

//Digital Ocean
$configInfos["159.203.14.73"]["host"] = '127.0.0.1';
//$configInfos["vpr-db13.dc.sfu.ca"]["user"] = 'trevormon';
//$configInfos["vpr-db13.dc.sfu.ca"]["pass"] = 'ilike!pasta&pizza33%';
$configInfos["159.203.14.73"]["user"] = 'isa';
$configInfos["159.203.14.73"]["pass"] = 'rilincetc';
$configInfos["159.203.14.73"]["dbdriver"] = 'mysqli';
$configInfos["159.203.14.73"]["dbname"] = 'isa';
$configInfos["159.203.14.73"]["peardir"] = '/usr/lib/php';
$configInfos["159.203.14.73"]["debug"] = false;
$configInfos["159.203.14.73"]["url_root"] = 'http://159.203.14.73/isa';
$configInfos["159.203.14.73"]["file_root"] = '/var/www/html/isa';
$configInfos["159.203.14.73"]["vendor"] = '/var/www/html/isa/vendor';
$configInfos["159.203.14.73"]["templates"] = '/var/www/html/isa/templates';
$configInfos["159.203.14.73"]["phpcas_path"] = '/var/www/html/isa/vendor/jasig/phpcas';

//CAS Info
$cas_host = 'cas.sfu.ca';
// Context of the CAS Server
$cas_context = '/cas';
// Port of your CAS server. Normally for a https server it's 443
$cas_port = 443;
// Path to the ca chain that issued the cas server certificate
$cas_server_ca_cert_path = '/path/to/cachain.pem';


// Global variable $configinfo will be filled with correct info depending on the server name

//  AUTH  SESSION CONFIGURATION
$sessionConfig["sessionname"] = "sfuc_research";	// session name to use. Must contain at least one letter.
$sessionConfig["sessionexpire"] = 18000; 				// 1800 secs = 30mins

//  AUTH  AVAILABLE METHODS
// You can select the available Authorization methods to use in this comma separated global variable
// Available methods:
//   ldap 		: use the function mrclib_ldapauth($uid, $pass) defined in the mrclib.php library
//   usertable 	: use the above defined array containing the username md5(password) pairs
//   database   : use a function to connect to a database table to validate username md5(password)
// DATABASE is here as a PLACE HOLDER ONLY, Its CURRENTLY NOT IMPLEMENTED
$sessionConfig["authmethod"] = "database,ldap,usertable";

//  AUTH  USER TABLE CONFIGURATION
// currently the usage of database for username/password is not enabled
// will temporarilly use this table. On this table the passwords must be MD5
// To set your password you can go to http://www.onlinefunctions.com/
// DONT enter there one of your real passwords.

$sessionConfig["usertable"]["tdavis"] = "827ccb0eea8a706c4c34a16891f84e7b"; //"c3f3c0b98db003270f05b83495c5b765";


if (strpos($_SERVER['HTTP_HOST'],':') != 0) {
    list($server,$port)=explode(":",$_SERVER['HTTP_HOST']);
} else {
    $server = $_SERVER['HTTP_HOST'];
    $port = 80;
}
if (strstr($server,"www.")) {
    $server = substr($server,4);
}
            
if (isset($configInfos[$server])) {
    $configInfo = $configInfos[$server];
} else {
    $configInfo = $configInfos["localhost"];
}

// set up default settings
if(!isset($configInfo["debug"])) {
    $configInfo["debug"] .= false;
}
if(isset($configInfo['authmethod'])) {
    $sessionConfig["authmethod"] = $configInfo['authmethod'];
} else {
    $sessionConfig["authmethod"] = "database,ldap,usertable";
}

if ($configInfo["peardir"] != "") {
    $configInfo["peardir"] .= "/";
}
if(!isset($configInfo["email_db_options"])) {
    $configInfo["email_db_options"] =  array(
        'type'        => 'db',
        'dsn'         => 'mysql://ors:rilinc@orsadmin-prep.sm.mtroyal.ca/research',
        'mail_table'  => 'mail_queue',
    );
}
if(!isset($configInfo["email_send_now"])) {
    $configInfo["email_send_now"] =  false;
}
if(!isset($configInfo["email_options"])) {
    $configInfo['email_options'] = array(
        'driver'   => 'smtp',
        'host'     => 'localhost',
        'port'     => 25,
        'auth'     => false,
        'username' => '',
        'password' => '',
    );
}
if(!isset($configInfo["debug_email"])) $configInfo['debug_email']=false;


define('MRJQUERYPATH','js/jquery-1.3.2.min.js'); // set up jquery path
define('MRCDEBUG',$configInfo["debug"]); // set up debug mode
define('MRCAJAXLOGIN',true); // set up ajax login

$iso8601 = "Y-m-d G:i";
$iso8601_day = "Y-m-d";
$niceday = "M. j, Y";
$WORKDAY=7.2;
$DOUBLE=12;
$WORKWEEK=36.0;

?>
