<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:55
 */

// JSON response
header('Content-Type: application/json');

// If POST is empty but request method is POST -> get POST data from php://input
if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST))
	$_POST = json_decode(file_get_contents( 'php://input'), true);

$user_name = $_POST["username"];
$password = $_POST["password"];

// Avoid anonymous login. Can not rely on LDAP server configuration
if (empty($user_name) or empty($password))
	$message = "empty username/password";
else {
	// Load PHP class
	require_once ("../../shared/config/LDAP.php");
	require_once ("../../shared/Utils.php");

	// Connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// All users in LDAP reside at this base level
	$base_dn = "ou=people,dc=root";

	// Set up the rootDN (username) for LDAP
	// Admin has different rootDN to authenticate from LDAP as it is the configured in server rather than directory
	if($user_name == "admin") {
		$is_user_admin = true;
		$root_dn = "cn=" . $user_name . ",dc=root";
	}
	else
		$root_dn = "mail=" . $user_name . "," . $base_dn;

	// Authenticate admin/user
	if(ldap_bind($ldap_connection, $root_dn, $password)) {
		// fetch info of all users (if admin) or authenticated user
		$data = ldap_search($ldap_connection, $is_user_admin ? $base_dn : $root_dn, "objectclass=*", array("*"));

		if($data) {
				$result = ldap_get_entries($ldap_connection, $data);
				$result = Utils::filterResult($result);
		} else
			$message = "could not to get information";

	} else
		$message = "access denied";
}

if (empty($result))
	$result = array("message" => $message);

echo json_encode($result);