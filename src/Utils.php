<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 13:26
 */

$config = include('config.php');
$tokens_file = __DIR__ . '/' . $config['tokens_filename'];

class Utils {
	/**
	 * Result from LDAP has an extra 'count','objectclass','userpassword' entries.
	 * This function ignores all these entries and return a filtered array
	 * @param $arr
	 * @return mixed
	 */
	private static function filterUser($arr) {
    	$result = array();

    	foreach ($arr as $key => $value) {
    		if (!is_numeric($key)) {
    			continue;
    		}

			if ($value == "objectclass" or $value == "userpassword" or $value == "ou") {
				continue;
			}

			$result[$value] = $arr[$value][0];
		}

		return $result;
	}

	public static function filterResult($arr) {
		$result = array();
		$index = 0;
		foreach ($arr as $key => $value) {
			if (is_array($value)) {
				$filtered_user = Utils::filterUser($value);
				if (!empty($filtered_user)) {
					$result[$index++] = $filtered_user;
				}
			}
		}

		return $result;
	}

	/**
	 * @param $username
	 * @param $redirect_page
	 * @return bool
	 */
	public static function mail($username, $redirect_page) {
		$token = "token=" . Utils::get_token($username);
		$href_link = $redirect_page . "?" . $token;

		$to = $username;
		$subject = "Recover Password";

		$message = " 
		<html>
		<head>
            <title>Recover Password</title>
        </head>
        <body>
        <p>Click <a href='" . $href_link . "' style='font-weight: bold'>here</a> to reset the password:</p>
        <h3></h3>
        </body>
		</html>
		";

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		return mail($to, $subject, $message, $headers);
	}

	public static function get_token($username) {
		$token = Utils::token_for_user_exists($username);

		if (empty($token)) {
			// Generate and save token
			$token = bin2hex(random_bytes(16));
			Utils::save_token($username . "=" . $token);
		}

		return $token;
	}

	public static function token_for_user_exists($username) {
		global $tokens_file;
		$search_for = $username;

		$contents = file_get_contents($tokens_file);
		$pattern = preg_quote($search_for, "/");
		$pattern = "/^.*$pattern.*\$/m";

		$user_and_token = NULL;

		if (preg_match($pattern, $contents, $matches)) {
			$user_and_token = explode("=", $matches[0]);
		}

		if ($user_and_token) {
			// return token only
			return $user_and_token[1];
		}
	}

	private static function save_token($token) {
		global $tokens_file;
		$data = $token . PHP_EOL;

		$fp = fopen($tokens_file, 'a');
		$result = fwrite($fp, $data);
		fclose($fp);

		return $result;
	}

	public static function delete_token($username, $token) {
		$line_to_replace = $username . "=" . $token . PHP_EOL;
		global $tokens_file;
		$contents = file_get_contents($tokens_file);
		$contents = str_replace($line_to_replace, '', $contents);
		file_put_contents($tokens_file, $contents);
	}
}