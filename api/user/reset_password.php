<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 21.06.18
 * Time: 12:24
 */

// JSON response
header('Content-Type: application/json');

require_once ("../../shared/Utils.php");

// required field
$username = $_POST["username"];
$password = $_POST["password"];
$token = $_POST["token"];

if (empty($username) or empty($password) or empty($token))
	$message = "username, password, token required";
else if(Utils::token_for_user_exists($username) != $token)
	$message = "token not valid";
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
		if(ldap_search($ldap_connection, $user_base_dn, "mail=" . $username, array("*"))) {
			$entry = array('userPassword' => $password);
			if(ldap_mod_replace($ldap_connection, $user_base_dn, $entry)) {
				Utils::delete_token($username, $token);
				$message = "success";
			} else {
				$message = "could not change password";
			}
		} else {
			$message = "username not found";
		}

	} else
		$message = "could not bind";
}

$response = ["message" => $message];
echo json_encode($response);
