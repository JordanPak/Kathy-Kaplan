<?php

/* Compatibility */

@ini_set("magic_quotes_runtime", 0);
@ini_set('display_errors',0);

/* Stripslashes */

function stripslashes_array(&$array, $iterations=0) {
    if ($iterations < 3) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                stripslashes_array($array[$key], $iterations + 1);
            } else {
                $array[$key] = stripslashes($array[$key]);
            }
        }
    }
}

if (get_magic_quotes_gpc()) {
    stripslashes_array($_GET);
    stripslashes_array($_POST);
    stripslashes_array($_COOKIE);
}


/* Fallback mb_strlen */

if(!function_exists('mb_strlen'))
{
	function mb_strlen( $str, $enc="" )
	{
		$counts = count_chars( $str );
		$total = 0;
	
		// Count ASCII bytes
		for( $i = 0; $i < 0x80; $i++ )
		{
			$total += $counts[$i];
		}
	
		// Count multibyte sequence heads
		for( $i = 0xc0; $i < 0xff; $i++ )
		{
			$total += $counts[$i];
		}
		
		return $total;
	}
}


/* Fallback json_encode */

if (!function_exists('json_encode'))
{
	function json_encode($a=false)
	{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a))
		{
			if (is_float($a))
			{
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}
		
			if (is_string($a))
			{
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else return $a;
		}
		
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}
		
		$result = array();
		if ($isList)
		{
			foreach ($a as $v) $result[] = json_encode($v);
			return '[' . join(',', $result) . ']';
		}
		else
		{
			foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}

?>