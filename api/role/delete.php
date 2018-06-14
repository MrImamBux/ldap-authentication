<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 14.05.18
 * Time: 13:54
 */

// JSON response
header('Content-Type: application/json');

$mail = $_GET["username"];
$role = $_GET["role"];

if (empty($mail) or empty($role))
	$message = "username and role required";
else {
	// Load PHP class
	require_once ("../../shared/config/LDAP.php");
	require_once ("../../shared/Utils.php");

	// Connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Where the roles are in the directory
	$role_base_dn = "cn=" . $role . ",cn=role,o=abc,dc=de,dc=root";

	// Which user to add in directory
	$user_base_dn = "mail=" . $mail . ",ou=people,dc=root";

	$entry["member"] = $user_base_dn;

	$root_dn = "cn=admin,dc=root";
	$password = "secret";

	// Bind connection via admin because assigning a user to a role requires admin privileges
	if (ldap_bind($ldap_connection, $root_dn, $password)) {
		if(ldap_mod_del($ldap_connection, $role_base_dn, $entry)) {
			$message = "success";
		} else
			$message = "can not delete role";

	} else
		$message = "access denied";
}

if (empty($result))
	$result = array("message" => $message);

echo json_encode($result);