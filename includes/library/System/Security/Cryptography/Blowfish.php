<?php

namespace System\Security\Cryptography
{
	class Blowfish extends \System\Object
	{
		public static function Hash($string, &$salt, $iterations = 4)
		{
			if( !isset($salt) || $salt == '' )
				$salt = \System\Security\Cryptography::GenerateRandomSalt(22,\System\Security\Cryptography::Salt_Charset_Blowfish);
			
			if( strlen($salt) > 22 )
				throw new \Exception('Provided salt is too long. Max length is 22.');
			
			if( $iterations < 4 || $iterations > 31 )
				throw new \Exception('Provided iteration count is invalid. It must be in the range of 4-31.');
			
			$s = '$2a$'.sprintf('%02d',$iterations).'$'.$salt;
			
			return crypt($string, $s);
		}
		
		public static function GetSaltCharset()
		{
			return './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		}
	}
}