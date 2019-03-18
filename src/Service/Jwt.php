<?php

namespace App\Service;

use \Firebase\JWT\JWT as JsonWebToken;

class Jwt
{
	/**
	 * @param array $values
	 * @param string $token
	 * @return string
	 * encode an array in jwt
	 */
	public static function encode(array $values, string $token)
	{
		return JsonWebToken::encode($values, $token);
	}
	
	/**
	 * @param string $encoded_json
	 * @param string $token
	 * @return bool|object
	 * encode a jwt in array
	 */
	public static function decode(string $encoded_json, string $token)
	{
		JsonWebToken::$leeway = 5;
		$decoded = JsonWebToken::decode($encoded_json, $token, ['HS256']);
		
		if ($decoded) {
			return $decoded;
		}
		
		return false;
	}
}
