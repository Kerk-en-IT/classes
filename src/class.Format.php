<?php
namespace KerkEnIT;

if (!defined('EOL_SPLIT')) :
	define('EOL_SPLIT', '- ');
endif;
if (!defined('EOL_SPLIT_SEARCH')) :
	define('EOL_SPLIT_SEARCH', ' - ');
endif;
if (!defined('EOL_SPLIT_REPLACE')) :
	define('EOL_SPLIT_REPLACE', '$#');
endif;

if (!defined('srcset')) :
	define('srcset', array(0.1, 0.25, 0.33, 0.5, 0.75, 1.0, 1.25, 1.5, 2.0, 3.0, 4.0));
endif;

/**
 * Format various of objects into the correct layout
 *
 * Formatting various objects into the expected output.
 *
 * @package    Classes
 * @subpackage Format
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2022 Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkennit.nl
 * @since      Class available since Release 1.0.57
 */

class Format
{

	/**
	 * Menu
	 * @deprecated in 1.3.107
	 *
	 * @param	string $url
	 * @param	string $name
	 * @return	string
	 */
	public static function Menu($url, $name)
	{
		global $data;
		//if((!isset($data[1]) || empty($data[1])) && $url == '') :
		//	return '<strong>' . $name . '</strong>';
		//elseif((isset($data[1]) && !empty($data[1])) && $url == $data[1]) :
		//	return '<strong>' . $name . '</strong>';
		//else :
		return '<span>' . $name . '</span>';
		//endif;
	}

	/**
	 * Gets the full name of a user
	 *
	 * @param	string $firstname
	 * @param	string $infix
	 * @param	string $lastname
	 * @return	string
	 */
	public static function Name($firstname, $infix, $lastname)
	{
		return trim($firstname . ' ' . trim($infix . ' ' . $lastname));
	}

	/**
	 * Gets the full name of a user including the name of the husband
	 *
	 * @param	string $firstname
	 * @param	string $infix
	 * @param	string $lastname
	 * @param	string $lastname_partner
	 * @param	int $gender
	 * @param	int $gender_partner
	 * @param	int|null $maritalstatus			Marital status person (Nullable)
	 * @return	string
	 */
	public static function NameMarried(?string $firstname = '', ?string $infix = null, ?string $lastname = null, ?string $lastname_partner = null, ?int $gender = null, ?int $gender_partner = null, ?int $maritalstatus = null)
	{
		return self::Name(($firstname ?? ''), ($infix ?? ''), ($gender_partner == 1 && $gender == 2 ? ($lastname_partner ?? '') . '-' . ($lastname ?? '') : ($lastname ?? '')));
	}

	/**
	 * Gets the full name of a user including the name of the husband
	 *
	 * @param	string $firstname				Firstname of person
	 * @param	string $infix					Infix of person
	 * @param	string $lastname					Last name of person
	 * @param	string $lastname_partner			Lastname of partner
	 * @param	int $gender						Gender of person
	 * @param	int $gender_partner				Last name of person
	 * @param	int|null $maritalstatus			Marital status person (Nullable)
	 * @param	int|null $age					Age of person (Nullable)
	 * @param	string|\DateTime|null $dateofbirth	Birthday. This can be empty (When `null` the age is used.)
	 * @param	string|\DateTime|null $dateofdeath	Date of death. Can be the date of death of the current date. Leave empty to use current date.
	 * @return	string
	 */
	public static function NameMarriedAge(?string $firstname = '', ?string $infix = null, ?string $lastname = null, ?string $lastname_partner = null, ?int $gender = null, ?int $gender_partner = null, ?int $maritalstatus = null, ?int $age = null, $dateofbirth = null, $dateofdeath = null)
	{

		$name_text =  self::NameMarried(firstname: $firstname, infix: $infix, lastname: $lastname, lastname_partner: $lastname_partner, gender: $gender, gender_partner: $gender_partner, maritalstatus: $maritalstatus);
		$age_text = '';
		if (($age === null || $age == 0) && $dateofbirth !== null) :
			$dateofbirth = DateTime::GetDate($dateofbirth);
			$dateofdeath = DateTime::GetDate($dateofdeath);
			$age = $dateofbirth->diff($dateofdeath)->y;
		endif;
		if (is_numeric($age) && $age > 0) :
			$age_text = sprintf('(%d jaar)', $age);
		endif;
		return trim($name_text . ' ' . $age_text);
	}

	/**
	 * Gets the full name of a user including the name of the husband.
	 * Almost equal to ```self::NameMarriedAge```. But this has a prefix for the gender.
	 *
	 * @param	string $firstname				Firstname of person
	 * @param	string $infix					Infix of person
	 * @param	string $lastname					Last name of person
	 * @param	string $lastname_partner			Lastname of partner
	 * @param	int $gender						Gender of person
	 * @param	int $gender_partner				Last name of person
	 * @param	int|null $maritalstatus			Marital status person (Nullable)
	 * @param	int|null $age					Age of person (Nullable)
	 * @param	string|DateTime|null $dateofbirth	Birthdate. This can be empty (When `null` the age is used.)
	 * @param	string|DateTime|null $dateofdeath	Date of death. Can be the date of death of the current date. Leave empty to use current date.
	 * @return	string
	 */
	public static function NameMarriedAgeGender(?string $firstname = null, ?string $infix = null, ?string $lastname = null, ?string $lastname_partner = null, ?int $gender = null, ?int $gender_partner = null, ?int $maritalstatus = null, ?int $age = null, $dateofbirth = null, $dateofdeath = null)
	{

		$name_text =  self::NameMarriedAge(firstname: $firstname, infix: $infix, lastname: $lastname, lastname_partner: $lastname_partner, gender: $gender, gender_partner: $gender_partner, maritalstatus: $maritalstatus, age: $age, dateofbirth: $dateofbirth, dateofdeath: $dateofdeath);
		if (($age === null || $age == 0) && $dateofbirth !== null) :
			$dateofbirth = DateTime::GetDate($dateofbirth);
			$dateofdeath = DateTime::GetDate($dateofdeath);
			$age = $dateofbirth->diff($dateofdeath)->y;
		endif;
		$gender_text = self::GetGender($gender, $age);
		return trim($gender_text . ' ' . $name_text);
	}


	/**
	 * Gets the abbreviation of the name
	 *
	 * @param  mixed $firstname
	 * @param  mixed $infix
	 * @param  mixed $lastname
	 * @return	string
	 */
	public static function NameAbbreviation($firstname, $infix, $lastname)
	{
		$rtn = '';
		$full_name = trim(str_replace("'", ' ', str_replace('-', ' ', $firstname . ' ' . trim($infix . ' ' . $lastname))));
		if (!empty($full_name)) :
			foreach (explode(' ', $full_name) as $name) :
				$rtn .= substr($name, 0, 1);
			endforeach;
		endif;
		return $rtn;
	}

	/**
	 * Gets the surname for sorting purposes
	 *
	 * @param  mixed $infix
	 * @param  mixed $lastname
	 * @return	string
	 */
	public static function LastName($infix, $lastname)
	{
		return rtrim($lastname . ', ' . $infix, ', ');
	}

	/**
	 * Get's the full surname
	 *
	 * @param  mixed $infix
	 * @param  mixed $lastname
	 * @return	string
	 */
	public static function InfixLastName($infix, $lastname)
	{
		return ltrim($infix . ' ' . $lastname, ' ');
	}

	/**
	 * Gets the address from s street and house number
	 *
	 * @param	string $street
	 * @param	string $number
	 * @param	string $suffix Can be empty
	 * @return	string
	 */
	public static function Address($street, $number, $suffix = '')
	{
		return trim($street . ' ' . $number . $suffix);
	}

	/**
	 * Get's the phone number. Use full for ```tel:``` hyperlinks
	 *
	 * @param	string $phone
	 * @return	string
	 */
	public static function PhoneURL($phone)
	{
		$str = str_replace(array('(', ')', ' ', '-', '+'), array('', '', '', '', '00'), $phone);
		if (strlen($str) == 9 && !str_starts_with($str, '0')) :
			$str = '0' . $str;
		endif;
		return $str;
	}

