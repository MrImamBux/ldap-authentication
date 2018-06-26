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

if (empty($mail) or empty($role))
	$message = "username, role required";
else {
	// Load PHP class
	require_once ("../../shared/config/LDAP.php");
	require_once ("../../shared/Utils.php");

	// Connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Where the roles are in the directory
	$role_base_dn = "cn=" . $role . ",cn=role,o=obcc,dc=de,dc=root";

	// Which user to add in directory
	$user_base_dn = "mail=" . $mail . ",ou=people,dc=root";

	$entry["member"] = $user_base_dn;

	$root_dn = "cn=admin,dc=root";
	$password = "secret";

	// Bind connection via admin because assigning a user to a role requires admin privileges
	if (ldap_bind($ldap_connection, $root_dn, $password)) {
		if(ldap_mod_add($ldap_connection, $role_base_dn, $entry)) {
			$message = "success";
		} else
			$message = "can not assign role";

	} else
		$message = "access denied";
}

$response = ["message" => $message];
echo json_encode($response);