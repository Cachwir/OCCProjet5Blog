<?php

function ensure_session_started() {
	if (session_id() == '') {
		ini_set('session.use_only_cookies', 0);
		// server should keep session data for AT LEAST 15 hour
		ini_set('session.gc_maxlifetime', 15 * 3600);
		// each client should remember their session id for EXACTLY 15 hour
		session_set_cookie_params(15 * 3600);
		session_start();
	}
}


function is_in_range($val, $min, $max) {
	return $val >= $min && $val <= $max;
}


function url_add_param($url, $params) {
	$info = parse_url($url);

	$parts = [];
	foreach ($params as $param => $value) {
		$parts[] = "$param=".urlencode($value);
	}

	return $info['scheme']."://".$info['host'].(isset($info['path']) ? $info['path'] : '')."?".(isset($info['query']) ? $info['query']."&" : "").join('&', $parts);
}

function is_utf8($str){
	$c=0;
	$b=0;
	$bits=0;
	$len=strlen($str);
	for($i=0; $i<$len; $i++) {
		$c=ord($str[$i]);
		if($c > 128) {
			if(($c >= 254)) return false;
			elseif($c >= 252) $bits=6;
			elseif($c >= 248) $bits=5;
			elseif($c >= 240) $bits=4;
			elseif($c >= 224) $bits=3;
			elseif($c >= 192) $bits=2;
			else return false;
			if(($i+$bits) > $len)
				return false;
			while($bits > 1) {
				$i++;
				$b=ord($str[$i]);
				if($b < 128 || $b > 191)
					return false;
				$bits--;
			}
		}
	}
	return true;
}

function camelize($value, $lcfirst = true) {
	$value = strtr(ucwords(strtr($value, array('_' => ' ', '-' => ' ', '.' => '_ '))), array(' ' => ''));
	return ($lcfirst ? strtolower($value[0]) : strtoupper($value[0])).substr($value, 1);
};


function cast($value, $type) {
	if ($value === null) {
		return null;
	}

	if ($type == 's') {
		return (string) trim($value);
	} elseif ($type == 'b') {
		return (boolean) $value;
	} elseif ($type == 'i') {
		return (int) $value;
	} elseif ($type == 'j' && is_string($value)) {
		return json_decode($value, true);
	} else {
		return $value;
	}
}

function uncast($value, $type) {
	if ($value === null) {
		return null;
	}

	if ($type == 'j') {
		return json_encode($value);
	} else {
		return $value;
	}
}

function format_number($val, $total = null, $decimals = 0, $only_percent = false) {
	if ($only_percent) {
		$number = '';
	} else {
		$number = number_format($val, $decimals, '.', ' ');
	}
	if ($total !== null) {
		$number .= ' ('.format_percent($val, $total).')';
	}
	return $number;
}

function format_percent($val, $total) {
	return sprintf('%.2f %%', $total > 0 ? $val * 100 / $total : 0);
}


function extract_date_from_request($fields) {
	foreach ($fields as $field) {
		if (!isset($_REQUEST[$field]) || $_REQUEST[$field] <= 0) {
			return null;
		}
	}

	return DateTime::createFromFormat('d/m/Y', sprintf("%02d/%02d/%04d", $_REQUEST[$fields[0]], $_REQUEST[$fields[1]], $_REQUEST[$fields[2]]), new DateTimeZone('Europe/Paris'));
}

function is_md5($str) {
	return preg_match('/^[a-z0-9]{32}$/', $str);
}

function is_sha1($str) {
	return preg_match('/^[a-z0-9]{40}$/', $str);
}

function generate_password($length) {
	// a base64-encoded md5 of chaos, truncated at self::IRC_PASSWORD_LENGTH
	return substr(base64_encode(md5(rand(0, PHP_INT_MAX))), 0, $length);
}

/**
 * Return an array indexed by $array[$i][$field]
 *
 * @param $array
 * @param $field
 * @param bool|true $no_duplicate
 * @param null $this_field_as_value
 * @return array an array where the keys are $array[$i][$field]
 */
function array_index_by($array, $field, $this_field_as_value = null, $no_duplicate = true) {
	$res = [];
	foreach ($array as &$row) {
		if (isset($row[$field])) {
			if ($no_duplicate) {
				if (isset($res[$row[$field]])) {
					throw new \InvalidArgumentException("There are duplicate $field keys (".$row[$field].")");
				}
				$res[$row[$field]] = $this_field_as_value !== null ? $row[$this_field_as_value] : $row;
			} else {
				if (!isset($res[$row[$field]])) {
					$res[$row[$field]] = [];
				}
				$res[$row[$field]][] = $this_field_as_value !== null ? $row[$this_field_as_value] : $row;
			}
		} else {
			throw new \InvalidArgumentException("There is no key $field");
		}
	}
	return $res;
}

function array_extract_keys($keys, array $array) {
	foreach ($array as $key => $value) {
		if (!in_array($key, $keys)) {
			unset($array[$key]);
		}
	}
	return $array;
}