	/**
	 * Formats the phone number to a standard format
	 * Don't forget to set the locale before using this function ```\Locale::setDefault('nl_NL');```
	 *
	 * @param	string|null $phone
	 * @param	string|null $country
	 * @return	string|null
	 */
	#[Depends('\libphonenumber\PhoneNumberUtil.
	See https://github.com/giggsey/libphonenumber-for-php.
	To install with composer run: `composer require giggsey/libphonenumber-for-php`')]
	public static function PhoneNumber(string|null $phone, string|null $country = null)
	{
		$lang = \Locale::getDefault();
		if(strlen($lang) >= 5) :
			$lang = substr($lang, 3, 2);
		endif;
		if($country === null) :
			$country = substr(\Locale::getDefault(), 3, 2);
		endif;
		if($phone !== null) :
			$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$phoneNumberObject = $phoneNumberUtil->parse($phone, $country);
				if($phoneNumberUtil->isValidNumber($phoneNumberObject)) :
					if($country == $lang) :
						//var_dump(($country === null ? $lang : null));
						$phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::NATIONAL);
					else :
						$phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
					endif;
				else :
					$phone = '#' . $phone;
				endif;
				//var_dump($phone);
				//die();
			} catch (\libphonenumber\NumberParseException $e) {
				Console::error($e);
			} finally {
				return $phone;
			}
		endif;
		return null;
	}
	/**
	 * Format the zipcode to the correct format
	 *
	 * @param	string|null $zipcode
	 * @return	string|null
	 */
	public static function Zipcode(string|null $zipcode)
	{
		if( $zipcode === null || $zipcode === '' || empty($zipcode)) :
			return null;
		endif;
		$zipcodeStrip = preg_replace('/[^0-9a-zA-Z]/', '', $zipcode);
		if( strlen($zipcodeStrip) == 6 && !str_contains($zipcodeStrip, ' ')) :
			if(\is_numeric(substr($zipcodeStrip, 0, 4)) && !\is_numeric(substr($zipcodeStrip, 4, 2))) :
				// When the first 4 characters are numbers and the last 2 characters are numbers
				$zipcode = substr($zipcodeStrip, 0, 4) . ' ' . strtoupper(substr($zipcodeStrip, 4, 2));
			endif;
		endif;
		return $zipcode;
	}

	/**
	 * Format number to currency
	 *
	 * @param  mixed $number
	 * @return	string 9,99
	 */
	public static function Currency($number)
	{
		$money = number_format($number, 2, ',', '.');
		return $money;
	}


	/**
	 * Format Money to Euro
	 *
	 * @param  mixed $number
	 * @return	string € 9,99
	 */
	public static function Money($number)
	{
		return "&euro; " . Format::Currency($number);
	}

	/**
	 * Convert variable to bool
	 *
	 * @param int|bool|string $object
	 * * ```string``` ```yes, ja, y, j, on``` OR  ```no, nee, n, off```
	 * * ```int``` ```1``` OR ```0```
	 * * ```bool``` ```true``` OR ```false```
	 * @return bool
	 */
	public static function ConvertToBool($object)
	{
		if (is_numeric($object)) :
			$object = ($object == 1);
		elseif (is_string($object)) :
			$object = strtolower($object);
			if ($object == 'y' || $object == 'j' || $object == 'yes' || $object == 'ja' ||  $object == 'on' ||  $object == '1') :
				$object = true;
			elseif ($object == 'n' || $object == 'no' || $object == 'nee' ||  $object == 'off' ||  $object == '0') :
				$object = false;
			endif;
		endif;

		if (is_bool($object)) :
			return $object;
		endif;
		return false;
	}

	/**
	 * Gets the text ```Ja``` or ```Nee```
	 *
	 * @param	bool|mixed $object
	 * @return	string
	 */
	public static function YesNo($object)
	{
		$object = Format::ConvertToBool($object);
		if (is_bool($object)) :
			return ($object ? 'Ja' : 'Nee');
		endif;
		return 'Nee';
	}

	/**
	 * Mark the checkbox as checked
	 *
	 * @param	bool|mixed $object
	 * @return	string
	 */
	public static function Checked($object)
	{
		$object = Format::ConvertToBool($object);

		if (is_bool($object)) :
			return ($object ? 'checked="checked"' : '');
		endif;
		return '';
	}

	/**
	 * Check if mail address is from a printer webservice
	 *
	 * @param string $email E-mail adres or GUID of user ID

	 * @return bool  ```true``` when is from a printer webservice. Otherwise ```false```
	 */
	public static function IsPrinter($email)
	{
		$email = self::GetUserMail($email);
		// make sure we've got a valid email
		if (!empty($email)) :
			// split on @ and return last value of array (the domain)
			if (str_contains($email, '@')) :
				//$domain = array_pop(explode('@', $email));
				return str_contains($email, 'print');
			endif;
		endif;
		return false;
	}

	/**
	 * Create a GUID with dashes
	 *
	 * @return	string GUID with dashes
	 */
	public static function uuid(?string $content = null): string
	{
		if($content !== null) :
			return self::md5_to_uuid(md5($content));
		endif;
		if (function_exists('com_create_guid') === true) {
			return trim(com_create_guid(), '{}');
		}

		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	/**
	 * @deprecated use uuid() instead
	 * Create a GUID with dashes
	 *
	 * @return	string GUID with dashes
	 */
	#[\Deprecated(message: "use uuid() instead")]
	public static function GUID(): string
	{
		return self::uuid();
	}


	/**
	 * Check if a given string is a valid UUID
	 *
	 * @param   string  $uuid   The string to check
	 * @return  boolean
	 */
	public static function is_uuid($uuid)
	{

		if (!is_string($uuid) || (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', strtolower($uuid)) !== 1)) {
			return false;
		}

		return true;
	}

	/**
	 * @deprecated use is_uuid() instead
	 * Check if a given string is a valid GUID
	 *
	 * @param   string  $guid   The string to check
	 * @return  boolean
	 */
	#[\Deprecated(message: "use is_uuid() instead")]
	public static function is_guid($uuid)
	{
		return self::is_uuid($uuid);
	}

	/**
	 * Convert a MD5 hash to a UUID
	 *
	 * @param	string $md5 MD5 hash
	 * @return	string UUID
	 */
	public static function md5_to_uuid($md5): string
	{
		if (!is_string($md5) || strlen($md5) !== 32) {
			throw new \InvalidArgumentException('Input must be a 32-character hexadecimal string.');
		}
		$md5 = substr($md5, 0, 8) . '-' .
			substr($md5, 8, 4) . '-' .
			substr($md5, 12, 4) . '-' .
			substr($md5, 16, 4) . '-' .
			substr($md5, 20);
		return $md5;
	}


	/**
	 * Convert a UUID to a MD5 hash
	 *
	 * @param	string|null $uuid UUID
	 * @return	string|null MD5 hash or null if not valid UUID
	 */
	public static function uuid_to_md5(?string $uuid): string|null
	{
		if (!is_string($uuid) || self::is_uuid($uuid) === false || strlen($uuid) !== 36) {
			//throw new \InvalidArgumentException('Input must be a 36-character hexadecimal string.');
			return null;
		}

		return str_replace('-', '', $uuid);
	}

	/**
	 * Gets the name from the user when it's a GUID
	 *
	 * @param string $email E-mail adres or GUID of user ID

	 * @return	string|null  Returns the name of the user. If not a valid e-mail it returns the default data
	 */
	public static function GetUserName($email)
	{
		if (self::is_uuid($email)) :
			global $mysqli;
			global $account_ID;
			if (self::is_uuid($account_ID)) :
				if ($result = $mysqli->query("SELECT `gender`, `firstname`, `infix`, `lastname` FROM `users` WHERE `ID` = '$email' AND `account_ID` = '$account_ID'")) :
					$user = $result->fetch_object();
					return Format::Name($user->firstname, $user->infix, $user->lastname);
				endif;
			endif;
		endif;
		return $email;
	}

	/**
	 * Gets the mail address from the user when it's a GUID
	 *
	 * @param string $email E-mail adres or GUID of user ID

	 * @return	string|null  Returns the e-mail of the user. If not a valid e-mail it returns ```null```
	 */
	public static function GetUserMail($email)
	{
		if (self::is_uuid($email)) :
			global $mysqli;
			global $account_ID;
			if (self::is_uuid($account_ID)) :
				if ($result = $mysqli->query("SELECT `email` FROM `users` WHERE `ID` = '$email' AND `account_ID` = '$account_ID'")) :
					$email = $result->fetch_object()->email;
				endif;
			endif;
		endif;
		// make sure we've got a valid email
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) :
			return $email;
		endif;
		return NULL;
	}

	/**
	 * Encrypt a string
	 * @deprecated @see Cryptography::encrypt()
	 */
	public static function encrypt($string)
	{
		return Cryptography::encrypt($string);
	}
	/**
	 * Decrypt a string
	 * @deprecated @see Cryptography::decrypt()
	 */
	public static function decrypt($encrypted)
	{
		return Cryptography::decrypt($encrypted);
	}


	/**
	 * pluralize s string
	 *
	 * @param	int $count
	 * @param	string $single text without pluralized string
	 * @param	string $double pluralized text
	 * @return	string
	 */
	public static function pluralize($count, $single, $double)
	{
		return $count . ($count == 1 ? $single : $double);
	}




	/**
	 * Eclipse a text
	 *
	 * @param	string $string source text
	 * @param	int $length length to cut the text
	 * @return	string
	 */
	public static function Eclipse($string, $length)
	{
		if (strlen($string) > $length + 3) :
			return substr($string, 0, $length) . '...';
		else :
			return $string;
		endif;
	}

	/**
	 * Escapte the input value
	 *
	 * @param	string $value
	 * @param	string $escape
	 * @return	string
	 */
	public static function InputValue($value, $escape)
	{
		return str_replace($escape, "\$escape", $value);
	}

	/**
	 * Get a list of subscription types
	 *
	 * @param	string $sender
	 * @return	string
	 */
	public static function subscriptionTypes($sender)
	{
		$return = '';
		$items = explode(',', $sender);
		foreach ($items as $index => $item) {
			$return .= sprintf('%d %s | ', ($index + 1), $item);
		}
		return substr($return, 0, -3);
	}
	/**
	 * Get the attendee status for the timetable subscription
	 *
	 * @param	int $index
	 * @param	int $count
	 * @return	string
	 */
	public static function Status($index, $count = 2)
	{
		switch ($index) {
			case 0:
				return 'niet';
			case 1:
				if ($count == 3) :
					return 'misschien';
				else :
					return 'wel';
				endif;
			case 2:
				if ($count == 3) :
					return 'wel';
				else :
					return 'misschien';
				endif;
			default:
				return 'misschien';
		}
	}


	/**
	 * Gets the hex color by class name
	 *
	 * @param	string $class
	 * @return	string
	 */
	public static function HexTextColor($class)
	{
		switch ($class) {
			case 'white':
				return '#000';
			case 'yellow':
			case 'gray':
				return '#666';
			case 'black':
				return '#ccc';
			default:
				return '#fff';
		}
	}

	/**
	 * Gets the hexadecimal color by class name.
	 * Especially for the border.
	 *
	 * @param	string $class
	 * @return	string
	 */
	public static function HexBorderColor($class)
	{
		switch ($class) {
			case 'green':
				return '#29b765';
			case 'yellow':
				return '#deb200';
			case 'orange':
				return '#d67520';
			case 'red':
				return '#cf4436';
			case 'white':
			case 'gray':
				return '#dfe8f1';
			case 'black':
				return '#000';
			case 'blue':
			case 'blue-alt':
				return '#5388d1';
			case 'purple':
				return '#7a3ecc';
			default:
				return '#00b19b';
		}
	}

	/**
	 * Gets the hexadecimal color by class name.
	 * Especially for the background.
	 *
	 * @param  mixed $class
	 * @return void
	 */
	public static function HexBackgroundColor($class)
	{
		switch ($class) {
			case 'green':
				return '#2ecc71';
			case 'yellow':
				return '#fc0';
			case 'orange':
				return '#e67e22';
			case 'red':
				return '#e74c3c';
			case 'white':
				return '#fff';
			case 'gray':
				return '#efefef';
			case 'black':
				return '#2d2d2d';
			case 'blue':
			case 'blue-alt':
				return '#65a6ff';
			case 'purple':
				return '#984dff';
			case 'primary':
			default:
				return '#00bca4';
		}
	}

	/**
	 * Get Contrast Color
	 *
	 * @param	string $hexColor
	 * @return	string
	 */
	public static function GetContrastColor($hexColor)
	{
		$prefix = '';
		if ($hexColor[0] === '#') :
			$hexColor = ltrim($hexColor, '#');
			$prefix = '#';
		endif;
		// hexColor RGB
		$R1 = hexdec(substr($hexColor, 0, 2));
		$G1 = hexdec(substr($hexColor, 2, 2));
		$B1 = hexdec(substr($hexColor, 4, 2));

		// Black RGB
		$blackColor = "#000000";
		$R2BlackColor = hexdec(substr($blackColor, 1, 2));
		$G2BlackColor = hexdec(substr($blackColor, 3, 2));
		$B2BlackColor = hexdec(substr($blackColor, 5, 2));

		// Calc contrast ratio
		$L1 = 0.2126 * pow($R1 / 255, 2.2) +
			0.7152 * pow($G1 / 255, 2.2) +
			0.0722 * pow($B1 / 255, 2.2);

		$L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
			0.7152 * pow($G2BlackColor / 255, 2.2) +
			0.0722 * pow($B2BlackColor / 255, 2.2);

		$contrastRatio = 0;
		if ($L1 > $L2) {
			$contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
		} else {
			$contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
		}
		//varDump($contrastRatio);
		// If contrast is more than 5, return black color
		if ($contrastRatio > 7) {
			return $prefix . '000000';
		} else {
			// if not, return white color.
			return $prefix . 'FFFFFF';
		}
	}


	/**
	 * iCal Color in W3C.REC-css3-color-20110607 format
	 *
	 * @param	string $class
	 * @return	string color name
	 */
	public static function iCalColor($class)
	{
		switch ($class) {
			case 'green':
				return 'Green';
			case 'yellow':
				return 'Yellow';
			case 'orange':
				return 'Red';
			case 'red':
				return 'Maroon';
			case 'white':
				return 'White';
			case 'gray':
				return 'Gray';
			case 'black':
				return 'Black';
			case 'blue':
			case 'blue-alt':
				return 'Blue';
			case 'purple':
				return 'Purple';
			case 'primary':
			default:
				return 'Teal';
		}
	}

	/**
	 * Gets the CSS style from a specific class name
	 *
	 * @param	string $class
	 * @return	string
	 */
	public static function ColorCSS($class)
	{
		return "color:" . Format::HexTextColor($class) . ";border-color:" . Format::HexBorderColor($class) . ";background:" . Format::HexBackgroundColor($class) . ";";
	}

	/**
	 * Correct the sentence with adding a period when necessary. and make it capitalize the first letter.
	 *
	 * @param  mixed $string
	 * @return mixed
	 */
	public static function CorrectSentence($string)
	{
		$string = trim($string);
		if (!str_ends_with($string, '.') && !str_ends_with($string, '!') && !str_ends_with($string, '?')) :
			$string .= '.';
		endif;

		return ucfirst($string);
	}

	public static function CorrectIntention($string)
	{
		$string = str_replace('v.z.', 'voor zaliger', $string);
		$string = str_replace('zal.', 'voor zaliger', $string);
		$string = str_replace('Zal.', 'voor zaliger', $string);
		$string = str_replace('lev.', 'levenden', $string);
		$string = str_replace('overl.', 'overledenen', $string);
		$string = str_replace('fam.', 'familie', $string);
		$string = str_replace('mevr.', 'mevrouw', $string);
		$string = str_replace('dhr.', 'de heer', $string);
		$string = str_replace('6 wkn dienst', 'zeswekendienst', $string);
		$string = str_replace('zes wkn dienst', 'zeswekendienst', $string);
		$string = str_replace('zes weken dienst', 'zeswekendienst', $string);

		return self::CorrectSentence($string);
	}

	/**
	 * Gender enum
	 *
	 * @return	array
	 */
	public static function Gender()
	{
		return array(
			'man'				=> 'Man',
			'woman'				=> 'Vrouw',
			'sister'			=> 'Zuster',
			'brother'			=> 'Broeder',
			'deacon'			=> 'Diaken',
			'chaplain'			=> 'Kapelaan',
			'pastor'			=> 'Pastoor',
			'dean'				=> 'Deken',
			'plaster-dean'		=> 'Plebaan-deken',
			'plaster'			=> 'Plebaan',
			'vicar'				=> 'Vicaris',
			'vicar-general'		=> 'Vicaris-generaal',
			'auxiliary_bishop'	=> 'Hulpbisschop',
			'bishop'			=> 'Bisschop',
			'archbishop'		=> 'Aartsbisschop',
			'cardinal'			=> 'Kardinaal',
			'pope'				=> 'Paus',
			'choir'				=> 'Koor'
		);
	}

	/**
	 * Gender enum
	 *
	 * @return	array
	 */
	public static function Genders()
	{
		return array(
			'man'				=> 'Mannen',
			'woman'				=> 'Vrouwen',
			'sister'			=> 'Zusters',
			'brother'			=> 'Broeders',
			'deacon'			=> 'Diakens',
			'chaplain'			=> 'Kapelaans',
			'pastor'			=> 'Pastoors',
			'dean'				=> 'Dekens',
			'plaster-dean'		=> 'Plebaan-dekens',
			'plaster'			=> 'Plebanen',
			'vicar'				=> 'Vicarissen',
			'vicar-general'		=> 'Vicaris-generaals',
			'auxiliary_bishop'	=> 'Hulpbisschoppen',
			'bishop'			=> 'Bisschoppen',
			'archbishop'		=> 'Aartsbisschoppen',
			'cardinal'			=> 'Kardinalen',
			'pope'				=> 'Paus',
			'choir'				=> 'Koren'
		);
	}

	/**
	 * Title enum
	 *
	 * @return	array
	 */
	public static function Title()
	{
		return array(
			'man'				=> 'Dhr.',
			'woman'				=> 'Mw.',
			'sister'			=> 'Zuster',
			'brother'			=> 'Broeder',
			'deacon'			=> 'Diaken',
			'chaplain'			=> 'Kapelaan',
			'pastor'			=> 'Pastoor',
			'dean'				=> 'Deken',
			'plaster'			=> 'Plebaan',
			'plaster-dean'		=> 'Plebaan-deken',
			'vicar'				=> 'Vicaris',
			'vicar-general'		=> 'Vicaris-generaal',
			'auxiliary_bishop'	=> 'Hulpbisschop',
			'bishop'			=> 'Bisschop',
			'archbishop'		=> 'Aartsbisschop',
			'cardinal'			=> 'Kardinaal',
			'choir'				=> 'Koor'
		);
	}

	public static function AddressLetterhead()
	{
		return array(
			'man'				=> 'Geachte heer',
			'woman'				=> 'Geachte mevrouw',
			'sister'			=> 'Eerwaarde Zuster',
			'brother'			=> 'Eerwaarde Broeder',
			'deacon'			=> 'Eerwaarde Heer Diaken',
			'chaplain'			=> 'Weleerwaarde Heer Kapelaan',
			'pastor'			=> 'Zeereerwaarde Heer Pastoor',
			'dean'				=> 'Zeereerwaarde Heer Deken',
			'plaster-dean'		=> 'Hoogeerwaarde Heer Plebaan-deken',
			'plaster'			=> 'Hoogeerwaarde Heer Plebaan',
			'vicar'				=> 'Hoogeerwaarde Heer Bisschoppelijk Vicaris',
			'vicar-general'		=> 'Hoogwaardige Heer Monseigneur',
			'auxiliary_bishop'	=> 'Zijne Hoogwaardige Excellentie Monseigneur',
			'bishop'			=> 'Zijne Hoogwaardige Excellentie Monseigneur',
			'archbishop'		=> 'Zijne Hoogwaardige Excellentie Monseigneur',
			'cardinal'			=> 'Zijne Eminentie',
			'pope'				=> 'Heilige Vader',
			'choir'				=> 'Geacht koor'
		);
	}



	/**
	 * Function enum
	 *
	 * @return	array
	 */
	public static function Function()
	{
		return array(
			'man'				=> '',
			'woman'				=> '',
			'sister'			=> 'Zuster',
			'brother'			=> 'Broeder',
			'deacon'			=> 'Diaken',
			'chaplain'			=> 'Kapelaan',
			'pastor'			=> 'Pastoor',
			'dean'				=> 'Deken',
			'plaster'			=> 'Plebaan',
			'plaster-dean'		=> 'Plebaan-deken',
			'vicar'				=> 'Vicaris',
			'vicar-general'		=> 'Vicaris-generaal',
			'auxiliary_bishop'	=> 'Hulpbisschop',
			'bishop'			=> 'Bisschop',
			'archbishop'		=> 'Aartsbisschop',
			'cardinal'			=> 'Kardinaal',
			'pope'				=> 'Paus',
			'choir'				=> 'Koor'
		);
	}

	/**
	 * Function enum
	 *
	 * @return	array
	 */
	public static function FunctionChoir()
	{
		return array(
			'man'				=> 'Cantor',
			'woman'				=> 'Cantor',
			'sister'			=> 'Zuster',
			'brother'			=> 'Broeder',
			'deacon'			=> 'Diaken',
			'chaplain'			=> 'Kapelaan',
			'pastor'			=> 'Pastoor',
			'dean'				=> 'Deken',
			'plaster'			=> 'Plebaan',
			'plaster-dean'		=> 'Plebaan-deken',
			'vicar'				=> 'Vicaris',
			'vicar-general'		=> 'Vicaris-generaal',
			'auxiliary_bishop'	=> 'Hulpbisschop',
			'bishop'			=> 'Bisschop',
			'archbishop'		=> 'Aartsbisschop',
			'cardinal'			=> 'Kardinaal',
			'pope'				=> 'Paus',
			'choir'				=> 'Koor	'
		);
	}

	/**
	 * Function enum
	 *
	 * @return	array
	 */
	public static function FunctionMusician()
	{
		return array(
			'man'				=> 'Organist',
			'woman'				=> 'Organiste',
			'sister'			=> 'Zuster',
			'brother'			=> 'Broeder',
			'deacon'			=> 'Diaken',
			'chaplain'			=> 'Kapelaan',
			'pastor'			=> 'Pastoor',
			'dean'				=> 'Deken',
			'plaster'			=> 'Plebaan',
			'plaster-dean'		=> 'Plebaan-deken',
			'vicar'				=> 'Vicaris',
			'vicar-general'		=> 'Vicaris-generaal',
			'auxiliary_bishop'	=> 'Hulpbisschop',
			'bishop'			=> 'Bisschop',
			'archbishop'		=> 'Aartsbisschop',
			'cardinal'			=> 'Kardinaal',
			'pope'				=> 'Paus',
			'choir'				=> 'Koor'
		);
	}

	/**
	 * Marital Status
	 *
	 * @return	array
	 */
	public static function MaritalStatus($gender = NULL, $empty = 'Niet bekend')
	{
		switch ($gender):
			case 1:
				return array(
					'' => $empty,
					'1' => 'Gehuwd',
					'2' => 'Gescheiden',
					'3' => 'Ongehuwd',
					'4' => 'Weduwnaar',
					'0' => 'Niet bekend'
				);
				break;
			case 2:
				return array(
					'' => $empty,
					'1' => 'Gehuwd',
					'2' => 'Gescheiden',
					'3' => 'Ongehuwd',
					'4' => 'Weduwe',
					'0' => 'Niet bekend'
				);
				break;
			default:
				return array(
					'' => $empty,
					'1' => 'Gehuwd',
					'2' => 'Gescheiden',
					'3' => 'Ongehuwd',
					'4' => 'Verweduwd',
					'0' => 'Niet bekend'
				);
				break;
		endswitch;
	}

	public static function AnointingOfTheSick()
	{
		return array(
			'' => '-- Kies --',
			'1' => 'Ja',
			'2' => 'Nee',
			'0' => 'Niet bekend'
		);
	}


	/**
	 * Order genders and function by importance
	 *
	 * @param  mixed $gender
	 * @return void
	 */
	public static function GetGenderOrder($gender)
	{
		$max = '99';
		$array = array(
			'cardinal'			=> '01',
			'archbishop'		=> '02',
			'bishop'			=> '03',
			'auxiliary_bishop'	=> '04',
			'vicar-general'		=> '05',
			'vicar'				=> '06',
			'plaster'			=> '07',
			'plaster-dean'		=> '07',
			'dean'				=> '08',
			'pastor'			=> '09',
			'chaplain'			=> '10',
			'deacon'			=> '11',
			'brother'			=> '12',
			'sister'			=> '13',
			'man'				=> '14',
			'woman'				=> '15',
			'choir'				=> '16',
			'max'				=> $max
		);

		try {
			if (isset($gender) && $gender !== null) :
				if (is_array($gender) && isset($gender['gender']) && !empty($gender['gender'])) :
					$gender = $gender['gender'];
					if (is_array($gender) && isset($gender['gender']) && !empty($gender['gender'])) :
						$gender = $gender['gender'];
					endif;
				elseif (!is_string($gender) && (is_object($gender) || gettype($gender) == "object")) :
					$gender = $gender->gender;
					if (!is_string($gender) && (is_object($gender) || gettype($gender) == "object")) :
						$gender = $gender->gender;
					else :
						$gender = 'max';
					endif;
				else :
					$gender = 'max';
				endif;
			else :
				$gender = 'max';
			endif;

			if (isset($gender) && is_string($gender) && array_key_exists($gender, $array)) :
				return $array[$gender];
			endif;
		} catch (Exception $e) {
			return $max;
		} finally {
			return $max;
		}
	}

	/**
	 * Gets the name of the gender
	 *
	 * @param	string $gender
	 * @return	string
	 */
	public static function GetGenderName($gender)
	{
		return self::Gender()[$gender];
	}

	/**
	 * Gets the name of the gender
	 *
	 * @param	int $gender,
	 * @param	int $age
	 * @return	string
	 */
	public static function GetGender($gender, $age = 0)
	{
		if ($age < 65) :
			switch ($gender):
				case 1:
					return 'De heer';
				case 2:
					return 'Mevrouw';
				case 3:
					return 'Zuster';
				case 4:
					return 'Broeder';
				case 5:
					return 'Diaken';
				case 6:
					return 'Kapelaan';
				case 7:
					return 'Pastoor';
				case 8:
					return 'Deken';
				case 9:
					return 'Plebaan-deken';
				case 10:
					return 'Plebaan';
				case 11:
					return 'Vicaris';
				case 12:
					return 'Vicaris-generaal';
				case 13:
					return 'Hulpbisschop';
				case 14:
					return 'Bisschop';
				case 15:
					return 'Aartsbisschop';
				case 16:
					return 'Kardinaal';
				case 17:
					return 'Heilige Vader de Paus';
				default:
					return '';
			endswitch;
		elseif ($age < 75) :
			switch ($gender):
				case 1:
					return 'De heer';
				case 2:
					return 'Mevrouw';
				case 3:
					return 'Zuster';
				case 4:
					return 'Broeder';
				case 5:
					return 'Emeritus diaken';
				case 6:
					return 'Emeritus kapelaan';
				case 7:
					return 'Emeritus pastoor';
				case 8:
					return 'Oud-deken';
				case 9:
					return 'Oud-plebaan-deken';
				case 10:
					return 'Oud-plebaan';
				case 11:
					return 'Oud-vicaris';
				case 12:
					return 'Oud-vicaris-generaal';
				case 13:
					return 'Emeritus hulpbisschop';
				case 14:
					return 'Emeritus bisschop';
				case 15:
					return 'Emeritus aartsbisschop';
				case 16:
					return 'Emeritus kardinaal';
				case 17:
					return 'Heilige Vader de Paus';
				default:
					return '';
			endswitch;
		else :
			switch ($gender):
				case 1:
					return 'De heer';
				case 2:
					return 'Mevrouw';
				case 3:
					return 'Zuster';
				case 4:
					return 'Broeder';
				case 5:
					return 'Diaken';
				case 6:
					return 'Kapelaan';
				case 7:
					return 'Emeritus pastoor';
				case 8:
					return 'Oud-deken';
				case 9:
					return 'Oud-plebaan-deken';
				case 10:
					return 'Oud-plebaan';
				case 11:
					return 'Oud-vicaris';
				case 12:
					return 'Oud-vicaris-generaal';
				case 13:
					return 'Emeritus hulpbisschop';
				case 14:
					return 'Emeritus bisschop';
				case 15:
					return 'Emeritus aartsbisschop';
				case 16:
					return 'Emeritus kardinaal';
				case 17:
					return 'Heilige Vader de Paus';
				default:
					return '';
			endswitch;
		endif;
	}

	public static function GetGenderTitle($gender)
	{
		return (array_values(self::Title()))[$gender - 1];
	}

	/**
	 * Gets the name of a function
	 *
	 * @param	string $gender
	 * @return	string
	 */
	public static function GetFunction($gender)
	{
		if (!empty($gender)) :
			return self::Function()[$gender];
		else :
			return '';
		endif;
	}

	/**
	 * Gets the name of a function for the choir
	 *
	 * @param	string $gender
	 * @return	string
	 */
	public static function GetFunctionChoir($gender)
	{
		return self::FunctionChoir()[$gender];
	}

	/**
	 * Gets the name of a function for the musician
	 *
	 * @param	string $gender
	 * @return	string
	 */
	public static function GetFunctionMusician($gender)
	{
		return self::FunctionMusician()[$gender];
	}

	/**
	 * Gets the title of a person
	 *
	 * @param	string $gender
	 * @return void
	 */
	public static function GetTitle($gender)
	{
		return self::Title()[$gender];
	}

	/**
	 * Gets the address letterhead of a person
	 *
	 * @param	string $gender
	 * @return void
	 */
	public static function GetAddressLetterhead($gender)
	{
		return self::AddressLetterhead()[$gender];
	}



	/**
	 * Gets the date and time from when the mass starts and add the church.
	 *
	 * @param  mixed $start
	 * @param  mixed $church
	 * @return void
	 */
	public static function MassAndChurch($start, $church)
	{
		return DateTime::FullDateAt($start) . ' in de ' . $church;
	}

	/**
	 * Get the information of a mass my ID
	 *
	 * @param	string|guid $masses_ID
	 * @return object|null
	 */
	public static function GetMass($masses_ID)
	{
		$sql = "SELECT `masses`.`start`, `church`.`title` AS `church` FROM `masses` INNER JOIN `church` ON `church`.`ID` =  `masses`.`church_ID` AND `church`.`account_ID` = `masses`.`account_ID` WHERE `masses`.`ID` = '$masses_ID' AND `masses`.`account_ID` = '" . (string)$_SESSION['account_ID'] . "'";
		global $mysqli;
		if ($result = $mysqli->query($sql)) :
			$row = $result->fetch_object();
			return $row;
		endif;
		return NULL;
	}

	/**
	 * Trim a text by a specific length. Based on the last full word.
	 * @deprecated 1.3.107 @see wordwrap()
	 *
	 * @param  string | null $s Input text
	 * @param  int $max_length Maximum length to trim.
	 * @return void
	 */
	public static function Trim(string| null $s, int $max_length = 300)
	{
		if ($s === null) return '';
		if (strlen($s) > $max_length) :
			$offset = ($max_length - 3) - strlen($s);
			$s = substr($s, 0, strrpos($s, ' ', $offset)) . '...';
		endif;
		return $s;
	}

	/**
	 * Clean the filename by removing all special charters
	 *
	 * @param	string $name
	 * @return	string
	 */
	public static function CleanFileName($name)
	{
		if (strpos($string = htmlentities($name, ENT_QUOTES, 'UTF-8'), '&') !== false) {
			$name = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
		}

		// remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
		$name = str_replace(array_merge(
			array_map('chr', range(0, 31)),
			array('<', '>', ':', '"', "'", '/', '\\', '|', '?', '*')
		), '', html_entity_decode($name));
		// maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
		$ext = pathinfo($name, PATHINFO_EXTENSION);
		$name = mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
		return $name;
	}


	public static function removeEmoji($string)
	{
		// Match Enclosed Alphanumeric Supplement
		$regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
		$clear_string = preg_replace($regex_alphanumeric, '', $string);

		// Match Miscellaneous Symbols and Pictographs
		$regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$clear_string = preg_replace($regex_symbols, '', $clear_string);

		// Match Emoticons
		$regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$clear_string = preg_replace($regex_emoticons, '', $clear_string);

		// Match Transport And Map Symbols
		$regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
		$clear_string = preg_replace($regex_transport, '', $clear_string);

		// Match Supplemental Symbols and Pictographs
		$regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
		$clear_string = preg_replace($regex_supplemental, '', $clear_string);

		// Match Miscellaneous Symbols
		$regex_misc = '/[\x{2600}-\x{26FF}]/u';
		$clear_string = preg_replace($regex_misc, '', $clear_string);

		// Match Dingbats
		$regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
		$clear_string = preg_replace($regex_dingbats, '', $clear_string);

		$clear_string = iconv('UTF-8', 'ISO-8859-15//IGNORE', $clear_string);
		$clear_string = preg_replace('/\s+/', ' ', $clear_string);
		return iconv('ISO-8859-15', 'UTF-8', $clear_string);
	}
	/**
	 *
	 * Create SEO friendly URL slug
	 *
	 * @param string $text Input name
	 * @param string $divider
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function slugify(string $text, string $divider = '-')
	{
		$text = html_entity_decode($text);
		$text = str_replace("'", '', $text);
		$text = Format::removeEmoji($text);

		// replace non letter or digits by divider
		$text = preg_replace('~[^\pL\d]+~u', $divider, $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, $divider);

		// remove duplicate divider
		$text = preg_replace('~-+~', $divider, $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return __('n-a');
		}
		return $text;
		return Format::remove_accents($text);
	}

	public static function remove_accents($string)
	{
		if (!preg_match('/[\x80-\xff]/', $string))
			return $string;

		$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(195) . chr(128) => 'A',
			chr(195) . chr(129) => 'A',
			chr(195) . chr(130) => 'A',
			chr(195) . chr(131) => 'A',
			chr(195) . chr(132) => 'A',
			chr(195) . chr(133) => 'A',
			chr(195) . chr(135) => 'C',
			chr(195) . chr(136) => 'E',
			chr(195) . chr(137) => 'E',
			chr(195) . chr(138) => 'E',
			chr(195) . chr(139) => 'E',
			chr(195) . chr(140) => 'I',
			chr(195) . chr(141) => 'I',
			chr(195) . chr(142) => 'I',
			chr(195) . chr(143) => 'I',
			chr(195) . chr(145) => 'N',
			chr(195) . chr(146) => 'O',
			chr(195) . chr(147) => 'O',
			chr(195) . chr(148) => 'O',
			chr(195) . chr(149) => 'O',
			chr(195) . chr(150) => 'O',
			chr(195) . chr(153) => 'U',
			chr(195) . chr(154) => 'U',
			chr(195) . chr(155) => 'U',
			chr(195) . chr(156) => 'U',
			chr(195) . chr(157) => 'Y',
			chr(195) . chr(159) => 's',
			chr(195) . chr(160) => 'a',
			chr(195) . chr(161) => 'a',
			chr(195) . chr(162) => 'a',
			chr(195) . chr(163) => 'a',
			chr(195) . chr(164) => 'a',
			chr(195) . chr(165) => 'a',
			chr(195) . chr(167) => 'c',
			chr(195) . chr(168) => 'e',
			chr(195) . chr(169) => 'e',
			chr(195) . chr(170) => 'e',
			chr(195) . chr(171) => 'e',
			chr(195) . chr(172) => 'i',
			chr(195) . chr(173) => 'i',
			chr(195) . chr(174) => 'i',
			chr(195) . chr(175) => 'i',
			chr(195) . chr(177) => 'n',
			chr(195) . chr(178) => 'o',
			chr(195) . chr(179) => 'o',
			chr(195) . chr(180) => 'o',
			chr(195) . chr(181) => 'o',
			chr(195) . chr(182) => 'o',
			chr(195) . chr(182) => 'o',
			chr(195) . chr(185) => 'u',
			chr(195) . chr(186) => 'u',
			chr(195) . chr(187) => 'u',
			chr(195) . chr(188) => 'u',
			chr(195) . chr(189) => 'y',
			chr(195) . chr(191) => 'y',
			// Decompositions for Latin Extended-A
			chr(196) . chr(128) => 'A',
			chr(196) . chr(129) => 'a',
			chr(196) . chr(130) => 'A',
			chr(196) . chr(131) => 'a',
			chr(196) . chr(132) => 'A',
			chr(196) . chr(133) => 'a',
			chr(196) . chr(134) => 'C',
			chr(196) . chr(135) => 'c',
			chr(196) . chr(136) => 'C',
			chr(196) . chr(137) => 'c',
			chr(196) . chr(138) => 'C',
			chr(196) . chr(139) => 'c',
			chr(196) . chr(140) => 'C',
			chr(196) . chr(141) => 'c',
			chr(196) . chr(142) => 'D',
			chr(196) . chr(143) => 'd',
			chr(196) . chr(144) => 'D',
			chr(196) . chr(145) => 'd',
			chr(196) . chr(146) => 'E',
			chr(196) . chr(147) => 'e',
			chr(196) . chr(148) => 'E',
			chr(196) . chr(149) => 'e',
			chr(196) . chr(150) => 'E',
			chr(196) . chr(151) => 'e',
			chr(196) . chr(152) => 'E',
			chr(196) . chr(153) => 'e',
			chr(196) . chr(154) => 'E',
			chr(196) . chr(155) => 'e',
			chr(196) . chr(156) => 'G',
			chr(196) . chr(157) => 'g',
			chr(196) . chr(158) => 'G',
			chr(196) . chr(159) => 'g',
			chr(196) . chr(160) => 'G',
			chr(196) . chr(161) => 'g',
			chr(196) . chr(162) => 'G',
			chr(196) . chr(163) => 'g',
			chr(196) . chr(164) => 'H',
			chr(196) . chr(165) => 'h',
			chr(196) . chr(166) => 'H',
			chr(196) . chr(167) => 'h',
			chr(196) . chr(168) => 'I',
			chr(196) . chr(169) => 'i',
			chr(196) . chr(170) => 'I',
			chr(196) . chr(171) => 'i',
			chr(196) . chr(172) => 'I',
			chr(196) . chr(173) => 'i',
			chr(196) . chr(174) => 'I',
			chr(196) . chr(175) => 'i',
			chr(196) . chr(176) => 'I',
			chr(196) . chr(177) => 'i',
			chr(196) . chr(178) => 'IJ',
			chr(196) . chr(179) => 'ij',
			chr(196) . chr(180) => 'J',
			chr(196) . chr(181) => 'j',
			chr(196) . chr(182) => 'K',
			chr(196) . chr(183) => 'k',
			chr(196) . chr(184) => 'k',
			chr(196) . chr(185) => 'L',
			chr(196) . chr(186) => 'l',
			chr(196) . chr(187) => 'L',
			chr(196) . chr(188) => 'l',
			chr(196) . chr(189) => 'L',
			chr(196) . chr(190) => 'l',
			chr(196) . chr(191) => 'L',
			chr(197) . chr(128) => 'l',
			chr(197) . chr(129) => 'L',
			chr(197) . chr(130) => 'l',
			chr(197) . chr(131) => 'N',
			chr(197) . chr(132) => 'n',
			chr(197) . chr(133) => 'N',
			chr(197) . chr(134) => 'n',
			chr(197) . chr(135) => 'N',
			chr(197) . chr(136) => 'n',
			chr(197) . chr(137) => 'N',
			chr(197) . chr(138) => 'n',
			chr(197) . chr(139) => 'N',
			chr(197) . chr(140) => 'O',
			chr(197) . chr(141) => 'o',
			chr(197) . chr(142) => 'O',
			chr(197) . chr(143) => 'o',
			chr(197) . chr(144) => 'O',
			chr(197) . chr(145) => 'o',
			chr(197) . chr(146) => 'OE',
			chr(197) . chr(147) => 'oe',
			chr(197) . chr(148) => 'R',
			chr(197) . chr(149) => 'r',
			chr(197) . chr(150) => 'R',
			chr(197) . chr(151) => 'r',
			chr(197) . chr(152) => 'R',
			chr(197) . chr(153) => 'r',
			chr(197) . chr(154) => 'S',
			chr(197) . chr(155) => 's',
			chr(197) . chr(156) => 'S',
			chr(197) . chr(157) => 's',
			chr(197) . chr(158) => 'S',
			chr(197) . chr(159) => 's',
			chr(197) . chr(160) => 'S',
			chr(197) . chr(161) => 's',
			chr(197) . chr(162) => 'T',
			chr(197) . chr(163) => 't',
			chr(197) . chr(164) => 'T',
			chr(197) . chr(165) => 't',
			chr(197) . chr(166) => 'T',
			chr(197) . chr(167) => 't',
			chr(197) . chr(168) => 'U',
			chr(197) . chr(169) => 'u',
			chr(197) . chr(170) => 'U',
			chr(197) . chr(171) => 'u',
			chr(197) . chr(172) => 'U',
			chr(197) . chr(173) => 'u',
			chr(197) . chr(174) => 'U',
			chr(197) . chr(175) => 'u',
			chr(197) . chr(176) => 'U',
			chr(197) . chr(177) => 'u',
			chr(197) . chr(178) => 'U',
			chr(197) . chr(179) => 'u',
			chr(197) . chr(180) => 'W',
			chr(197) . chr(181) => 'w',
			chr(197) . chr(182) => 'Y',
			chr(197) . chr(183) => 'y',
			chr(197) . chr(184) => 'Y',
			chr(197) . chr(185) => 'Z',
			chr(197) . chr(186) => 'z',
			chr(197) . chr(187) => 'Z',
			chr(197) . chr(188) => 'z',
			chr(197) . chr(189) => 'Z',
			chr(197) . chr(190) => 'z',
			chr(197) . chr(191) => 's'
		);

		$string = strtr($string, $chars);

		return $string;
	}

	/**
	 *
	 * The the text between the HTML comment
	 *
	 * @param string $text html
	 * @return	string result
	 */
	public static function html_comment($text)
	{
		return trim(str_replace(array('<!--', '-->'), '', $text ?? ''));
	}

	/**
	 *
	 * Protect email addresses from spam bots
	 *
	 * @param string $email email address
	 * @return	string javascript protected email
	 */
	public static function hide_email(string $email): string
	{
		$html = '<a href="mailto:' . $email . '">' . str_replace('@', __('[apenstaartje]'), str_replace('.', __('[punt]'), $email)) . '</a>';
		return self::protect_HTML($html);
	}

	/**
	 *
	 * Protect phone number from spam bots
	 *
	 * @param string $phone telephone number in international format
	 * @param string $phon2 telephone number in	national format
	 * @return	string javascript protected email
	 */
	public static function hide_phone(string $phone, string $phone2): string
	{
		$html = '<a href="tel:+' . $phone . '">' . $phone2 . '</a>';
		return self::protect_HTML($html);
	}


	/**
	 * Protect HTML from spam bots
	 *
	 * @param	string $html HTML
	 * @return	string javascript protected html
	 */
	public static function protect_HTML(string $html): string
	{
		$encHTML = base64_encode($html);

		$script = "document.write(atob('" . $encHTML . "'));";
		$script = "(function () { " . $script . " })();";
		$script = '<script type="text/javascript">' . $script . '</script><noscript>' . __('U moet javascript ingeschakeld hebben') . '</noscript>';

		return $script;
	}

	/**
	 *
	 * Removed the title from the body text
	 *
	 * @param string $title title without HTML
	 * @param string $text HTML body
	 * @return	string result
	 */
	public static function clean_body($title, $text)
	{

		$return = str_replace('<p></p>', '', str_replace('<h1>' . str_replace('&', '&amp;', $title) . '</h1>', '', str_replace('<h1>' . str_replace('&amp;', '&', $title) . '</h1>', '', str_replace('<h1>' . html_entity_decode($title) . '</h1>', '', str_replace('<h1>' . $title . '</h1>', '', $text)))));
		preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $return, $result);
		if (!empty($result)) :
			# Found a link.
			foreach ($result['href'] as $href) :
				if (str_starts_with($href, './')) :
					$return = str_replace($href, rtrim($href, '/') . (DEBUG ? '/index.html' : '/'), $return);
				endif;
			endforeach;
		endif;
		return $return;
	}

	/**
	 *
	 * Removed the title from the body text
	 *
	 * @param string $comment HTML comment
	 * @param string $text HTML body
	 * @return	string result
	 */
	public static function clean_description($title, $text)
	{
		return  str_replace('<p></p>', '', str_replace('<!--' . $title . '-->', '', str_replace('<!-- ' . $title . ' -->', '', $text)));
	}


	public static function clean_ArticleBody($text)
	{
		global $langs;

		foreach ($langs as $key => $lang) :
			$text = str_replace($lang->post_hreflang, '', $text);
			$text = str_replace($lang->page_hreflang, '', $text);
		endforeach;

		return trim($text);
	}





	/**
	 * Cleans the meta tag
	 *
	 * @param  mixed $text
	 * @return	string result
	 */
	public static function clean_meta($text)
	{
		return  trim(html_entity_decode(trim(str_replace('""', "'", strip_tags(str_replace(PHP_EOL, ' ', $text)))), ENT_QUOTES | ENT_SUBSTITUTE));
	}

	/**
	 *
	 * Create a correct formatted RSS valid text
	 *
	 * @param string $text HTML body
	 * @return	string result
	 */
	public static function clean_rss($text)
	{
		return $text;
		if (str_starts_with($text, '<p>')) :
			$text = substr($text, strlen('<p>'), strlen($text) - 1);
		endif;
		if (str_ends_with($text, '</p>')) :
			$text = substr($text, 0, strlen($text) - strlen('</p>'));
		endif;
		$text = preg_replace("/<p[^>]*?>/", "<br />", $text);
		$text = trim(strip_tags($text, '<img><br><blockquote><a>'));
		if (str_starts_with($text, "<br />")) :
			$text = substr($text, strlen("<br />"), strlen($text) - 1);
		endif;
		if (str_ends_with($text, "<br />")) :
			$text = substr($text, 0,  strlen($text) - strlen("<br />"));
		endif;
		$text = trim($text);
		if (str_ends_with($text, "<br />")) :
			$text = substr($text, 0,  strlen($text) - strlen("<br />"));
		endif;
		$text = htmlentities($text, ENT_NOQUOTES, 'UTF-8', false);
		$text = str_replace(array('&lt;', '&gt;'), array('<', '>'), $text);
		$text = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $text);
		$text = str_replace(' & ', ' &amp; ', $text);
		//$text = str_replace("'","&#039;", $text);
		$text = str_replace(array('&amp;rdquo;', '&amp;ldquo;'), array('&rdquo;', '&ldquo;'), $text);
		$text = str_replace('href="./', 'href="' . __('domain') . '/', $text);
		$text = str_replace('src="./', 'src="' . __('domain') . '/', $text);

		return str_replace('&', '&amp;', html_entity_decode(trim($text)));
	}

	/**
	 * Remove all Image attributes
	 * @param string $html
	 * @return	string
	 */
	public static function stripImageAttributes($html)
	{
		// init document
		$doc = new \DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML('<!doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>' . $html . '</body></html>');
		libxml_clear_errors();
		// init xpath
		$xpath = new \DOMXPath($doc);

		// process images
		$body = $xpath->query('/html/body')->item(0);

		foreach ($xpath->query('//img', $body) as $image) {
			$toRemove = null;

			foreach ($image->attributes as $attr) {
				if ('src' !== $attr->name && 'alt' !== $attr->name) {
					$toRemove[] = $attr;
				}
			}

			if ($toRemove) {
				foreach ($toRemove as $attr) {
					if (gettype($image) == 'object' && $image !== null) :
						$image->removeAttribute($attr->name);
					endif;
				}
			}
		}

		// convert the document back to a HTML string
		$html = '';
		foreach ($body->childNodes as $node) {
			$html .= $doc->saveHTML($node);
		}

		return $html;
	}

	public static function clean_buffer($buffer)
	{
		preg_match_all("/\[[^\]]*\]/", $buffer, $matches);
		foreach ($matches[0] as $match) :
			$replace = '';
			/* Replace date with age */
			if (str_starts_with($match, '[how-old-am-i bday="')) :
				$date = rtrim(str_replace('[how-old-am-i bday="', '', $match), '"]');
				$tz  = new \DateTimeZone('Europe/Amsterdam');
				$replace = \DateTime::createFromFormat('Y-m-d', $date, $tz);
				if ($replace !== false) :
					$replace = $replace->diff(new \DateTime('now', $tz))->y;
				endif;
			elseif (str_starts_with($match, "[how-old-am-i bday='")) :
				$date = rtrim(str_replace("[how-old-am-i bday='", '', $match), "']");
				$tz  = new \DateTimeZone('Europe/Amsterdam');
				$replace = \DateTime::createFromFormat('Y-m-d', $date, $tz);
				if ($replace !== false) :
					$replace = $replace->diff(new \DateTime('now', $tz))->y;
				endif;
			else :
				//varDump($match);
				continue;
			endif;
			$buffer = str_replace($match, $replace, $buffer);
		endforeach;
		$buffer = str_replace(__('domain') . __('domain'), __('domain'), $buffer);
		return $buffer;
	}

	public static function clean_buffer_amp($buffer)
	{
		preg_match_all("/\[[^\]]*\]/", $buffer, $matches);
		foreach ($matches[0] as $match) :
			$replace = '';
			/* Replace date with age */
			if (str_starts_with($match, '[how-old-am-i bday="')) :
				$date = rtrim(str_replace('[how-old-am-i bday="', '', $match), '"]');
				$tz  = new \DateTimeZone('Europe/Amsterdam');
				$replace = \DateTime::createFromFormat('Y-m-d', $date, $tz);
				if ($replace !== false) :
					$replace = $replace->diff(new \DateTime('now', $tz))->y;
				endif;
			elseif (str_starts_with($match, "[how-old-am-i bday='")) :
				$date = rtrim(str_replace("[how-old-am-i bday='", '', $match), "']");
				$tz  = new \DateTimeZone('Europe/Amsterdam');
				$replace = \DateTime::createFromFormat('Y-m-d', $date, $tz);
				if ($replace !== false) :
					$replace = $replace->diff(new \DateTime('now', $tz))->y;
				endif;
			else :
				continue;
			//varDump($match);
			endif;
			$buffer = str_replace($match, $replace, $buffer);
		endforeach;
		$findAndReplace = array(
			__('domain') . __('domain') => __('domain'),
			"<ul><li></li></ul>" => ""
		);
		foreach ($findAndReplace as $search => $replace) :
			$buffer = str_replace($search, $replace, $buffer);
		endforeach;

		return $buffer;
	}

	/**
	 * Get a snippet from a string
	 *
	 * @param  string|null $str Input text
	 * @param  int $wordCount Number of words to return. Default 45
	 * @return string Snippet
	 */
	public static function get_snippet(string|null $str, int $wordCount = 45): string
	{
		if($str === null) return '';
		$str = strip_tags($str ?? '');

		$rtn = implode(
			'',
			array_slice(
				preg_split(
					'/([\s,\.;\?\!]+)/',
					$str,
					$wordCount * 2 + 1,
					PREG_SPLIT_DELIM_CAPTURE
				),
				0,
				$wordCount * 2 - 1
			)
		);
		if (strlen($rtn) < strlen($str)) :
			$rtn .= '&nbsp;&lsqb;&hellip;&rsqb;';
		endif;
		return $rtn;
	}

	/**
	 * Gets the correct Price format.
	 * @see https://schema.org/price
	 * @param  float|double
	 * @return	string Price
	 */
	public static function Price($money)
	{
		return str_replace(',', '.', sprintf("%.2f", $money));
	}


	public static function srcset(string|array $images)
	{
		global $dist_path;
		$image_width = 0;
		$rtn = array();
		if (!is_array($images)) :
			$srcset_image = $images;
			$images = array();
			$extension = ((object)pathinfo($srcset_image, PATHINFO_EXTENSION))->scalar;
			if (defined('srcset')) :
				foreach (srcset as $scale) :
					$images[] = str_replace('.' . $extension, '@' . $scale . 'x.' . $extension, $srcset_image);
				endforeach;
			endif;
		endif;

		$images = array_map('trim', $images);
		foreach ($images as $image) :
			if (file_exists($image) || realpath($dist_path . '/' . $image) !== false) :
				$image_size = getimagesize($dist_path . '/' . $image);
				if($image_size !== false) :
					$image_width = $image_size[0];
				endif;
				$rtn[] = ltrim($image, '/') . '?v=' . Cryptography::get_hash($dist_path . '/' . $image) . ' ' . $image_width . 'w';
			endif;
		endforeach;
		return implode(', ', $rtn);
	}

	public static function sizes(string|array $images, float $basepx)
	{
		global $dist_path;
		$rtn = array();
		if (!is_array($images)) :
			$srcset_image = $images;
			$images = array();
			$extension = ((object)pathinfo($srcset_image, PATHINFO_EXTENSION))->scalar;
			if (defined('srcset')) :
				foreach (srcset as $scale) :
					$images[] = str_replace('.' . $extension, '@' . $scale . 'x.' . $extension, $srcset_image);
				endforeach;
			endif;
		endif;
		$images = array_map('trim', $images);
		$baseimg = null;
		foreach ($images as $image) :
			if (file_exists($image)) :
				$filename = pathinfo($image)['filename'];
				if (str_contains($filename, '@1x') || !str_contains($filename, '@')) :
					$baseimg = str_replace('@1x', '', $filename);
				endif;
			elseif (realpath($dist_path . '/' . $image) !== false) :
				$filename = pathinfo($dist_path . '/' . $image)['filename'];
				if (str_contains($filename, '@1x') || !str_contains($filename, '@')) :
					$baseimg = str_replace('@1x', '', $filename);
				endif;
			endif;
		endforeach;
		$scale = 1.0;
		$imageList = array();
		foreach ($images as $image) :
			if (file_exists($image) && $baseimg !== null) :
				$filename = pathinfo($image)['filename'];
				$scale = ltrim(rtrim(str_replace($baseimg, '', $filename), 'xX'), '@');
				if (empty($scale)) :
					$scale = '1.0';
				endif;
				$scale = (float)$scale;

				$imageList[$image] = $scale;
			endif;
		endforeach;

		//foreach($imageList as $image => $scale) :
		//	if($scale < 1) :
		//		$imgpx = getimagesize($dist_path . '/' . $image)[0];
		//		$rtn[] = '(min-width: ' . intval(($basepx / $scale)) . 'px) ' . $imgpx .'px';
		//	endif;
		//endforeach;
		$rtn[] = '100vw';
		return implode(', ', $rtn);
	}


	private static function srcset2($image)
	{
		$image = strtok($image, '?');
		$items = array();
		$extension = ((object)pathinfo($image))->extension;
		if (defined('srcset')) :
			foreach (srcset as $src) :
				$items[] = (!DEBUG ? __('domain') : '.') . ltrim(str_replace('.' . $extension, '-' . $src . '.' . $extension, str_replace(' ', '%20', $image)), '.')  . '?v=' . Cryptography::getFileVersion(str_replace('.' . $extension, '-' . $src . '.' . $extension, $image)) . ' ' . $src . 'w';
			endforeach;
		endif;
		return implode(', ', $items);
	}

	public static function latlon($value)
	{
		return str_replace(',', '.', (string)$value);
	}

	public static function SentenceEnd($value)
	{
		if (empty($value)) :
			return '';
		elseif (str_ends_with($value, '.')) :
			return $value;
		elseif (str_ends_with($value, '!')) :
			return $value;
		elseif (str_ends_with($value, '?')) :
			return $value;
		else :
			return $value . '.';
		endif;
	}

	/**
	 * @param int month
	 * @return	string december
	 */
	public static function Month($month)
	{
		$months = array("januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december");
		return $months[((int)$month) - 1];
	}


	// Automatically parse youtube video/playlist links and generate the respective embed code
	public static function AutoParseYoutubeLink($url): string|null
	{
		$data = null;
		if ((str_contains($url, 'youtube') || str_contains($url, 'youtu.be'))) :
			// Check if youtube link is a playlist
			if (strpos($url, 'list=') !== false) :
				// Generate the embed code
				$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube-nocookie.com/embed/videoseries?list=$1', $url);

				return $data;
			endif;

			// Check if youtube link is not a playlist but a video [with time identifier]
			if (strpos($url, 'list=') === false && strpos($url, 't=') !== false) :
				$time_in_secs = null;

				// Get the time in seconds from the time function
				$time_in_secs = self::ConvertTimeToSeconds($url);

				// Generate the embed code
				$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube-nocookie.com/embed/$1?start=' . $time_in_secs, $url);

				return $data;
			endif;

			// If the above conditions were false then the youtube link is probably just a plain video link. So generate the embed code already.
			$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube-nocookie.com/embed/$1', $url);
		endif;
		return $data;
	}

	// Check for time identifier in the youtube video link and conver it into seconds
	private static function ConvertTimeToSeconds($data)
	{
		$time = null;
		$hours = null;
		$minutes = null;
		$seconds = null;
		$pattern_time_split = "([0-9]{1-2}+[^hms])";

		// Regex to check for youtube video link with time identifier
		$youtube_time = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*(t=((\d+h)?(\d+m)?(\d+s)?))~i';

		// Check for time identifier in the youtube video link, extract it and convert it to seconds
		if (preg_match($youtube_time, $data, $matches)) {
			// Check for hours
			if (isset($matches[4])) {
				$hours = $matches[4];
				$hours = preg_split($pattern_time_split, $hours);
				$hours = substr($hours[0], 0, -1);
			}
			// Check for minutes
			if (isset($matches[5])) {
				$minutes = $matches[5];
				$minutes = preg_split($pattern_time_split, $minutes);
				$minutes = substr($minutes[0], 0, -1);
			}
			// Check for seconds
			if (isset($matches[6])) {
				$seconds = $matches[6];
				$seconds = preg_split($pattern_time_split, $seconds);
				$seconds = substr($seconds[0], 0, -1);
			}
			// Convert time to seconds
			$time = (($hours * 3600) + ($minutes * 60) + $seconds);
		}

		return $time;
	}


	public static function YouTube_ID($url): string|null
	{
		$youtube_id = null;
		// Here is a sample of the URLs this regex matches: (there can be more content after the given URL that will be ignored)

		// http://youtu.be/dQw4w9WgXcQ
		// http://www.youtube.com/embed/dQw4w9WgXcQ
		// http://www.youtube.com/watch?v=dQw4w9WgXcQ
		// http://www.youtube.com/?v=dQw4w9WgXcQ
		// http://www.youtube.com/v/dQw4w9WgXcQ
		// http://www.youtube.com/e/dQw4w9WgXcQ
		// http://www.youtube.com/user/username#p/u/11/dQw4w9WgXcQ
		// http://www.youtube.com/sandalsResorts#p/c/54B8C800269D7C1B/0/dQw4w9WgXcQ
		// http://www.youtube.com/watch?feature=player_embedded&v=dQw4w9WgXcQ
		// http://www.youtube.com/?feature=player_embedded&v=dQw4w9WgXcQ

		// It also works on the youtube-nocookie.com URL with the same above options.
		// It will also pull the ID from the URL in an embed code (both iframe and object tags)
		if (!empty($url)) :
			preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
			if (isset($match) && is_array($match) && array_key_exists(1, $match)) :
				$youtube_id = $match[1];
			endif;
		endif;
		return $youtube_id;
	}
}
