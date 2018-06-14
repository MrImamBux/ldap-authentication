<?php
/**
 * Created by PhpStorm.
 * User: imam.bux
 * Date: 30.04.18
 * Time: 13:26
 */

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
    		if (!is_numeric($key))
    			continue;

			if ($value == "objectclass" or $value == "userpassword" or $value == "ou")
				continue;

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
				if(!empty($filtered_user))
					$result[$index++] = $filtered_user;
			}
		}

		return $result;
	}

}