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

// All configurations
$config = include('../../src/config.php');
// Admin and Password present in LDAP configuration
$ldap_config = $config['ldap'];
// User roles are present at this level of database in LDAP
$role_dn = $config['dns']['role'];
// Users are present at this level of database in LDAP
$people_dn = $config['dns']['people'];

// load PHP class
require_once("../../src/LDAP.php");
require_once("../../src/Utils.php");

// connect LDAP Server
$ldap = LDAP::getInstance();
$ldap_connection = $ldap->getConnection();

// Bind connection via admin because creating a user requires admin privileges
if (ldap_bind($ldap_connection,$ldap_config['username'], $ldap_config['password'])) {
	// Where the roles are in the directory
	$role_base_dn = $role_dn['base_dn'];

	$mail = $_GET["username"];
	if ($mail) {
		// Which user to look for in directory
		$user_base_dn = $people_dn['required'] . '=' . $mail . "," . $people_dn['base_dn'];
		$filter = "member=" . $user_base_dn;
	} else {
		$filter = "objectclass=*";
	}

	$just_these = array("cn");
	$data = ldap_search($ldap_connection, $role_base_dn, $filter, $just_these);

	$result = ldap_get_entries($ldap_connection, $data);
	$result = Utils::filterResult($result);
	if ($result) {
		$message = "success";
	} else {
		$message = "no role(s) found for $mail";
	}
} else {
	$message = "could not bind";
}

$response = ["message" => $message, "data" => $result];
echo json_encode($response);