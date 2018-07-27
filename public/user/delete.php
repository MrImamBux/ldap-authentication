<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:55
 */

// JSON response
header('Content-Type: application/json');

// All configurations
$config = include('../../src/config.php');
// Admin and Password present in LDAP configuration
$ldap_config = $config['ldap'];
// Users are present at this level of database in LDAP
$people_dn = $config['dns']['people'];

// user to delete
$username = $_GET["username"];

if (empty($username)) {
	$message = "username required";
} else {
	// load PHP class
	require_once("../../src/LDAP.php");
	require_once("../../src/Utils.php");

	// connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Bind connection via admin because admin can delete user from database
	if (ldap_bind($ldap_connection, $ldap_config['username'], $ldap_config['password'])) {
		// Where to delete user in directory
		$user_base_dn = $people_dn['required'] . "=" . $username . "," . $people_dn['base_dn'];

		// deletes user
		if (ldap_delete($ldap_connection, $user_base_dn)) {
			$message = "success";
		} else {
			$message = "username not found";
		}
	} else {
		$message = "could not bind with ldap connection";
	}
}

$response = ["message" => $message];
echo json_encode($response);