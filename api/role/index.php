<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:43
 */

// JSON response
header('Content-Type: application/json');
$result = array();

// load PHP class
require_once ("../../shared/config/LDAP.php");
require_once ("../../shared/Utils.php");

// connect LDAP Server
$ldap = LDAP::getInstance();
$ldap_connection = $ldap->getConnection();

// Bind connection via admin because creating a user requires admin privileges
if(ldap_bind($ldap_connection,"cn=admin,dc=root", "secret")) {
	// Where the roles are in the directory
	$role_base_dn = "cn=role,o=obcc,dc=de,dc=root";

	$mail = $_GET["username"];
	if ($mail) {
		// Which user to look for in directory
		$user_base_dn = "mail=" . $mail . ",ou=people,dc=root";
		$filter = "member=" . $user_base_dn;
	} else
		$filter = "objectclass=*";


	$just_these = array("cn");

	$data = ldap_search($ldap_connection, $role_base_dn, $filter, $just_these);

		$result = ldap_get_entries($ldap_connection, $data);
		$result = Utils::filterResult($result);
		if($result)
			$message = "success";
		else
			$message = "roles not available";

} else
	$message = "could not bind";

$response = ["message" => $message, "data" => $result];
echo json_encode($response);