<?php
include_once 'vendor/autoload.php';
use techdeck\passwordHash\password;
$password = new password;

// error reporting --------- don't forget to remove this for production!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// constants
define('ROOT',dirname(__FILE__));
define('HOSTINFO','mysql:dbname=starneto_starNetOnline;host=localhost;charset=utf8');
define('SUPPORTHOST','mysql:dbname=starneto_support;host=localhost');
define('PASSWORD','Cartm@n123');
define('USERNAME','starneto_starNet');

foreach (glob(ROOT."/adapters/constants/*.php") as $filename) {
    include $filename;
}

// not sure how I want to handle this one yet
define('PROMOTIONDAY', '2019-06-03');

// environ setup
setlocale(LC_MONETARY, 'en_US');

//  auto load data adapters
spl_autoload_register(function ($class) {
    if(is_file(ROOT.'/adapters/'.$class.'.php')) {
        include_once ROOT.'/adapters/'.$class.'.php';
	}
});

// set up session stuff, so that we can manage logged in state
list($subdomain, $host, $tdl) = explode('.', $_SERVER['HTTP_HOST']);

session_name("starsacramento");
session_set_cookie_params(0, '/', '.'.$host.'.'.$tdl, false, false);
// session_set_cookie_params(['SameSite' => 'None', 'Secure' => true]);
session_start();

$sessionVals = $_SESSION;

if (isset($_SERVER['HTTP_ORIGIN'])) {
	// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
	// you want to allow, and if so:
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

if(isset($_POST['logout'])) {
    unset($_SESSION['user']);
    unset($_SESSION['roles']);
    unset($_SESSION['contexts']);
	unset($_SESSION['currentContext']);
	
	//*************** CROSS-SITE COMPAT */ remove old site id
	unset($_SESSION['id']);
}

if(isset($_SESSION['currentContext']) && gettype($_SESSION['currentContext']) == 'string')unset($_SESSION['currentContext']);

//  are we trying to login?
$failed = 'not logging in';
if(isset($_POST['login'])) {
	$localUser = userData::getRowByEmail($_POST['email']);
    if($localUser->id) {
		//  user found, have they verified their email address?
		if($localUser->verified) {
			// all good, load roles and check pwd
			$userRoles = usersRolesData::getRowsByUserId($localUser->id);
			if($password->validate($_POST['current-password'], $localUser->passwordHash)) {
				// we can log in, make sure that if account is closed, we now open it.
				$localUser->openAccount();

				//  set all important session vars
				$_SESSION['user'] = $localUser;
				$_SESSION['roles'] = $userRoles;

				// not sure if we will have contexts on the front end, but just in case...
				$_SESSION['contexts'] = usersAccessContextsData::getRowByUserId($localUser->id);
				$_SESSION['currentContext'] = (isset($_SESSION['currentContext']) ? $_SESSION['currentContext'] : $_SESSION['contexts'][0]);
				
				// set admin, just in case we want to give ourselves extra functionality
				$_SESSION['admin'] = array_intersect(array_column($userRoles, 'roleId'), [1,2,3]) ? true : false;

				//*************** CROSS-SITE COMPAT */ create old site id
				$_SESSION['id'] = $localUser->id;
			
			} else {
				$failed = 'This email address and/or password is incorrect. Please try again or contact tech support for help.';
			}
		} else {
			$failed = 'You account email address was never verified, please contact tech support for help.';
		}
    } else {
        $failed = 'This email address and/or password is incorrect. Please try again or contact tech support for help.';
    }

    unset($localUser);
}

// are we changing contexts? probably not on the front end, but just in case...
if(isset($_POST['changeContext'])) {
    foreach($_SESSION['contexts'] as $context) {
        if($context->id == $_POST['changeContext']){
            $_SESSION['currentContext'] = $context;
        }
    }

}

//*********** CROSS-SITE COMPAT */  need to check if they are logged in the old site
if(isset($_SESSION['id']) && !isset($_SESSION['user'])){
	$localUser = userData::getRowById($_SESSION['id']);
	$userRoles = usersRolesData::getRowsByUserId($localUser->id);
	$_SESSION['user'] = $localUser;
	$_SESSION['roles'] = $userRoles;

	// not sure if we will have contexts on the front end, but just in case...
	$_SESSION['contexts'] = usersAccessContextsData::getRowByUserId($localUser->id);
	$_SESSION['currentContext'] = (isset($_SESSION['currentContext']) ? $_SESSION['currentContext'] : $_SESSION['contexts'][0]);
	
	// set admin, just in case we want to give ourselves extra functionality
	$_SESSION['admin'] = array_intersect(array_column($userRoles, 'roleId'), [1,2,3]) ? true : false;
		
}

if(isset($_SESSION['user'])){
    $user = $_SESSION['user'];
    $roles = $_SESSION['roles'];
	$admin = $_SESSION['admin'];
	
	$primary = primaryData::getRowByUserId($user->id);
	$students = studentData::getCompleteDataByUserId($user->id);

	// again, not sure about this, but costs so little to include
    $contexts = $_SESSION['contexts'];
	$currentContext = (isset($_SESSION['currentContext']) ? $_SESSION['currentContext'] : $contexts[0]);
}

// here is where we might set up some routing for enrollment situations.... maybe
// this is backwards of how we used to do this, but for an api, this could make sense
if(isset($_POST['init'])) include ROOT.'/requests/initialLoad.php';

if(!isset($_POST['action'])) {
	// if we got here, this is an initial page load, or a null request of some kind.
	// in either case, return back current session state information
	if(isset($user) && $user->id){
		echo json_encode([
			"error" => false,
			"user" => $user->sanitized(),
			"roles" => $roles,
			"contexts" => $contexts,
			"currentContext" => $currentContext,
			"admin" => $admin,
			"primary" => $primary,
			"students" => $students,
		]);
	} else {
		echo json_encode([
			"error" => true,
			"reason" => $failed,
		]);
	}
}