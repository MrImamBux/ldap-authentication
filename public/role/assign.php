<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:43
 */

// JSON response
header('Content-Type: application/json');

$mail = $_GET["username"];
$role = $_GET["role"];

if (empty($mail) or empty($role)) {
	$message = "username, role required";
} else {
	// All configurations
	$config = include('../../src/config.php');
	// Admin and Password present in LDAP configuration
	$ldap_config = $config['ldap'];
	// User roles are present at this level of database in LDAP
	$role_dn = $config['dns']['role'];
	// Users are present at this level of database in LDAP
	$people_dn = $config['dns']['people'];

	// Load PHP class
	require_once("../../src/LDAP.php");
	require_once("../../src/Utils.php");

		// Connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Where the roles are in the directory
	$role_base_dn = $role_dn['required'] . "=" . $role . "," . $role_dn['base_dn'];

	// Which user to add in directory
	$user_base_dn = $people_dn['required'] . '=' . $mail . "," . $people_dn['base_dn'];

	$entry["member"] = $user_base_dn;

	// Bind connection via admin because assigning a user to a role requires admin privileges
	if (ldap_bind($ldap_connection, $ldap_config['username'], $ldap_config['password'])) {
		if (ldap_mod_add($ldap_connection, $role_base_dn, $entry)) {
			$message = "success";
		} else {
			$message = "could not assign role to $mail";
		}
	} else {
		$message = "access denied";
	}
}

$response = ["message" => $message];
echo json_encode($response);