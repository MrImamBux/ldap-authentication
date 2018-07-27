<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 21.06.18
 * Time: 12:24
 */

require_once("../../src/Utils.php");

// JSON response
header('Content-Type: application/json');

// All configurations
$config = include('../../src/config.php');
// Admin and Password present in LDAP configuration
$ldap_config = $config['ldap'];
// Users are present at this level of database in LDAP
$people_dn = $config['dns']['people'];

// required field
$username = $_POST["username"];
$password = $_POST["password"];
$token = $_POST["token"];

if (empty($username) or empty($password) or empty($token)) {
	$message = "username, password, token required";
} else if (Utils::token_for_user_exists($username) != $token) {
	$message = "token not valid";
} else {
	// load PHP class
	require_once("../../src/LDAP.php");

	// connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Bind connection via admin
	if (ldap_bind($ldap_connection, $ldap_config['username'], $ldap_config['password'])) {
		// Where the username should exist in directory
		$user_base_dn = $people_dn['required'] . "=" . $username . "," . $people_dn['base_dn'];
		$filter = $people_dn['required'] . "=" . $username;

		// username exists
		if (ldap_search($ldap_connection, $user_base_dn, $filter, array("*"))) {
			$entry = array('userPassword' => $password);
			if (ldap_mod_replace($ldap_connection, $user_base_dn, $entry)) {
				Utils::delete_token($username, $token);
				$message = "success";
			} else {
				$message = "could not change password";
			}
		} else {
			$message = "username not found";
		}
	} else {
		$message = "could not bind";
	}
}

$response = ["message" => $message];
echo json_encode($response);
