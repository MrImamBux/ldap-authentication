<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:55
 */

// JSON response
header('Content-Type: application/json');
$result = array();

// All configurations
$config = include('../../src/config.php');
// Users are present at this level of database in LDAP
$people_dn = $config['dns']['people'];

// If POST is empty but request method is POST -> get POST data from php://input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
	$_POST = json_decode(file_get_contents( 'php://input'), true);
}

$user_name = $_POST["username"];
$password = $_POST["password"];

// Avoid anonymous login. Preferred to handle from here, can not rely on LDAP server configuration
if (empty($user_name) or empty($password)) {
	$message = "empty username/password";
} else {
	// Load PHP class
	require_once("../../src/LDAP.php");
	require_once("../../src/Utils.php");

	// Connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// All users, except admin, in LDAP reside at this base level
	$people_base_dn = $people_dn['base_dn'];

	// Setting up the base dn specific to user in LDAP. Admin has different base dn than normal user as it is
	// configured in server rather than in LDAP directory
	if ($user_name == "admin") {
		$is_user_admin = true;
		$user_base_dn = $people_dn['admin_base_dn'];
	} else {
		$user_base_dn = $people_dn['required'] . "=" . $user_name . "," . $people_base_dn;
	}

	// let's authenticate the user with user_base_dn and provided password with ldap_connection
	if (ldap_bind($ldap_connection, $user_base_dn, $password)) {
		// fetch info of all users (if admin) or of an authenticated user
		$data = ldap_search($ldap_connection, $is_user_admin ? $people_base_dn : $user_base_dn, "objectclass=*", array("*"));

		if ($data) {
				$result = Utils::filterResult(ldap_get_entries($ldap_connection, $data));
				$message = "success";
		} else {
			$message = "could not get information";
		}
	} else {
		$message = "access denied";
	}
}

$response = ["message" => $message, "data" => $result];
echo json_encode($response);