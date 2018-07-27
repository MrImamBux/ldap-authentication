<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 24.07.18
 * Time: 09:26
 */

return [
	'ldap' => [
		'host' => 'localhost',
		'port' => '389',
		'ssl_port' => '636',
		'version' => 3,
		'use_ssl' => false,
		'$use_start_tls' => false,
		'username' => 'cn=admin,dc=mymanaged,dc=host',
		'password' => 'eeqhkX3ibVPi'
	],
	'dns' => [
		'root' => 'dc=mymanaged,dc=host',
		'people' => [
			'base_dn' => 'ou=people,dc=mymanaged,dc=host',
			'admin_base_dn' => 'cn=admin,dc=mymanaged,dc=host',
			'required' => 'mail' // only one field supported at the moment
		],
		'role' => [
			'base_dn' => 'cn=role,o=obcc,dc=mymanaged,dc=host',
			'admin_base_dn' => 'cn=admin,dc=mymanaged,dc=host',
			'required' => 'cn' // only one field supported at the moment
		]
	],
	'tokens_filename' => 'tokens.txt' // File will be created at src folder. If token is not created, make sure the user has permissions to read and write file
];