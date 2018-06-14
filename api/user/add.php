<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 10:55
 */

// JSON response
header('Content-Type: application/json');

// if POST is empty but request method is POST -> get POST data from php://input
if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST))
	$_POST = json_decode(file_get_contents( 'php://input'), true);

// required fields
$uid = $_POST["uid"];
$mail = $_POST["username"];
$cn = $_POST["first_name"];
$sn = $_POST["last_name"];
$user_password = $_POST["password"];

if(empty($uid))
	$message = "uid ";
if(empty($mail))
	$message .= "username ";
if(empty($cn))
	$message .= "first_name ";
if(empty($sn))
	$message .= "last_name ";
if(empty($user_password))
	$message .= "password";

if($message) {
	$message = trim($message);
	$message = str_replace(" ", ", ", $message);
	$message .= " required";
} else {
	// collecting form data and assigning to meaningful variables as used in LDAP.
	// e.g. POST['username'] is to 'mail' in LDAP
	$user_entry["objectClass"] = "inetOrgPerson";
	$user_entry["uid"] = $uid;
	$user_entry["mail"] = $mail;
	$user_entry["cn"] = $cn;
	$user_entry["sn"] = $sn;
	$user_entry["userPassword"] = $user_password;

	// optional fields
	$mobile = $_POST["mobile"];
	$facsimile_telephone_number = $_POST["facsimile_telephone_number"];
	$postal_address = $_POST["postal_address"];
	$registered_address = $_POST["registered_address"];
	$st = $_POST["state"];

	if($mobile)
		$user_entry["mobile"] = $mobile;
	if($facsimile_telephone_number)
		$user_entry["$facsimile_telephone_number"] = $facsimile_telephone_number;
	if($postal_address)
		$user_entry["postalAddress"] = $postal_address;
	if($registered_address)
		$user_entry["registeredAddress"] = $registered_address;
	if($st)
		$user_entry["st"] = $st;

	// load PHP class
	require_once ("../../shared/config/LDAP.php");
	require_once ("../../shared/Utils.php");

	// connect LDAP Server
	$ldap = LDAP::getInstance();
	$ldap_connection = $ldap->getConnection();

	// Bind connection via admin because creating a user requires admin privileges
	if(ldap_bind($ldap_connection, "cn=admin,dc=root", "secret")) {
		// Where to add user in directory
		$user_base_dn = "mail=" . $mail . ",ou=people,dc=root";

		// adds user
		if(ldap_add($ldap_connection, $user_base_dn, $user_entry)) {
			$message = "success";
		} else
			$message = "username not available";

	} else
		$message = "could not bind";
}

$result = array("message" => $message);

echo json_encode($result);