<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 21.06.18
 * Time: 12:24
 */

// JSON response
header('Content-Type: application/json');

// required field
$username = $_GET["username"];
$redirect_page = $_GET["redirect_page"];

if (empty($username) or empty($redirect_page))
	$message = "username, redirect_page required";
else {
	// load PHP class
	require_once ("../../shared/config/LDAP.php");
	require_once ("../../shared/Utils.php");

	// connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Bind connection via admin
	if(ldap_bind($ldap_connection, "cn=admin,dc=root", "secret")) {
		// Where the username should exist in directory
		$user_base_dn = "mail=" . $username . ",ou=people,dc=root";

		// username exists
		if(ldap_search($ldap_connection, $user_base_dn, "mail=" . $username, array("*")))
			if(Utils::mail($username, $redirect_page))
				$message = "email sent to " . $username;
			else
				$message = "email could not be sent to " . $username;
		else
		$message = "username not found";

	} else
		$message = "could not bind";
}

$response = ["message" => $message];
echo json_encode($response);
