<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:55
 */

// required fields
$mail = $_GET["username"];

if(empty($mail))
	$message = "username required";
else {
	// load PHP class
	require_once ("../../shared/config/LDAP.php");
	require_once ("../../shared/Utils.php");

	// connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Bind connection via admin because assigning role needs admin privileges
	if(ldap_bind($ldap_connection, "cn=admin,dc=root", "secret")) {
		// Where to delete user in directory
		$user_base_dn = "mail=" . $mail . ",ou=people,dc=root";

		// deletes user
		if(ldap_delete($ldap_connection, $user_base_dn)) {
			$message = "success";
		} else
			$message = "username not available";

	} else
		$message = "could not bind";
}

$response = ["message" => $message];
echo json_encode($response);