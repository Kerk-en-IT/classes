<?php
if (!defined('EOL_SPLIT')) :
	define('EOL_SPLIT', '- ');
endif;
if (!defined('EOL_SPLIT_SEARCH')) :
	define('EOL_SPLIT_SEARCH', ' - ');
endif;
if (!defined('EOL_SPLIT_REPLACE')) :
	define('EOL_SPLIT_REPLACE', '$#');
endif;

/**
 * Format various of objects into the correct layout
 *
 * @author     Marco van 't Klooster, Kerk en IT <info@kerkenit.nl>
 */

class Format {

	/**
	 * Menu
	 * @deprecated in 1.3.107
	 *
	 * @param  string $url
	 * @param  string $name
	 * @return string
	 */
	public static function Menu($url, $name) {
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
	 * @param  string $firstname
	 * @param  string $infix
	 * @param  string $lastname
	 * @return string
	 */
	public static function Name($firstname, $infix, $lastname) {
		return trim($firstname . ' ' . trim($infix . ' ' . $lastname));
	}

	/**
	 * Gets the full name of a user including the name of the husband
	 *
	 * @param  string $firstname
	 * @param  string $infix
	 * @param  string $lastname
	 * @param  string $lastname_partner
	 * @param  int $gender
	 * @param  int $gender_partner
	 * @param  int|null $maritalstatus			Marital status person (Nullable)
	 * @return string
	 */
	public static function NameMarried(string $firstname = null, string $infix = null, string $lastname = null, string $lastname_partner = null, int $gender = null, int $gender_partner =null, int $maritalstatus = null) {
		return self::Name(($firstname ?? ''), ($infix ?? ''), ($gender_partner == 1 && $gender == 2 ? ($lastname_partner ?? '') . '-' . ($lastname ?? '') : ($lastname ?? '')));
	}

	/**
	 * Gets the full name of a user including the name of the husband
	 *
	 * @param  string $firstname				Firstname of person
	 * @param  string $infix					Infix of person
	 * @param  string $lastname					Last name of person
	 * @param  string $lastname_partner			Lastname of partner
	 * @param  int $gender						Gender of person
	 * @param  int $gender_partner				Last name of person
	 * @param  int|null $maritalstatus			Marital status person (Nullable)
	 * @param  int|null $age					Age of person (Nullable)
	 * @param  string|DateTme|null $dateofbirth	Birthday. This can be empty (When `null` the age is used.)
	 * @param  string|DateTme|null $dateofdeath	Date of death. Can be the date of death of the current date. Leave empty to use current date.
	 * @return string
	 */
	public static function NameMarriedAge(string $firstname = null, string $infix = null, string $lastname = null, string $lastname_partner = null, int $gender = null, int $gender_partner = null, int $maritalstatus = null, int $age = null, \DateTime|string $dateofbirth =null, \DateTime|string $dateofdeath = null)
	{

		$name_text =  self::NameMarried(firstname: $firstname, infix: $infix, lastname: $lastname, lastname_partner: $lastname_partner, gender: $gender, gender_partner: $gender_partner, maritalstatus: $maritalstatus);
		$age_text = '';
		if(($age === null || $age == 0) && $dateofbirth !== null) :
			$dateofbirth = self::GetDate($dateofbirth);
			$dateofdeath = self::GetDate($dateofdeath);
			$age = $dateofbirth->diff($dateofdeath)->y;
		endif;
		if(is_numeric($age) && $age > 0) :
			$age_text = sprintf('(%d jaar)', $age);
		endif;
		return trim($name_text . ' ' . $age_text);
	}

	/**
	 * Gets the full name of a user including the name of the husband.
	 * Almost equal to ```self::NameMarriedAge```. But this has a prefix for the gender.
	 *
	 * @param  string $firstname				Firstname of person
	 * @param  string $infix					Infix of person
	 * @param  string $lastname					Last name of person
	 * @param  string $lastname_partner			Lastname of partner
	 * @param  int $gender						Gender of person
	 * @param  int $gender_partner				Last name of person
	 * @param  int|null $maritalstatus			Marital status person (Nullable)
	 * @param  int|null $age					Age of person (Nullable)
	 * @param  string|DateTme|null $dateofbirth	Birthdate. This can be empty (When `null` the age is used.)
	 * @param  string|DateTme|null $dateofdeath	Date of death. Can be the date of death of the current date. Leave empty to use current date.
	 * @return string
	 */
	public static function NameMarriedAgeGender(string $firstname = null, string $infix = null, string $lastname = null, string $lastname_partner = null, int $gender = null, int $gender_partner = null, int $maritalstatus = null, int $age = null, \DateTime|string $dateofbirth = null, \DateTime|string $dateofdeath = null)
	{

		$name_text =  self::NameMarriedAge(firstname: $firstname, infix: $infix, lastname: $lastname, lastname_partner: $lastname_partner, gender: $gender, gender_partner: $gender_partner, maritalstatus: $maritalstatus, age: $age,dateofbirth: $dateofbirth,dateofdeath: $dateofdeath);
		if (($age === null || $age == 0) && $dateofbirth !== null) :
			$dateofbirth = self::GetDate($dateofbirth);
			$dateofdeath = self::GetDate($dateofdeath);
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
	 * @return string
	 */
	public static function NameAbbreviation($firstname, $infix, $lastname) {
		$rtn = '';
		$full_name = trim(str_replace("'", ' ', str_replace('-', ' ', $firstname . ' ' . trim($infix . ' ' . $lastname))));
		if(!empty($full_name)) :
			foreach(explode(' ', $full_name) as $name) :
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
	 * @return string
	 */
	public static function LastName($infix, $lastname) {
		return rtrim($lastname . ', ' . $infix, ', ');
	}

	/**
	 * Get's the full surname
	 *
	 * @param  mixed $infix
	 * @param  mixed $lastname
	 * @return string
	 */
	public static function InfixLastName($infix, $lastname) {
		return ltrim($infix . ' ' .$lastname, ' ');
	}

	/**
	 * Gets the address from s street and house number
	 *
	 * @param  string $street
	 * @param  string $number
	 * @param  string $suffix Can be empty
	 * @return string
	 */
	public static function Address($street, $number, $suffix = '') {
		return trim($street . ' ' . $number. $suffix);
	}

	/**
	 * Get's the phone number. Use full for ```tel:``` hyperlinks
	 *
	 * @param  string $phone
	 * @return string
	 */
	public static function PhoneURL($phone) {
		$str = str_replace(array('(', ')', ' ', '-', '+'), array('','','','', '00'), $phone);
		if(strlen($str) == 9 && !str_starts_with($str, '0')) :
			$str = '0' . $str;
		endif;
		return $str;
	}

	/**
	 * Format number to currency
	 *
	 * @param  mixed $number
	 * @return string 9,99
	 */
	public static function Currency($number) {
		$money = number_format($number, 2, ',', '.');
		return $money;
	}


	/**
	 * Format Money to Euro
	 *
	 * @param  mixed $number
	 * @return string € 9,99
	 */
	public static function Money($number) {
		return "&euro; " . Format::Currency($number);
	}


	/**
	 * Get Date from various types
	 *
	 * @param  int|string|DateTime $datetime
	 * @return DateTime DateTime object
	 */
	public static function GetDate($datetime)
	{
		if(is_numeric($datetime)) :
			$date = new DateTime();
			$date->setTimestamp($datetime);
			return $date;
		elseif(!is_object($datetime)) :
			if(is_string($datetime)) :
				if(strlen($datetime) == 10) :
					$items = explode('-', $datetime);
					if(strlen($items[0]) == 4) :
						$datetime = $items[0] . '-' . $items[1] . '-' . $items[2] . ' 00:00:00';
					else :
						$datetime = $items[2] . '-' . $items[1] . '-' . $items[0] . ' 00:00:00';
					endif;
				endif;
			endif;
			try{
				$datetime = new DateTime($datetime ?? 'now');
			} catch (Exception $e) {
				try {
					if (is_string($datetime)) :
						$items = explode('-', $datetime);
						if (strlen($items[0]) == 4) :
							$datetime = $items[0] . '-' . $items[1] . '-' . $items[2] . ' 00:00:00';
						else :
							$datetime = $items[2] . '-' . $items[1] . '-' . $items[0] . ' 00:00:00';
						endif;
					endif;
					$datetime = new DateTime($datetime ?? 'now');
				} catch (Exception $e) {
					$datetime = new DateTime();
				}
			}

		endif;
		$datetime->setTimezone(new DateTimeZone(date_default_timezone_get()));
		return $datetime;
	}

	/**
	 * Get's a list with the full names of all months
	 *
	 * @return array
	 */
	private static function months_full()
	{
		return array("januari","februari","maart","april","mei","juni","juli", "augustus","september","oktober","november", "december");
	}

	/**
	 * Get's a list with the short names of all months
	 *
	 * @return array
	 */
	private static function months_short()
	{
		return array("jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec");
	}

	/**
	 * Get's a list with the full names of the days of the week
	 *
	 * @return array
	 */
	private static function days_full()
	{
		return array("zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag");
	}

	/**
	 * Get's a list with the short names of the days of the week
	 *
	 * @return array
	 */
	private static function days_short()
	{
		return array("zo", "ma", "di", "wo", "do", "vr", "za");
	}

	/**
	 * Get name of the part of the day
	 *
	 * @param  int $hour
	 * @return string morgen
	 */
	public static function days_part($hour)
	{
		if($hour < 6) :
			return "nacht";
		elseif ($hour >= 6 && $hour < 12) :
			return "morgen";
		elseif ($hour >= 12 && $hour < 18) :
			return "middag";
		else :
			return "avond";
		endif;
	}

	/**
	 * Get name of the part of the day in another form
	 *
	 * @param  int $hour
	 * @return string ochtend
	 */
	public static function days_part2($hour)
	{
		if ($hour < 6) :
			return "ochtend";
		elseif ($hour >= 6 && $hour < 12) :
			return "morgen";
		elseif ($hour >= 12 && $hour < 18) :
			return "middag";
		else :
			return "avond";
		endif;
	}

	/**
	 * Get's a list of timing periods
	 *
	 * @param  bool $plural
	 * @return array
	 */
	private static function period($plural = false)
	{
		if(!$plural) {
			return array("seconde", "minuut", "uur", "dag", "week", "maand", "jaar", "decennium");
		} else {
			return array("seconden", "minuten", "uren", "dagen", "weken", "maanden", "jaren", "decennia");
		}
	}

	/**
	 * Get the text at
	 *
	 * @return string
	 */
	private static function at()
	{
		return 'om';
	}

	/**
	 * maandag 20 december 2010
	 *
	 * @param object datetime
	 * @return string maandag 20 december 2010
	 */
	public static function LongDate($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')].' '.$datetime->format('j').' '.self::months_full()[$datetime->format('m') - 1].' ' .$datetime->format('Y');
	}

	/**
	 * maandag 20 december 2010
	 *
	 * @param object datetime
	 * @return string maandag 20 dec. 2010
	 */
	public static function ShortMonthDate($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_short()[$datetime->format('m') - 1].'.' . ' ' . $datetime->format('Y');
	}

	/**
	 * december 2010
	 *
	 * @param object datetime
	 * @return string december 2010
	 */
	public static function MonthAndYear($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y');
	}

	/**
	 * maandag 20 december
	 *
	 * @param object datetime
	 * @return string maandag 20 december
	 */
	public static function DayDateMonth($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')].' '.$datetime->format('j').' '. self::months_full()[$datetime->format('m') - 1];
	}

	/**
	 * maandag 20 december
	 *
	 * @param object datetime
	 * @param string period
	 * @return string maandag 20 december
	 */
	public static function DayDateMonthPeriod($datetime, $period = 'morgen')
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . $period . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1];
	}

	/**
	 * maandag
	 *
	 * @param object datetime
	 * @return string maandag
	 */
	public static function DayOfWeekName($value)
	{
		if (!is_numeric($value) || $value > 10) :
			$datetime = self::GetDate($value);
			$value = (int)$datetime->format('w');
		else :
			$value = ((int)$value)-1;
		endif;

		return self::days_full()[$value];
	}

	/**
	 * maandag 20 december 2010
	 *
	 * @param object datetime
	 * @return string maandag 20 december 2010
	 */
	public static function FullDate($datetime)
	{
		return self::LongDate($datetime);
	}

	/**
	 * 20 december 2010
	 *
	 * @param object datetime
	 * @return string 20 december 2010
	 */
	public static function DutchDate($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y');
	}

	/**
	 * maandag 20 december 2010 9:42
	 *
	 * @param object datetime
	 * @return string maandag 20 december 2010 9:42
	 */
	public static function FullDateTime($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')].' '.$datetime->format('j').' '. self::months_full()[$datetime->format('m') - 1].' ' .$datetime->format('Y G:i');
	}

	/**
	 * ma 20 dec 2010 9:42
	 *
	 * @param object datetime
	 * @return string ma 20 dec 2010 9:42
	 */
	public static function ShortDateAndTime($datetime)
	{
		$datetime = self::GetDate($datetime);
		return str_replace(' 0:00', '', self::days_short()[$datetime->format('w')].' '.$datetime->format('d').' '. self::months_short()[$datetime->format('m') - 1].' ' .$datetime->format('Y G:i'));
	}

	/**
	 * 20 dec
	 *
	 * @param object datetime
	 * @return string 20 dec
	 */
	public static function ShortDateAndMonth($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('d').' '. self::months_short()[$datetime->format('m') - 1];
	}

	/**
	 * 20 december
	 *
	 * @param object datetime
	 * @return string 20 december
	 */
	public static function ShortDateAndFullMonth($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('d') . ' ' . self::months_full()[$datetime->format('m') - 1];
	}

	/**
	 * 9:42
	 *
	 * @param object datetime
	 * @return string 9:42
	 */
	public static function ShortTime($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('G:i');
	}

	/**
	 * 9.42
	 *
	 * @param object datetime
	 * @return string 9.42
	 */
	public static function DutchShortTime($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('G.i');
	}

	/**
	 * 09:42
	 *
	 * @param object datetime
	 * @return string 09:42
	 */
	public static function Time($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('H:i');
	}

	/**
	 * ma 20 december 2010
	 *
	 * @param object datetime
	 * @return string ma 20 december 2010
	 */
	public static function ShortDate($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_short()[$datetime->format('w')].' '.$datetime->format('j').' '. self::months_full()[$datetime->format('m') - 1].' ' .$datetime->format('Y');
	}

	/**
	 * maandag 20 december 2010 om 9:42
	 *
	 * @param object datetime
	 * @return string maandag 20 december 2010 om 9:42
	 */
	public static function FullDateAt($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')].' '.$datetime->format('j').' '. self::months_full()[$datetime->format('m') - 1].' ' .$datetime->format('Y').' ' . self::at() . ' ' .$datetime->format('G:i');
	}

	/**
	 * maandag 20 december 2010 om 9:42
	 *
	 * @param object datetime
	 * @return string maandag 20 december 2010 om 9:42
	 */
	public static function FullDateAtNullTime($datetime)
	{
		return str_replace(' om 0:00', '', self::FullDateAt($datetime));
	}

	/**
	 * maandag 20 december om 9:42
	 *
	 * @param object datetime
	 * @return string maandag 20 december om 9:42
	 */
	public static function FullDateWithoutYearAt($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . self::at() . ' ' . $datetime->format('G:i');
	}

	/**
	 * ma 20-12 9:42
	 *
	 * @param object datetime
	 * @return string ma 20-12 9:42
	 */
	public static function ShortDateTimeMonth($datetime)
	{
		$datetime = self::GetDate($datetime);

		return self::days_short()[$datetime->format('w')].' ' . $datetime->format('j') . ' '. self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('G:i');
	}

	/**
	 * ma 20-12-2015 9:42
	 *
	 * @param object datetime
	 * @return string ma 20-12-2015 9:42
	 */
	public static function ShortDateTime($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_short()[$datetime->format('w')].' '.$datetime->format('d-m-Y G:i');
	}

	/**
	 * ma 20-12-2015 09:42
	 *
	 * @param object datetime
	 * @return string ma 20-12-2015 09:42
	 */
	public static function ShortDateTimeHour($datetime)
	{
		$datetime = self::GetDate($datetime);
		return self::days_short()[$datetime->format('w')].' '.$datetime->format('d-m-Y H:i');
	}

	public static function idiv($a, $b)
	{
		return floor($a / $b);
	}

	/**
	 * Calculates the Easter date for a given year
	 *
	 * @param  int $y
	 * @return DateTime
	 */
	public static function easter_date($y)
	{
		$firstdig1 = array(21, 24, 25, 27, 28, 29, 30, 31, 32, 34, 35, 38);
		$firstdig2 = array(33, 36, 37, 39, 40);

		$firstdig = self::idiv($y, 100);
		$remain19 = $y % 19;

		$temp = self::idiv($firstdig - 15, 2) + 202 - 11 * $remain19;

		if (in_array($firstdig, $firstdig1)) {
			$temp = $temp - 1;
		}
		if (in_array($firstdig, $firstdig2)) {
			$temp = $temp - 2;
		}

		$temp = $temp % 30;

		$ta = $temp + 21;
		if ($temp == 29) {
			$ta = $ta - 1;
		}
		if ($temp == 28 and $remain19 > 10) {
			$ta = $ta - 1;
		}

		$tb = ($ta - 19) % 7;

		$tc = (40 - $firstdig) % 4;
		if ($tc == 3) {
			$tc = $tc + 1;
		}
		if ($tc > 1) {
			$tc = $tc + 1;
		}

		$temp = $y % 100;
		$td = ($temp + self::idiv($temp, 4)) % 7;

		$te = ((20 - $tb - $tc - $td) % 7) + 1;
		$d = $ta + $te;

		if ($d > 31) {
			$d = $d - 31;
			$m = 4;
		} else {
			$m = 3;
		}
		return new DateTime("$y-$m-$d", new DateTimeZone('Europe/Amsterdam'));
	}

	public static function FeastDate_YN($date)
	{
		$datetime = self::GetDate($date);
		if ($datetime->format('N') >= 6) :
			return true;
		endif;
		$feasts = array('08-12', '25-12', '01-01', '06-01', '19-03', '25-03', '24-06', '29-06', '15-08', '01-11', '07-11');
		if (in_array($datetime->format('d-m'), $feasts)) :
			return true;
		endif;

		$easter = self::easter_date($datetime->format('Y'));
		$specialDates = array($easter->add(new DateInterval('P39D'))->format('Y-m-d'), $easter->add(new DateInterval('P68D'))->format('Y-m-d'));
		if (in_array($datetime->format('Y-m-d'), $specialDates)) :
			return true;
		endif;

		return false;
	}

	/**
	 * IsoDate
	 *
	 * @param  mixed $datetime
	 * @return string 20161220T094200
	 */
	public static function IsoDate($datetime)
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('Ymd') . 'T' . $datetime->format('His');
	}

	/**
	 * ISO8601 int ATOM format
	 *
	 * @param  mixed $datetime
	 * @return string 2016-12-20T09:42:00+02:00
	 */
	public static function ISO8601($datetime = 'now')
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format(DateTime::ATOM);
	}

	/**
	 * Gets the time from now in
	 *
	 * @param  string $date
	 * @return string
	 */
	public static function Ago($date) {
	    if (empty($date)) :
	        return "Geen datum";
	    endif;
	    $period = self::period(false);
	    $periods = self::period(true);
	    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
	    $now = time();
	    $unix_date = strtotime($date);
	// check validity of date
	    if (empty($unix_date)) :
	        return "Verkeerde datum";
	    endif;
	// is it future date or past date
	    if ($now > $unix_date) :
	        $difference = $now - $unix_date;
	        $tense = "geleden";
	    else :
	        $difference = $unix_date - $now;
	        $tense = "vanaf nu";
	    endif;
	    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
	        $difference /= $lengths[$j];
	    }
	    $difference = round($difference);
	    if ($difference != 1) :
	        $period[$j] = $periods[$j];
	    endif;
	    return "$difference $period[$j] {$tense}";
	}
	/**
	 * Gets the date for a datetime picker
	 *
	 * @param  mixed $datetime
	 * @return string 20-12-2016 09:42:00
	 */
	public static function DateTimePicker($datetime)
	{
		return self::GetDate($datetime)->format('d-m-Y H:i:s');
	}

	/**
	 * Gets the date for a date picker
	 *
	 * @param  mixed $datetime
	 * @param  bool $nullable Default false
	 * @return string 2016-12-20
	 */
	public static function DatePicker($datetime, $nullable = false)
	{
		$format = 'Y-m-d';
		$detect = new Mobile_Detect;
		$user_agent = $detect->getUserAgent();
		if($detect->is('WebKit') || $detect->isiOS()) :
			$format = 'Y-m-d';
		endif;
		if(empty($datetime) && $nullable) :
			return '';
		else :
			return (new DateTime($datetime))->format($format);
		endif;
	}

	/**
	 * Gets the date for a time picker
	 *
	 * @param  mixed $datetime
	 * @param  bool $nullable Default false
	 * @return string 09:42
	 */
	public static function TimePicker($datetime, $nullable = false)
	{
		$format = 'H:i';
		/*
		$detect = new Mobile_Detect;
		$user_agent = $detect->getUserAgent();
		if($detect->isMobile()) :
			$format = 'H:i';
		elseif (stripos( $user_agent, 'Chrome') !== false) :
		    $format = 'h:i';
		elseif (stripos( $user_agent, 'Safari') !== false) :
		  	$format = 'H:i';
		endif;
*/
		if (empty($datetime) && $nullable) :
			return '';
		else :
			return (new DateTime($datetime))->format($format);
		endif;
	}

	/**
	 * Gets the day of the week
	 * ISO 8601 numeric representation of the day of the week
	 * 1 (for Monday) through 7 (for Sunday)
	 *
	 * @param  mixed $datetime
	 * @return int
	 */
	public static function DayOfWeek($datetime = 'now')
	{
		$datetime = self::GetDate($datetime);
		return (int)$datetime->format('N');
	}

	/**
	 * Add days to a datetime object
	 *
	 * @param  mixed $datetime
	 * @param  int $days
	 * @param  string $format Output format.
	 * @return string
	 */
	public static function DateTimeAdd($datetime, $days, $format = 'd-m-Y H:i:s')
	{
		$invert = 0;
		$datetime = self::GetDate($datetime);
		if(is_numeric($days)) :
			if($days < 0) :
				$invert = 1;
			endif;
			$days = 'P' . abs($days) . 'D';
		endif;
		$interval = new DateInterval($days);
		$interval->invert = $invert;
		$datetime->add($interval);
		return $datetime->format($format);
	}

	/**
	 * Get a logical structure of a date.
	 *
	 * @param  mixed $datetime
	 * @param  string $type
	 * @return object $logic
	 */
	public static function GetDateTimeLogic($datetime, $type)
	{
		$datetime = self::GetDate($datetime);
		$logic = array();
		$logic['type'] = $type;
		if($type == 'week') :
			$logic['day'] = $datetime->format('l');
			$logic['dayname'] = self::DayOfWeekName($datetime);
		elseif ($type == 'month') :
			$logic['month'] = $datetime->format('F');
		endif;
		$logic['interval'] = ((int)$datetime->format('j'));
		if($logic['interval'] > 0 && $logic['interval'] <= 7) :
			$logic['interval'] = 'First';
			$logic['repeat'] = 'Eerste';
		elseif ($logic['interval'] >= (((int)$datetime->format('t')) - 7) && $logic['interval'] <= (int)$datetime->format('t')) :
			$logic['interval'] = 'Last';
			$logic['repeat'] = 'Laatste';
		elseif ($logic['interval'] > 7 && $logic['interval'] <= 14) :
			$logic['interval'] = 'Second';
			$logic['repeat'] = 'Tweede';
		elseif ($logic['interval'] > 14 && $logic['interval'] <= 21) :
			$logic['interval'] = 'Third';
			$logic['repeat'] = 'Derde';
		elseif ($logic['interval'] > 21 && $logic['interval'] <= 28) :
			$logic['interval'] = 'Fourth';
			$logic['repeat'] = 'Vierde';
		endif;
		//for($year = 2022; $year <= 2023; $year++) :

		//	for($month = 1; $month <= 12; $month++) :
		//		var_dump((new DateTime('Last Tuesday of ' . (new DateTime('01-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . $year))->format('F Y')))->format('l j F Y'));
		//		echo '<br />';
		//	endfor;
		//endfor;

		return (object)$logic;
	}

	/**
	 * Get a logical structure of a date.
	 *
	 * @param  mixed $datetime
	 * @param  object $logic Logical Date object
	 * @return DateTime
	 */
	public static function AddDateTimeLogic($datetime, $logic)
	{
		$datetime = self::GetDate($datetime);
		if($logic->type == 'week') :
			return (new DateTime($logic->interval . ' ' . $logic->day . ' of ' . $datetime->format('F Y')));
		endif;
		return $datetime;
	}
	/**
	 * Get a ```bigint``` of the given date
	 *
	 * @param  mixed $datetime
	 * @return int 20161220094200
	 */
	public static function DateSort($datetime)
	{
		return (int)self::GetDate($datetime)->format('YmdHis');
	}

	/**
	 * Check if given date is today
	 *
	 * @param  mixed $datetime
	 * @return bool
	 */
	public static function isToday($datetime)
	{
		return self::GetDate($datetime)->format('Y-m-d') == (new DateTime('now'))->format('Y-m-d');
	}

	/**
	 * Check if given date is within this this week
	 *
	 * @param  mixed $datetime
	 * @return bool
	 */
	public static function isWithinNextWeek($datetime)
	{
		return Format::isWithinXDays($datetime, 6);
	}

	/**
	 * Check if given date is within the given days in ```$x```
	 *
	 * @param  mixed $datetime
	 * @param  int|float $x Add days to datetime. When negative it subtract the amount of days.
	 * @return bool
	 */
	public static function isWithinXDays($datetime, $x)
	{
		$currentDate = self::GetDate($datetime);
		$today = self::GetDate('today');
		$nextDate = self::GetDate('today');
		if($x > 0) :
			$nextDate->add(new DateInterval('P' . $x . 'D'));
			if ($currentDate >= $today && $currentDate <= $nextDate) :
				return TRUE;
			endif;
		else :
			if ($currentDate >= $nextDate) :
				return TRUE;
			endif;
		endif;

		return FALSE;
	}



	/**
	 * Check if given date is within between the in ```$begin``` and` ``$end``` datetime
	 *
	 * @param  mixed $datetime DateTime to compare.
	 * @param  mixed $begin DateTime ot the beginning.
	 * @param  mixed $end DateTime of the end.
	 * @param  int|float $x Add days to datetime. When negative it subtract the amount of days.
	 * @return bool
	 */
	public static function isBetween($datetime, $begin, $end, $x = 0)
	{
		$currentDate = self::GetDate($datetime);
		$begin = self::GetDate($begin);
		$end = self::GetDate($end);
		//var_dump($currentDate);
		if ($currentDate >= $begin && $currentDate <= $end) :
			return TRUE;
		endif;

		return FALSE;
	}

	/**
	 * jsDateTime
	 * @deprecated in 1.3.107 @see Format::ISO8601()
	 *
	 * @param  mixed $datetime
	 * @return string
	 */
	public static function jsDateTime($datetime)
	{
		return self::ISO8601($datetime);
	}

	/**
	 * Gets a order by based on the TimeStamp
	 *
	 * @param  mixed $datetime
	 * @param  int $order
	 * @return int 2016355094200
	 */
	public static function TimeStampOrder($datetime, $order)
	{
		return (int)self::GetDate($datetime)->format('yzHi') . str_pad($order, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Get The Unix Timestamp from a datetime object
	 *
	 * @param object datetime
	 * @return int 1450600974
	 */
	public static function TimeStamp($datetime)
	{
		return self::GetDate($datetime)->getTimeStamp();
	}


	/**
	 * Added minutes to the start time to calculate the end time
	 *
	 * @param  mixed $datetime
	 * @param  int $duration
	 * @return string
	 */
	public static function jsEndDateTime($datetime, $duration)
	{
		if(empty($duration))
		{
			$duration = 60;
		}
		$DateTime = self::GetDate($datetime);
		$DateTime->add(new DateInterval('PT' . $duration . 'M'));
		return self::ISO8601($DateTime);
	}

	/**
	 * Check if date is in the future
	 *
	 * @param  mixed $date
	 * @return bool
	 */
	public static function dateInFuture_YN($date)
	{
		$datetimeObj1 = new DateTime($date);
		$datetimeObj2 = new DateTime();
		$interval = $datetimeObj1->diff($datetimeObj2);
		$dateDiff = $interval->format('%R%a');

		return $dateDiff > 0;
	}

	/**
	 * Get the SQL DateTime
	 *
	 * @param  mixed $datetime
	 * @return string 2016-12-20 09:42:00
	 */
	public static function sqlDateTime($datetime)
	{
		return self::GetDate($datetime) ->format('Y-m-d H:i:s');
	}

	/**
	 * Get the SQL Date
	 *
	 * @param  mixed $datetime
	 * @return string 2016-12-20
	 */
	public static function sqlDate($datetime)
	{
		return self::GetDate($datetime)->format('Y-m-d');
	}

	/**
	 * Get the SQL Time
	 *
	 * @param  mixed $datetime
	 * @return string 09:42:00
	 */
	public static function sqlTime($datetime)
	{
		return self::GetDate($datetime)->format('H:i:s');
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
		if(is_numeric($object)) :
			$object = ($object == 1);
		elseif (is_string($object)) :
			$object = strtolower($object);
			if($object == 'y' || $object == 'j' || $object == 'yes' || $object == 'ja' ||  $object == 'on' ||  $object == '1') :
				$object = true;
			elseif($object == 'n' || $object == 'no' || $object == 'nee' ||  $object == 'off' ||  $object == '0') :
				$object = false;
			endif;
		endif;

		if(is_bool($object)) :
			return $object;
		endif;
		return false;
	}

	/**
	 * Gets the text ```Ja``` or ```Nee```
	 *
	 * @param  bool|mixed $object
	 * @return string
	 */
	public static function YesNo($object)
	{
		$object = Format::ConvertToBool($object);
		if(is_bool($object)) :
			return ($object ? 'Ja' : 'Nee');
		endif;
		return 'Nee';
	}

	/**
	 * Mark the checkbox as checked
	 *
	 * @param  bool|mixed $object
	 * @return string
	 */
	public static function Checked($object)
	{
		$object = Format::ConvertToBool($object);

		if(is_bool($object)) :
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
		if(!empty($email)) :
			// split on @ and return last value of array (the domain)
			if(str_contains($email, '@')) :
				//$domain = array_pop(explode('@', $email));
				return str_contains($email, 'print');
			endif;
		endif;
		return false;
	}

	/**
	 * Gets the name from the user when it's a GUID
	 *
	 * @param string $email E-mail adres or GUID of user ID

	 * @return string|null  Returns the name of the user. If not a valid e-mail it returns the default data
	 */
	public static function GetUserName($email)
	{
		if (is_guid($email)) :
			global $mysqli;
			global $account_ID;
			if (is_guid($account_ID)) :
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

	 * @return string|null  Returns the e-mail of the user. If not a valid e-mail it returns ```null```
	 */
	public static function GetUserMail($email)
	{
		if(is_guid($email)) :
			global $mysqli;
			global $account_ID;
			if(is_guid($account_ID)) :
				if($result = $mysqli->query("SELECT `email` FROM `users` WHERE `ID` = '$email' AND `account_ID` = '$account_ID'")) :
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

	public static function encrypt($string)
	{
		$iv = mcrypt_create_iv(
			mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC),
			MCRYPT_DEV_URANDOM
		);

		$encrypted = base64_encode(
			$iv .
			mcrypt_encrypt(
				MCRYPT_RIJNDAEL_128,
				hash('sha256', ENCKEY, true),
				$string,
				MCRYPT_MODE_CBC,
				$iv
			)
		);

		return $encrypted;
	}

	public static function decrypt($encrypted)
	{
		$data = base64_decode($encrypted);
		$iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));

		$decrypted = rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				hash('sha256', ENCKEY, true),
				substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)),
				MCRYPT_MODE_CBC,
				$iv
			),
			"\0"
		);

		return $decrypted;
	}


	/**
	 * pluralize s string
	 *
	 * @param  int $count
	 * @param  string $single text without pluralized string
	 * @param  string $double pluralized text
	 * @return string
	 */
	private static function pluralize( $count, $single, $double )
	{
	    return $count . ( $count == 1 ? $single : $double );
	}


	/**
	 * Get Time Ago
	 *
	 * @param  mixed $datetime
	 * @return string
	 */
	public static function TimeAgo( $datetime )
	{
		$datetime = self::GetDate($datetime);
	    $interval = date_create('now')->diff( $datetime );
	    $suffix = ( $interval->invert ? ' geleden' : '' );
	    if ( $v = $interval->y >= 1 ) return Format::pluralize( $interval->y, ' jaar', ' jaren' ) . $suffix;
	    if ( $v = $interval->m >= 1 ) return Format::pluralize( $interval->m, ' maand', ' maanden' ) . $suffix;
	    if ( $v = $interval->d >= 1 ) return Format::pluralize( $interval->d, ' dag', ' dagen' ) . $suffix;
	    if ( $v = $interval->h >= 1 ) return Format::pluralize( $interval->h, ' uur', ' uren' ) . $suffix;
	    if ( $v = $interval->i >= 1 ) return Format::pluralize( $interval->i, ' minuut', ' minuten' ) . $suffix;
	    return ($interval->s < 5 ? 'zojuist' : ' seconden geleden' );
	}

	/**
	 * Get Duration text
	 *
	 * @param  int $minutes Minutes to add or subtract from the time of now
	 * @return string
	 */
	public static function Duration( $minutes )
	{
		$datetime = date_create('today');
		if($minutes >= 0) :

			$datetime->add(new DateInterval( 'PT' . ( (integer) $minutes ) . 'M' ) );
		else :
			$datetime->sub(new DateInterval( 'PT' . ( abs((integer) $minutes )) . 'M' ) );
		endif;
	    $interval = date_create('today')->diff( $datetime );

		$text = '';
	    if ( $v = $interval->y >= 1 ) :
	    	$text .= Format::pluralize( $interval->y, ' jaar ', ' jaren ' );
	    endif;
	    if ( $v = $interval->m >= 1 )  :
	    	$text .= Format::pluralize( $interval->m, ' maand ', ' maanden ' );
	    endif;

	    if ( $v = $interval->d >= 1 )  :
	    	$text .= Format::pluralize( $interval->d, ' dag ', ' dagen ' );
	    endif;

	    if ( $v = $interval->h >= 1 )  :
	    	$text .= Format::pluralize( $interval->h, ' uur ', ' uren ' );
	    endif;

	    if ( $v = $interval->i >= 1 )  :
	    	$text .= Format::pluralize( $interval->i, ' minuut ', ' minuten ' );
	    endif;

	    if ( $v = $interval->s >= 1 )  :
	    	$text .= Format::pluralize( $interval->i, ' seconde ', ' seconden ' );
	    endif;

		if(empty($text)) :
			return 'Geen';
		endif;
	    return $text;
	}

	/**
	 * Eclipse a text
	 *
	 * @param  string $string source text
	 * @param  int $length length to cut the text
	 * @return string
	 */
	public static function Eclipse($string, $length)
	{
		if(strlen($string) > $length + 3) :
			return substr($string, 0, $length) . '...';
		else :
			return $string;
		endif;
	}

	/**
	 * Escapte the input value
	 *
	 * @param  string $value
	 * @param  string $escape
	 * @return string
	 */
	public static function InputValue($value, $escape)
	{
		return str_replace($escape, "\$escape", $value);
	}

	/**
	 * Get a list of subscription types
	 *
	 * @param  string $sender
	 * @return string
	 */
	public static function subscriptionTypes($sender)
	{
		$return = '';
		$items = explode(',', $sender);
		foreach($items as $index => $item)
		{
			$return .= sprintf('%d %s | ', ($index + 1), $item);
		}
		return substr($return, 0, -3);
	}
	/**
	 * Get the attendee status for the timetable subscription
	 *
	 * @param  int $index
	 * @param  int $count
	 * @return string
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
	 * @param  string $class
	 * @return string
	 */
	public static function HexTextColor($class)
	{
		switch($class)
		{
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
	 * @param  string $class
	 * @return string
	 */
	public static function HexBorderColor($class)
	{
		switch($class)
		{
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
		switch($class)
		{
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
	 * @param  string $hexColor
	 * @return string
	 */
	public static function GetContrastColor($hexColor)
	{
		$prefix = '';
		if($hexColor[0] === '#') :
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
	 * @param  string $class
	 * @return string color name
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
	 * @param  string $class
	 * @return string
	 */
	public static function ColorCSS($class) {
		return "color:" . Format::HexTextColor($class) . ";border-color:" . Format::HexBorderColor($class) . ";background:" . Format::HexBackgroundColor($class) . ";";
	}

	/**
	 * Gets the intention type
	 *
	 * @param  string $type
	 * @param  bool $plural
	 * @return string
	 */
	public static function Intention($type, $plural = false)
	{
		switch($type)
		{
			case IntentionType::Intention->value:
				if($plural) :
					return 'Misintenties';
				else :
					return 'Misintentie';
				endif;
			case 'intention_default':
				if ($plural) :
					return 'Standaard misintenties';
				else :
					return 'Standaard misintentie';
				endif;
			case 'queue':
				if ($plural) :
					return 'Misintenties (webshop)';
				else :
					return 'Misintentie (webshop)';
				endif;
			case 'foundation':
				if ($plural) :
					return 'Stichting';
				else :
					return 'Stichting';
				endif;
			case 'foundations':
				if ($plural) :
					return 'Gestichte jaardiensten';
				else :
					return 'Gestichte jaardienst';
				endif;
			case IntentionType::Announcement->value:
				if($plural) :
					return 'Mededelingen';
				else :
					return 'Mededeling';
				endif;
				break;
			case IntentionType::Funeral->value:
				if ($plural) :
					return 'Uitvaarten van deze week';
				else :
					return 'Uitvaart van deze week';
				endif;
			case IntentionType::Baptize->value:
				if ($plural) :
					return 'Doopsels van deze week';
				else :
					return 'Doopsel van deze week';
				endif;
			case IntentionType::Marriage->value:
				if ($plural) :
					return 'Huwelijken van deze week';
				else :
					return 'Huwelijk van deze week';
				endif;
		}
		return 'Intentie';
	}

	/**
	 * Gets the intention type name
	 *
	 * @param  string $type
	 * @param  bool $plural
	 * @return string
	 */
	public static function IntentionType($type, $plural = false)
	{
		switch ($type) {
			case IntentionType::Intention->value:
			case 'intention_default':
			case 'queue':
			case 'foundation':
				if ($plural) :
					return 'Misintenties';
				else :
					return 'Misintentie';
				endif;
			case IntentionType::Announcement->value:
				if ($plural) :
					return 'Mededelingen';
				else :
					return 'Mededeling';
				endif;
				break;
			case IntentionType::Funeral->value:
				if ($plural) :
					return 'Uitvaarten';
				else :
					return 'Uitvaart';
				endif;
			case IntentionType::Baptize->value:
				if ($plural) :
					return 'Doopsels';
				else :
					return 'Doopsel';
				endif;
			case IntentionType::Marriage->value:
				if ($plural) :
					return 'Huwelijken';
				else :
					return 'Huwelijk';
				endif;
		}
		return 'Intentie';
	}

	/**
	 * Gets the intention color
	 *
	 * @param  string $type
	 * @return string
	 */
	public static function IntentionColor($type)
	{
		switch ($type) {
			case IntentionType::Funeral->value:
				return 'black';
			case IntentionType::Baptize->value:
				return 'blue';
			case IntentionType::Marriage->value:
				return 'red';
		}
		return 'primary';
	}

	/**
	 * Gets the funeral formatted text
	 *
	 * @param  string $json
	 * @param  string $date
	 * @return string
	 */
	public static function Funeral($json, $date = '')
	{
		$note_arr = (array)json_decode($json);
		try {
			$note_arr['Uitvaart op'] = self::FullDateAt($date);
		} catch (Exception $e) {
			$note_arr['Uitvaart op'] = $date;
		}
		if(is_numeric($note_arr['Leeftijd'])) :
			if($note_arr['Leeftijd'] > 0) :
				$note_arr['Naam'] .= ' (' . $note_arr['Leeftijd'] . ' jaar)';
			endif;
		endif;

		if (is_numeric($note_arr['Geslacht'])) :
			$note_arr['Naam'] = self::GetGender($note_arr['Geslacht'], $note_arr['Leeftijd']) . ' ' . $note_arr['Naam'];
			unset($note_arr['Geslacht']);
		endif;
		if(array_key_exists('Leeftijd', $note_arr)) :
			unset($note_arr['Leeftijd']);
		endif;

		if(empty($date)) :
			$note_arr = array_filter($note_arr, fn ($value) => !is_null($value) && !empty($value));
		endif;
		$note_text = '';
		foreach (CommonFunctions::GetIntentionJson(IntentionType::Funeral->value) as $key => $value) :
			if (key_exists($key, $note_arr)) :
				if($key == 'Naam') :
					$note = $note_arr[$key];
				elseif ($key == 'Uitvaart op') :
					$note = $key . ' ' . $note_arr[$key];
				else :
					$note = $key . ': ' . $note_arr[$key];
				endif;
				$note = htmlspecialchars(ltrim(str_replace(EOL_SPLIT_REPLACE, EOL_SPLIT_SEARCH, $note), EOL_SPLIT));
				if (!empty($note)) :
					$note_text .= $note . PHP_EOL;
				endif;
			endif;
		endforeach;
		return $note_text;
	}
	/**
	 * Gets the baptize formatted text
	 *
	 * @param  string $json
	 * @param  string $date
	 * @return string
	 */
	public static function Baptize($json, $date = '')
	{
		$note_arr = (array)json_decode($json);
		try {
			$note_arr['Doop op'] = self::FullDateAt($date);
		} catch (Exception $e) {
			$note_arr['Doop op'] = $date;
		}
		if(isset($note_arr['Geboortedatum'])):
			if (is_numeric($note_arr['Geboortedatum'])) :
				$note_arr['Dopeling'] .= ' (' . $note_arr['Geboortedatum'] . ' jaar)';
				unset($note_arr['Geboortedatum']);
			else :
				$note_arr['Geboortedatum'] = self::FullDate($note_arr['Geboortedatum']);
			endif;
		endif;
		if (empty($date)) :
			$note_arr = array_filter($note_arr, fn ($value) => !is_null($value) && !empty($value));
		endif;
		$note_text = '';

		foreach (CommonFunctions::GetIntentionJson(IntentionType::Baptize->value) as $key => $value) :
			$note = '';
			if (key_exists($key, $note_arr)) :
				if ($key == 'Dopeling') :
					$note = 'Doop van ' . trim($note_arr[$key]);
				elseif ($key == 'Geboortedatum') :
					$note = '';
				else :
					$note = $key . ': ' . $note_arr[$key];
				endif;
				$note = htmlspecialchars(ltrim(str_replace(EOL_SPLIT_REPLACE, EOL_SPLIT_SEARCH, $note), EOL_SPLIT));
				if (!empty($note)) :
					$note_text .= $note . PHP_EOL;
				endif;
			endif;
		endforeach;
		$note_text = trim(trim(rtrim($note_text, PHP_EOL)) . (!empty($date) ? ' op ' . $date : ''));
		return $note_text;
	}
	/**
	 * Gets the marriage formatted text
	 *
	 * @param  string $json
	 * @param  string $date
	 * @return string
	 */
	public static function Marriage($json, $date = '')
	{
		$note_arr = (array)json_decode($json);
		try {
			$note_arr['Huwelijk op'] = self::FullDateAt($date);
		} catch (Exception $e) {
			$note_arr['Huwelijk op'] = $date;
		}

		if (empty($date)) :
			$note_arr = array_filter($note_arr, fn ($value) => !is_null($value) && !empty($value));
		endif;
		$note_text = '';
		foreach (CommonFunctions::GetIntentionJson(IntentionType::Marriage->value) as $key => $value) :
			if (key_exists($key, $note_arr)) :
				if ($key == 'Bruid') :
					$note = 'Huwelijk van ' . $note_arr['Bruid']  . ' & ' . $note_arr['Bruidegom'];
				elseif ($key == 'Bruidegom') :
					continue;
				elseif ($key == 'Huwelijk op') :
					$note = 'op ' . $note_arr[$key];
				else :
					$note = $key . ': ' . $note_arr[$key];
				endif;
				$note = htmlspecialchars(ltrim(str_replace(EOL_SPLIT_REPLACE, EOL_SPLIT_SEARCH, $note), EOL_SPLIT));
				if (!empty($note)) :
					$note_text .= $note . PHP_EOL;
				endif;
			endif;
		endforeach;
		$note_text = trim(rtrim($note_text, PHP_EOL) . (!empty($date) ? ' op ' . $date : ''));
		return $note_text;
	}

	/**
	 * Get the note text of an intention
	 *
	 * @param  string $note
	 * @param  string $type
	 * @param  string $date
	 * @return string
	 */
	public static function IntentionNote($note, $type ='', $date = '')
	{
		if (CommonFunctions::IsJson($note)) :
			if ($type == IntentionType::Funeral->value) :
				$note = Format::Funeral($note, $date);
			elseif ($type == IntentionType::Baptize->value) :
				$note = Format::Baptize($note, $date);
			elseif ($type == IntentionType::Marriage->value) :
				$note = Format::Marriage($note, $date);
			endif;
		endif;
		return nl2br($note);
	}

	/**
	 * Gets the index of the intention for correct sorting.
	 *
	 * @param  string $type
	 * @return int
	 */
	public static function IntentionIndex($type)
	{
		switch($type)
		{
			case IntentionType::Intention->value:
			case 'queue':
			case 'foundation':
				return 1;
			case 'intention_default':
				return 2;
			case IntentionType::Funeral->value:
			case IntentionType::Baptize->value:
			case IntentionType::Marriage->value:
				return 3;
			case IntentionType::Announcement->value:
				return 4;
		}
		return 0;
	}

	/**
	 * Gets the index of the intention for correct sorting.
	 *
	 * @param  string $type
	 * @param  string $note
	 * @return int
	 */
	public static function IntentionSort($type, $note)
	{
		$note = strtolower($note);
		$index = self::IntentionIndex($type) * 100;

		switch ($type) {
			case 'foundation':
				$index += 10;
				break;
			case IntentionType::Intention->value:
				$index += 10;
				break;
			case 'queue':
				$index += 10;
				break;
			case 'intention_default':
				$index += 30;
				break;
			case IntentionType::Funeral->value:
				$index += 40;
				break;
			case IntentionType::Baptize->value:
				$index += 50;
				break;
			case IntentionType::Marriage->value:
				$index += 60;
				break;
			case IntentionType::Announcement->value:
				$index += 70;
				break;
		}

		if(str_contains($note, 'zeswekendienst')) :
			$index += 1;
		elseif(str_contains($note, 'jaardienst')) :
			$index += 2;
		elseif (str_contains($note, 'zaliger')) :
			$index += 3;
		elseif (str_contains($note, 'zielen')) :
			$index += 4;
		elseif (str_contains($note, 'verjaardag')) :
			$index += 5;
		else :
			$index += 6;
		endif;
		return $index;
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
	 * @return array
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
			'choir'				=> 'Koor'
		);
	}

	/**
	 * Gender enum
	 *
	 * @return array
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
			'choir'				=> 'Koren'
		);
	}

	/**
	 * Title enum
	 *
	 * @return array
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
			'choir'				=> 'Geacht koor'
		);
	}



	/**
	 * Function enum
	 *
	 * @return array
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
			'choir'				=> 'Koor'
		);
	}

	/**
	 * Function enum
	 *
	 * @return array
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
			'choir'				=> 'Koor	'
		);
	}

	/**
	 * Function enum
	 *
	 * @return array
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
			'choir'				=> 'Koor'
		);
	}

	/**
	 * Marital Status
	 *
	 * @return array
	 */
	public static function MaritalStatus($gender = NULL, $empty = 'Niet bekend')
	{
		switch($gender) :
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
			if(isset($gender) && $gender !== null) :
				if(is_array($gender) && isset($gender['gender']) && !empty($gender['gender'])) :
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

			if(isset($gender) && is_string($gender) && array_key_exists($gender, $array)) :
				return $array[$gender];
			endif;
		} catch(Exception $e) {
			return $max;
		} finally {
			return $max;
		}
	}

	/**
	 * Gets the name of the gender
	 *
	 * @param  string $gender
	 * @return string
	 */
	public static function GetGenderName($gender)
	{
		return self::Gender()[$gender];
	}

	/**
	 * Gets the name of the gender
	 *
	 * @param  int $gender,
	 * @param  int $age
	 * @return string
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
				default:
					return '';
			endswitch;
		endif;
	}

	public static function GetGenderTitle($gender)
	{
		return (array_values(self::Title()))[$gender-1];
	}

	/**
	 * Gets the name of a function
	 *
	 * @param  string $gender
	 * @return string
	 */
	public static function GetFunction($gender)
	{
		if(!empty($gender)) :
			return self::Function()[$gender];
		else :
			return '';
		endif;
	}

	/**
	 * Gets the name of a function for the choir
	 *
	 * @param  string $gender
	 * @return string
	 */
	public static function GetFunctionChoir($gender)
	{
		return self::FunctionChoir()[$gender];
	}

	/**
	 * Gets the name of a function for the musician
	 *
	 * @param  string $gender
	 * @return string
	 */
	public static function GetFunctionMusician($gender)
	{
		return self::FunctionMusician()[$gender];
	}

	/**
	 * Gets the title of a person
	 *
	 * @param  string $gender
	 * @return void
	 */
	public static function GetTitle($gender)
	{
		return self::Title()[$gender];
	}

	/**
	 * Gets the address letterhead of a person
	 *
	 * @param  string $gender
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
		return Format::FullDateAt($start) . ' in de ' . $church;
	}

	/**
	 * Get the information of a mass my ID
	 *
	 * @param  string|guid $masses_ID
	 * @return object|null
	 */
	public static function GetMass($masses_ID)
	{
		$sql = "SELECT `masses`.`start`, `church`.`title` AS `church` FROM `masses` INNER JOIN `church` ON `church`.`ID` =  `masses`.`church_ID` AND `church`.`account_ID` = `masses`.`account_ID` WHERE `masses`.`ID` = '$masses_ID' AND `masses`.`account_ID` = '" . (string)$_SESSION['account_ID'] . "'";
		global $mysqli;
		if($result = $mysqli->query($sql)) :
			$row = $result->fetch_object();
			return $row;
		endif;
		return NULL;
	}

	/**
	 * Trim a text by a specific length. Based on the last full word.
	 * @deprecated 1.3.107 @see wordwrap()
	 *
	 * @param  mixed $s Input text
	 * @param  mixed $max_length Maximum length to trim.
	 * @return void
	 */
	public static function Trim($s, $max_length = 300)
	{
		if (strlen($s) > $max_length) :
		    $offset = ($max_length - 3) - strlen($s);
		    $s = substr($s, 0, strrpos($s, ' ', $offset)) . '...';
		endif;
		return $s;
	}

	/**
	 * Clean the filename by removing all special charters
	 *
	 * @param  string $name
	 * @return string
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
}