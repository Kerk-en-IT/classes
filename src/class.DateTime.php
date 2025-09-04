<?php

namespace KerkEnIT;
use KerkEnIT\Format;
/**
 * DateTime Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package		KerkEnIT
 * @subpackage	DateTime
 * @author		Marco van 't Klooster <info@kerkenit.nl>
 * @copyright	2010-2025 © Kerk en IT
 * @license		https://www.gnu.org/licenses/gpl-3.0.html	GNU General Public License v3.0
 * @link		https://www.kerkenit.nl
 * @since		Class available since Release 1.1.0
 **/
class DateTime
{
	/**
	 * Get Date from various types
	 *
	 * @param mixed $datetime
	 * @param string|bool|DateTimeZone $timezone
	 * @return \DateTime DateTime object
	 */
	public static function GetDate(mixed $datetime, string|bool|\DateTimeZone $timezone = false): \DateTime
	{
		$date = null;
		if (($timezone instanceof \DateTimeZone) === false) :
			if ($timezone === false || empty($timezone)) :
				$timezone = date_default_timezone_get();
			endif;
			if (is_string($timezone)) :
				$timezone = new \DateTimeZone($timezone);
			endif;
		endif;

		if ($datetime !== null && is_object($datetime) && $datetime instanceof \DateTime) :
			return $datetime;
		elseif ($datetime !== null && is_object($datetime) && $datetime instanceof \DateTimeImmutable) :
			$date = new \DateTime('now', $datetime->getTimezone());
			$date->setTimestamp($datetime->getTimestamp());
			return $date;
		elseif ($datetime !== null && \is_array($datetime) && isset($datetime['date']) && is_string($datetime['date'])) :
			$date = new \DateTime($datetime['date']);
			if (isset($datetime['timezone']) && $datetime['timezone'] instanceof \DateTimeZone) :
				$date->setTimezone($datetime['timezone']);
			elseif (isset($datetime['timezone']) && is_string($datetime['timezone'])) :
				$date->setTimezone(new \DateTimeZone($datetime['timezone']));
			endif;
			return $date;
		elseif ($datetime !== null && \is_object($datetime) && isset($datetime->date) && is_string($datetime->date)) :
			$date = new \DateTime($datetime->date);
			if (isset($datetime->timezone) && $datetime->timezone instanceof \DateTimeZone) :
				$date->setTimezone($datetime->timezone);
			elseif (isset($datetime->timezone) && is_string($datetime->timezone)) :
				$date->setTimezone(new \DateTimeZone($datetime->timezone));
			endif;
			return $date;
		elseif ($datetime !== null && (is_numeric($datetime) || is_integer($datetime) || is_long($datetime))) :
			$date = new \DateTime();
			$date->setTimestamp($datetime);
		elseif ($datetime !== null && is_string($datetime) && (strlen($datetime) == 8 || strlen($datetime) == 5) && is_numeric(str_replace(':', '', $datetime))) :
			$date = new \DateTime((new \DateTime())->format('Y-m-d ') . $datetime);
		elseif (!is_object($datetime)) :
			try {
				$date = new \DateTime();
				if (is_numeric($datetime)) :
					$date = new \DateTime();
					$date->setTimestamp($datetime);
				elseif (!is_object($datetime)) :

					if (is_string($datetime)) :
						if (strlen($datetime) >= 10 && $datetime[5] !== '-' && $datetime[2] !== ':') :
							$items = explode('-', $datetime);
							if (\count($items) >= 3) :
								if (strlen($items[0]) == 4) :
									$datetime = $items[0] . '-' . $items[1] . '-' . $items[2];
								else :
									$datetime = $items[2] . '-' . $items[1] . '-' . $items[0];
								endif;
							endif;
						endif;
					endif;
					try {
						if (is_string($datetime)) :
							$datetime = str_replace(array(' jan ', ' jan.'), ' jan ', $datetime);
							$datetime = str_replace(array(' feb ', ' feb. ', ' febr ', ' febr. '), ' feb ', $datetime);
							$datetime = str_replace(array(' mrt ', ' mrt. '), ' mar ', $datetime);
							$datetime = str_replace(array(' apr ', ' apr. '), ' apr ', $datetime);
							$datetime = str_replace(array(' mei ', ' mei. '), ' may ', $datetime);
							$datetime = str_replace(array(' jun ', ' jun. ', ' juni '), ' jun ', $datetime);
							$datetime = str_replace(array(' jul ', ' jul. ', ' juli '), ' jul ', $datetime);
							$datetime = str_replace(array(' aug ', ' aug. '), ' aug ', $datetime);
							$datetime = str_replace(array(' sep ', ' sep. ', ' sept ', ' sept. '), ' sep ', $datetime);
							$datetime = str_replace(array(' okt ', ' okt. '), ' oct ', $datetime);
							$datetime = str_replace(array(' nov ', ' nov. '), ' nov ', $datetime);
							$datetime = str_replace(array(' dec ', ' dec. '), ' dec ', $datetime);
							if (\str_contains($datetime, ' en ')) :
								$datetime = explode(' en ', $datetime)[0];
							endif;
							$datetime = \ltrim($datetime, '-');
							try {
								$date = new \DateTime($datetime ?? 'now');
							} catch (Exception $e) {
								try {
									$date = new \DateTime(substr($datetime, 0, strlen('2000-01-01 00:00:00') - 1) ?? 'now');
								} catch (Exception $e) {
									$date = new \DateTime(substr($datetime, 0, strlen('2000-01-01') - 1) ?? 'now');
								}
							}
						endif;
					} catch (Exception $e) {
						try {
							if (is_string($datetime)) :
								$items = explode('-', $datetime);
								if (count($items) >= 3) :
									if (strlen($items[0]) == 4) :
										$datetime = $items[0] . '-' . $items[1] . '-' . $items[2];
									else :
										$datetime = $items[2] . '-' . $items[1] . '-' . $items[0];
									endif;
								endif;
							endif;
							$date = new \DateTime($datetime ?? 'now');
						} catch (Exception $e) {
							$date = new \DateTime();
						}
					}
				endif;
			} catch (Exception $e) {
				$datetime = str_replace(array(' jan ', ' jan.'), ' jan ', $datetime);
				$datetime = str_replace(array(' feb ', ' feb. ', ' febr ', ' febr. '), ' feb ', $datetime);
				$datetime = str_replace(array(' mrt ', ' mrt. '), ' mar ', $datetime);
				$datetime = str_replace(array(' apr ', ' apr. '), ' apr ', $datetime);
				$datetime = str_replace(array(' mei ', ' mei. '), ' may ', $datetime);
				$datetime = str_replace(array(' jun ', ' jun. ', ' juni '), ' jun ', $datetime);
				$datetime = str_replace(array(' jul ', ' jul. ', ' juli '), ' jul ', $datetime);
				$datetime = str_replace(array(' aug ', ' aug. '), ' aug ', $datetime);
				$datetime = str_replace(array(' sep ', ' sep. ', ' sept ', ' sept. '), ' sep ', $datetime);
				$datetime = str_replace(array(' okt ', ' okt. '), ' oct ', $datetime);
				$datetime = str_replace(array(' nov ', ' nov. '), ' nov ', $datetime);
				$datetime = str_replace(array(' dec ', ' dec. '), ' dec ', $datetime);

				$date = new \DateTime($datetime);
			}

		endif;
		if($date === null) :
			$date = new \DateTime();
		endif;
		if ($timezone !== false && ($timezone instanceof \DateTimeZone)) :
			$date->setTimezone($timezone);
		endif;
		return $date;
	}

	public static function GetTimestamp(mixed $datetime, string|bool|\DateTimeZone $timezone = false): int
	{
		return self::GetDate($datetime, $timezone)->getTimestamp();
	}

	public static function GetTimezoneDate($time): \DateTime
	{
		$datetime = self::GetDate($time);
		$timezone = 'Europe/Amsterdam';
		if ($datetime > new \DateTime('2024-01-10') && $datetime < new \DateTime('2024-03-12')) :
			$timezone = 'America/Panama';
		endif;
		$schedule_date = new \DateTime($datetime->format('Y-m-d H:i:s'), new \DateTimeZone($timezone));
		$schedule_date->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

		return $schedule_date;
	}

	/**
	 *
	 * Get the date for the current culture
	 *
	 * @param string $date input date
	 * @return	string formatted date
	 */
	public static function culture_date($date, $style = \IntlDateFormatter::LONG): string
	{
		$time = self::GetDate($date);
		global $culture;
		$formatter = new \IntlDateFormatter(str_replace('-', '_', $culture), $style, \IntlDateFormatter::NONE);
		return $formatter->format($time);
	}

	/**
	 *
	 * Get the date for the current culture
	 *
	 * @param string $date input date
	 * @return	string formatted date
	 */
	public static function culture_time($date, $style = \IntlDateFormatter::SHORT): string
	{
		$time = time();

		if (is_numeric($date)) :
			$time = $date;
		elseif (is_object($date) && $date instanceof \DateTime) :
			$time = $date;
		else :
			$time = strtotime($date);
		endif;
		global $culture;
		$formatter = new \IntlDateFormatter(str_replace('-', '_', $culture), \IntlDateFormatter::NONE, $style);
		return $formatter->format($time);
	}

	/**
	 *
	 * Get the date and time for the current culture
	 *
	 * @param string $date input date
	 * @return	string formatted date
	 */
	public static function culture_datetime($date): string
	{
		return self::culture_date($date) . ' ' . __('at') . ' ' . self::culture_time($date);
	}

	/**
	 *
	 * Get the weekday, date and time for the current culture
	 *
	 * @param string $date input date
	 * @return	string formatted date
	 */
	public static function culture_full_datetime($date): string
	{
		return self::culture_date($date, \IntlDateFormatter::FULL) . ' ' . __('at') . ' ' . self::culture_time($date);
	}

	/**
	 *
	 * Get the date in RSS standard.
	 * D, d M Y H:i:s O
	 *
	 * @param string $date input date
	 * @param string $timezone destination TimeZone
	 * @return	string formatted date in RSS standard
	 * E.g. Thu, 07 Dec 2023 14:19:00 +0100
	 * When TimeZone = UTC it will return Thu, 07 Dec 2023 13:19:00 +0000
	 */
	public static function RSS($date, $timezone = 'Europe/Amsterdam'): string
	{
		$datetime = new \DateTime('now', new \DateTimeZone('Europe/Amsterdam'));
		if (is_numeric($date)) :
			$datetime->setTimestamp($date);
		else :
			$datetime = self::GetDate($date, new \DateTimeZone('Europe/Amsterdam'));
		endif;
		if ($timezone == 'UTC') :
			if ((int)$datetime->format('His') === 0) :
				$datetime->setTimezone(new \DateTimeZone($timezone));
				$datetime->setTime(0, 0);
			else :
				$datetime->setTimezone(new \DateTimeZone($timezone));
			endif;
			return $datetime->format(\DateTime::RSS); // Updated ISO8601
		elseif ($timezone == 'GMT') :
			if ((int)$datetime->format('His') === 0) :
				$datetime->setTimezone(new \DateTimeZone($timezone));
				$datetime->setTime(0, 0);
			else :
				$datetime->setTimezone(new \DateTimeZone($timezone));
			endif;
			return $datetime->format(\DateTime::RFC7231); // Updated ISO8601
		else :
			if ((int)$datetime->format('His') === 0) :
				$datetime->setTimezone(new \DateTimeZone($timezone));
				$datetime->setTime(0, 0);
			else :
				$datetime->setTimezone(new \DateTimeZone($timezone));
			endif;
			return $datetime->format(\DateTime::RSS); // Updated ISO8601
		endif;
	}


	/**
	 *
	 * Get the date in ISO 8601 standard.
	 * Also known as ATOM date
	 *
	 * @param mixed $datetime input date
	 * @param string|bool $timezone destination TimeZone
	 * @return	string formatted date in ISO 8601 standard
	 * E.g. 2022-12-01T13:47:24+01:00
	 * When TimeZone = UTC it will return 2009-02-28T18:56:23Z
	 */
	public static function ISO8601($datetime = 'now', $timezone = 'Europe/Amsterdam'): string
	{
		$date = self::GetDate($datetime, $timezone);
		if ($timezone !== false) :
			$date->setTimezone(new \DateTimeZone($timezone));
		endif;
		if ($timezone == 'UTC') :
			return $date->format('Y-m-d\TH:i:s\Z'); // Updated ISO8601
		else :
			return $date->format(\DateTime::ATOM); // Updated ISO8601
		endif;

		return $date->format(\DateTime::ATOM);
	}

	/**
	 *
	 * Get the date in ISO 8601 standard.
	 * Also known as ATOM date
	 *
	 * @param mixed $datetime input date
	 * @param string|bool $timezone destination TimeZone
	 * @return	string formatted date in ISO 8601 standard
	 * E.g. 2022-12-01T13:47:24+01:00
	 */
	public static function ATOM($datetime = 'now', $timezone = 'Europe/Amsterdam'): string
	{
		$date = self::GetDate($datetime);
		if ($timezone !== false) :
			$date->setTimezone(new \DateTimeZone($timezone));
		endif;

		return $date->format(\DateTime::ATOM); // Updated ISO8601

	}

	/**
	 *
	 * Get the date in RFC 5545 standard.
	 * Best for iCal (.ics) files
	 *
	 * @param mixed $datetime input date
	 * @param string|DateTimeZone|bool $timezone destination TimeZone
	 * @return	string formatted date in ISO 8601 standard
	 * E.g. 20221201T134724
	 */
	public static function RFC5545($datetime = 'now', string|\DateTimeZone|bool $timezone = false): string
	{
		$date = self::GetDate($datetime);
		if (($timezone instanceof \DateTimeZone) === false) :
			if ($timezone === false || empty($timezone)) :
				$timezone = date_default_timezone_get();
			endif;
			if (is_string($timezone)) :
				$timezone = new \DateTimeZone($timezone);
			endif;
		endif;

		$date->setTimezone($timezone);
		return $date->format('Ymd\THis');
	}

	/**
	 * Get the duration in ISO 8601 standard.
	 *
	 * @param mixed $duration
	 * @return	string P1Y2M10DT2H30M
	 */
	public static function ISO8601Duration($duration): string
	{
		$duration = new \Khill\Duration\Duration($duration);
		if ($duration->days > 0) :
			return sprintf('P0Y0M%dDT%dH%dM%dS', round($duration->days), round($duration->hours), round($duration->minutes), round($duration->seconds));
		elseif ($duration->hours > 0) :
			return sprintf('PT%dH%dM%dS', round($duration->hours), round($duration->minutes), round($duration->seconds));
		elseif ($duration->minutes > 0) :
			return sprintf('PT%dM%dS', round($duration->minutes), round($duration->seconds));
		else :
			return sprintf('PT%dS', round($duration->seconds));
		endif;
	}

	/**
	 * IsoDate
	 *
	 * @param	mixed $datetime
	 * @return	string 20161220T094200
	 */
	public static function IsoDate($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('Ymd') . 'T' . $datetime->format('His');
	}

	/**
	 * Get Time Ago
	 *
	 * @param mixed $datetime
	 * @return	string
	 */
	public static function TimeAgo($datetime): string
	{
		$datetime = self::GetDate($datetime);
		$interval = date_create('now')->diff($datetime);
		$suffix = ($interval->invert ? ' geleden' : '');
		if ($v = $interval->y >= 1) return KerkEnIT\Format::pluralize($interval->y, ' jaar', ' jaren') . $suffix;
		if ($v = $interval->m >= 1) return KerkEnIT\Format::pluralize($interval->m, ' maand', ' maanden') . $suffix;
		if ($v = $interval->d >= 1) return KerkEnIT\Format::pluralize($interval->d, ' dag', ' dagen') . $suffix;
		if ($v = $interval->h >= 1) return KerkEnIT\Format::pluralize($interval->h, ' uur', ' uren') . $suffix;
		if ($v = $interval->i >= 1) return KerkEnIT\Format::pluralize($interval->i, ' minuut', ' minuten') . $suffix;
		return ($interval->s < 5 ? 'zojuist' : ' seconden geleden');
	}

	/**
	 * Get the age in years
	 *
	 * @param mixed $datetime
	 * @return	int
	 */
	public static function Age($datetime): int
	{
		$datetime = self::GetDate($datetime);
		$now = new \DateTime();
		$interval = $datetime->diff($now);
		return $interval->y;
	}

	/**
	 * Get's a list with the full names of all months
	 *
	 * @return	array
	 */
	private static function months_full(): array
	{
		return array("januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december");
	}

	/**
	 * Get's a list with the short names of all months
	 *
	 * @return	array
	 */
	private static function months_short(): array
	{
		return array("jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec");
	}

	/**
	 * Get's a list with the full names of the days of the week
	 *
	 * @return	array
	 */
	private static function days_full(): array
	{
		return array("zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag");
	}

	/**
	 * Get's a list with the short names of the days of the week
	 *
	 * @return	array
	 */
	private static function days_short(): array
	{
		return array("zo", "ma", "di", "wo", "do", "vr", "za");
	}

	/**
	 * Get name of the part of the day
	 *
	 * @param	int $hour
	 * @return	string morgen
	 */
	public static function days_part($hour): string
	{
		if ($hour < 6) :
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
	 * @param	int $hour
	 * @return	string ochtend
	 */
	public static function days_part2($hour): string
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
	 * @param	bool $plural
	 * @return	array
	 */
	private static function period($plural = false): array
	{
		if (!$plural) {
			return array("seconde", "minuut", "uur", "dag", "week", "maand", "jaar", "decennium");
		} else {
			return array("seconden", "minuten", "uren", "dagen", "weken", "maanden", "jaren", "decennia");
		}
	}

	/**
	 * Get the text at
	 *
	 * @return	string
	 */
	private static function at(): string
	{
		return 'om';
	}

	/**
	 * Get the text from
	 *
	 * @return	string
	 */
	private static function from(): string
	{
		return 'van';
	}

	/**
	 * Get the text to
	 *
	 * @return	string
	 */
	private static function to(): string
	{
		return 'tot';
	}

	/**
	 * Get the text to
	 *
	 * @return	string
	 */
	private static function till(): string
	{
		return 't/m';
	}

	/**
	 * Format in a long date format maandag 20 december 2010
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december 2010
	 */
	public static function LongDate($datetime, $locale = NULL): string
	{
		$datetime = self::GetDate($datetime);
		if ($datetime instanceof \DateTime) :
			if ($locale !== NULL) :
				$dateFormatter = \IntlDateFormatter::create(
					$locale,
					\IntlDateFormatter::FULL,
					\IntlDateFormatter::NONE,
					date_default_timezone_get(),
					\IntlDateFormatter::GREGORIAN
				);
				return $dateFormatter->format($datetime);
			else :
				return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y');
			endif;
		endif;
		return '';
	}

	/**
	 * maandag 20 december 2010
	 *
	 * @param	object datetime
	 * @return	string maandag 20 dec. 2010
	 */
	public static function ShortMonthDate($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_short()[$datetime->format('m') - 1] . '.' . ' ' . $datetime->format('Y');
	}

	/**
	 * december 2010
	 *
	 * @param	object datetime
	 * @return	string december 2010
	 */
	public static function MonthAndYear($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y');
	}

	/**
	 * maandag 20 december
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december
	 */
	public static function DayDateMonth($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1];
	}

	/**
	 * maandag 20 december
	 *
	 * @param	object datetime
	 * @param string period
	 * @return	string maandag 20 december
	 */
	public static function DayDateMonthPeriod($datetime, $period = 'morgen'): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . $period . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1];
	}

	/**
	 * maandag
	 *
	 * @param	object datetime
	 * @return	string maandag
	 */
	public static function DayOfWeekName($value): string
	{
		if (!is_numeric($value) || $value > 10) :
			$datetime = self::GetDate($value);
			$value = (int)$datetime->format('w');
		else :
			$value = ((int)$value) - 1;
		endif;

		return self::days_full()[$value];
	}

	/**
	 * maandag 20 december 2010
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december 2010
	 */
	public static function FullDate($datetime): string
	{
		return self::LongDate($datetime);
	}

	/**
	 * 20 december 2010
	 *
	 * @param	object datetime
	 * @return	string 20 december 2010
	 */
	public static function DutchDate($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y');
	}

	/**
	 * maandag 20 december 2010 9:42
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december 2010 9:42
	 */
	public static function FullDateTime($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y G:i');
	}

	/**
	 * ma 20 dec 2010 9:42
	 *
	 * @param	object datetime
	 * @return	string ma 20 dec 2010 9:42
	 */
	public static function ShortDateAndTime($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return str_replace(' 0:00', '', self::days_short()[$datetime->format('w')] . ' ' . $datetime->format('d') . ' ' . self::months_short()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y G:i'));
	}

	/**
	 * 20 dec
	 *
	 * @param	object datetime
	 * @return	string 20 dec
	 */
	public static function ShortDateAndMonth($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('d') . ' ' . self::months_short()[$datetime->format('m') - 1];
	}

	/**
	 * 20 december
	 *
	 * @param	object datetime
	 * @return	string 20 december
	 */
	public static function ShortDateAndFullMonth($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('d') . ' ' . self::months_full()[$datetime->format('m') - 1];
	}

	/**
	 * 9:42
	 *
	 * @param	object datetime
	 * @return	string 9:42
	 */
	public static function ShortTime($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('G:i');
	}

	/**
	 * 9.42
	 *
	 * @param	object datetime
	 * @return	string 9.42
	 */
	public static function DutchShortTime($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('G.i');
	}

	/**
	 * 09:42
	 *
	 * @param	object datetime
	 * @return	string 09:42
	 */
	public static function Time($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return $datetime->format('H:i');
	}

	/**
	 * ma 20 december 2010
	 *
	 * @param	object datetime
	 * @return	string ma 20 december 2010
	 */
	public static function ShortDate($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_short()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y');
	}

	/**
	 * maandag 20 december 2010 om 9:42
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december 2010 om 9:42
	 */
	public static function FullDateAt($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('Y') . ' ' . self::at() . ' ' . $datetime->format('G:i');
	}

	/**
	 * maandag 20 december 2010 om 9:42
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december 2010 om 9:42
	 */
	public static function FullDateAtNullTime($datetime): string
	{
		return str_replace(' om 0:00', '', self::FullDateAt($datetime));
	}

	/**
	 * maandag 20 december om 9:42
	 *
	 * @param	object datetime
	 * @return	string maandag 20 december om 9:42
	 */
	public static function FullDateWithoutYearAt($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_full()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . self::at() . ' ' . $datetime->format('G:i');
	}

	/**
	 * maandag 20 december om 9:42 tot 10:42
	 *
	 * @param	object datetime
	 * @param	object datetime
	 * @return	string maandag 20 december om 9:42 tot 10:42
	 */
	public static function FullDateWithoutYearFromTill($from, $to = null): string
	{
		$from = self::GetDate($from);
		$to = self::GetDate($to);
		if ($to !== null && $from->format('Y-m-d') == $to->format('Y-m-d')) :
			return self::days_full()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_full()[$from->format('m') - 1] . ' ' . self::from() . ' ' . $from->format('G:i') . ' ' . self::to() . ' ' . $to->format('G:i');
		elseif ($to !== null && $from->format('Y-m-d') != $to->format('Y-m-d')) :
			return self::days_full()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_full()[$from->format('m') - 1] . ' ' . self::at() . ' ' . $from->format('G:i') . ' ' . self::to() . ' ' . self::days_full()[$to->format('w')] . ' ' . $to->format('j') . ' ' . self::months_full()[$to->format('m') - 1] . ' ' . self::at() . ' ' . $to->format('G:i');
		else :
			return self::days_full()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_full()[$from->format('m') - 1] . ' ' . self::from() . ' ' . $from->format('G:i');
		endif;
	}



	/**
	 * ma 20 dec om 9:42 tot 10:42
	 *
	 * @param	object datetime
	 * @param	object datetime
	 * @return	string ma 20 dec om 9:42 tot 10:42
	 */
	public static function ShortDateWithoutYearFromTill($from, $to = null): string
	{
		$from = self::GetDate($from);
		$to = self::GetDate($to);
		if ($to !== null && $from->format('Y-m-d') == $to->format('Y-m-d')) :
			return self::days_short()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_short()[$from->format('m') - 1] . ' ' . self::from() . ' ' . $from->format('G:i') . ' ' . self::till() . ' ' . $to->format('G:i');
		elseif ($to !== null && $from->format('Y-m-d') != $to->format('Y-m-d')) :
			return self::days_short()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_short()[$from->format('m') - 1] . '. ' . $from->format('G:i') . ' ' . self::till() . ' ' . self::days_short()[$to->format('w')] . ' ' . $to->format('j') . ' ' . self::months_short()[$to->format('m') - 1] . '. ' . $to->format('G:i');
		else :
			return self::days_short()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_short()[$from->format('m') - 1] . ' ' . self::from() . ' ' . $from->format('G:i');
		endif;
	}


	/**
	 * ma 20 dec om 9:42 tot 10:42
	 *
	 * @param	object datetime
	 * @param	object datetime
	 * @return	string ma 20 dec om 9:42 tot 10:42
	 */
	public static function ShortDayWithoutYearFromTill($from, $to = null): string
	{
		$from = self::GetDate($from);
		$to = self::GetDate($to);
		if ($to !== null && $from->format('Y-m-d') == $to->format('Y-m-d')) :
			return self::days_short()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_short()[$from->format('m') - 1] . ' ' . self::from() . ' ' . $from->format('G:i') . ' ' . self::till() . ' ' . $to->format('G:i');
		elseif ($to !== null && $from->format('Y-m-d') != $to->format('Y-m-d')) :
			return self::days_short()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_short()[$from->format('m') - 1] . '. ' . self::till() . ' ' . self::days_short()[$to->format('w')] . ' ' . $to->format('j') . ' ' . self::months_short()[$to->format('m') - 1] . '.';
		else :
			return self::days_short()[$from->format('w')] . ' ' . $from->format('j') . ' ' . self::months_short()[$from->format('m') - 1] . ' ' . self::from() . ' ' . $from->format('G:i');
		endif;
	}

	/**
	 * ma 20-12 9:42
	 *
	 * @param	object datetime
	 * @return	string ma 20-12 9:42
	 */
	public static function ShortDateTimeMonth($datetime): string
	{
		$datetime = self::GetDate($datetime);

		return self::days_short()[$datetime->format('w')] . ' ' . $datetime->format('j') . ' ' . self::months_full()[$datetime->format('m') - 1] . ' ' . $datetime->format('G:i');
	}

	/**
	 * ma 20-12-2015 9:42
	 *
	 * @param	object datetime
	 * @return	string ma 20-12-2015 9:42
	 */
	public static function ShortDateTime($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_short()[$datetime->format('w')] . ' ' . $datetime->format('d-m-Y G:i');
	}

	/**
	 * ma 20-12-2015 09:42
	 *
	 * @param	object datetime
	 * @return	string ma 20-12-2015 09:42
	 */
	public static function ShortDateTimeHour($datetime): string
	{
		$datetime = self::GetDate($datetime);
		return self::days_short()[$datetime->format('w')] . ' ' . $datetime->format('d-m-Y H:i');
	}



	/**
	 * Calculates the Easter date for a given year
	 *
	 * @param	int $y
	 * @return \DateTime
	 */
	public static function easter_date($y)
	{
		$firstdig1 = array(21, 24, 25, 27, 28, 29, 30, 31, 32, 34, 35, 38);
		$firstdig2 = array(33, 36, 37, 39, 40);

		$firstdig = \KerkEnIT\Math::idiv($y, 100);
		$remain19 = $y % 19;

		$temp = \KerkEnIT\Math::idiv($firstdig - 15, 2) + 202 - 11 * $remain19;

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
		$td = ($temp + \KerkEnIT\Math::idiv($temp, 4)) % 7;

		$te = ((20 - $tb - $tc - $td) % 7) + 1;
		$d = $ta + $te;

		if ($d > 31) {
			$d = $d - 31;
			$m = 4;
		} else {
			$m = 3;
		}
		return new \DateTime("$y-$m-$d", new \DateTimeZone('Europe/Amsterdam'));
	}

	public static function FeastDate_YN($date)
	{
		$datetime = self::GetDate($date);
		if ($datetime->format('N') > 5) :
			return true;
		endif;
		$feasts = array('08-12', '25-12', '01-01', '06-01', '19-03', '25-03', '24-06', '29-06', '15-08', '01-11', '07-11');
		if (in_array($datetime->format('d-m'), $feasts)) :
			return true;
		endif;

		$easter = self::easter_date($datetime->format('Y'));
		$specialDates = array($easter->add(new \DateInterval('P39D'))->format('Y-m-d'), $easter->add(new \DateInterval('P68D'))->format('Y-m-d'));
		if (in_array($datetime->format('Y-m-d'), $specialDates)) :
			return true;
		endif;

		return false;
	}



	/**
	 * Get Duration text
	 *
	 * @param int $minutes Minutes to add or subtract from the time of now
	 * @return	string
	 */
	public static function Duration($minutes)
	{
		$datetime = date_create('today');
		if ($minutes >= 0) :

			$datetime->add(new \DateInterval('PT' . ((int) $minutes) . 'M'));
		else :
			$datetime->sub(new \DateInterval('PT' . (abs((int) $minutes)) . 'M'));
		endif;
		$interval = date_create('today')->diff($datetime);

		$text = '';
		if ($v = $interval->y >= 1) :
			$text .= KerkEnIT\Format::pluralize($interval->y, ' jaar ', ' jaren ');
		endif;
		if ($v = $interval->m >= 1) :
			$text .= KerkEnIT\Format::pluralize($interval->m, ' maand ', ' maanden ');
		endif;

		if ($v = $interval->d >= 1) :
			$text .= Format::pluralize($interval->d, ' dag ', ' dagen ');
		endif;

		if ($v = $interval->h >= 1) :
			$text .= Format::pluralize($interval->h, ' uur ', ' uren ');
		endif;

		if ($v = $interval->i >= 1) :
			$text .= Format::pluralize($interval->i, ' minuut ', ' minuten ');
		endif;

		if ($v = $interval->s >= 1) :
			$text .= Format::pluralize($interval->i, ' seconde ', ' seconden ');
		endif;

		if (empty($text)) :
			return 'Geen';
		endif;
		return $text;
	}



	/**
	 * Gets the time from now in
	 *
	 * @param	string $date
	 * @return	string
	 */
	public static function Ago($date)
	{
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
	 * @return	string 20-12-2016 09:42:00
	 */
	public static function DateTimePicker($datetime)
	{
		return self::GetDate($datetime)->format('d-m-Y H:i:s');
	}

	/**
	 * Gets the date for a date picker
	 *
	 * @param  mixed $datetime
	 * @param	bool $nullable Default false
	 * @return	string 2016-12-20
	 */
	public static function DatePicker($datetime, $nullable = false)
	{
		$format = 'Y-m-d';
		$detect = new \Mobile_Detect;
		$user_agent = $detect->getUserAgent();
		if ($detect->is('WebKit') || $detect->isiOS()) :
			$format = 'Y-m-d';
		endif;
		if (empty($datetime) && $nullable) :
			return '';
		else :
			return (new \DateTime($datetime))->format($format);
		endif;
	}

	/**
	 * Gets the date for a time picker
	 *
	 * @param  mixed $datetime
	 * @param	bool $nullable Default false
	 * @return	string 09:42
	 */
	public static function TimePicker($datetime, $nullable = false)
	{
		$format = 'H:i';
		/*
		$detect = new \Mobile_Detect;
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
			return (new \DateTime($datetime))->format($format);
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
	 * @param	int $days
	 * @param	string $format Output format.
	 * @return	string
	 */
	public static function DateTimeAdd($datetime, $days, $format = 'd-m-Y H:i:s')
	{
		$invert = 0;
		$datetime = self::GetDate($datetime);
		if (is_numeric($days)) :
			if ($days < 0) :
				$invert = 1;
			endif;
			$days = 'P' . abs($days) . 'D';
		endif;
		$interval = new \DateInterval($days);
		$interval->invert = $invert;
		$datetime->add($interval);
		return $datetime->format($format);
	}

	/**
	 * Get a logical structure of a date.
	 *
	 * @param  mixed $datetime
	 * @param	string $type
	 * @return object $logic
	 */
	public static function GetDateTimeLogic($datetime, $type)
	{
		$datetime = self::GetDate($datetime);
		$logic = array();
		$logic['type'] = $type;
		if ($type == 'week') :
			$logic['day'] = $datetime->format('l');
			$logic['dayname'] = self::DayOfWeekName($datetime);
		elseif ($type == 'month') :
			$logic['month'] = $datetime->format('F');
		endif;
		$logic['interval'] = ((int)$datetime->format('j'));
		if ($logic['interval'] > 0 && $logic['interval'] <= 7) :
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
		return (object)$logic;
	}

	/**
	 * Get a logical structure of a date.
	 *
	 * @param  mixed $datetime
	 * @param  object $logic Logical Date object
	 * @return \DateTime
	 */
	public static function AddDateTimeLogic($datetime, $logic)
	{
		$datetime = self::GetDate($datetime);
		if ($logic->type == 'week') :
			return (new \DateTime($logic->interval . ' ' . $logic->day . ' of ' . $datetime->format('F Y')));
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
		return self::GetDate($datetime)->format('Y-m-d') == (new \DateTime('now'))->format('Y-m-d');
	}

	/**
	 * Check if given date is within this this week
	 *
	 * @param  mixed $datetime
	 * @return bool
	 */
	public static function isWithinNextWeek($datetime)
	{
		return self::isWithinXDays($datetime, 6);
	}

	/**
	 * Check if given date is within the given days in ```$x```
	 *
	 * @param  mixed $datetime
	 * @param	int|float $x Add days to datetime. When negative it subtract the amount of days.
	 * @return bool
	 */
	public static function isWithinXDays($datetime, $x)
	{
		$currentDate = self::GetDate($datetime);
		$today = self::GetDate('today');
		$nextDate = self::GetDate('today');
		if ($x > 0) :
			$nextDate->add(new \DateInterval('P' . $x . 'D'));
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
	 * @param  mixed $datetime \DateTime to compare.
	 * @param  mixed $begin \DateTime ot the beginning.
	 * @param  mixed $end \DateTime of the end.
	 * @param	int|float $x Add days to datetime. When negative it subtract the amount of days.
	 * @return bool
	 */
	public static function isBetween($datetime, $begin, $end, $x = 0)
	{
		$currentDate = self::GetDate($datetime);
		$begin = self::GetDate($begin);
		$end = self::GetDate($end);
		if ($currentDate >= $begin && $currentDate <= $end) :
			return TRUE;
		endif;

		return FALSE;
	}

	/**
	 * jsDateTime
	 * @deprecated in 1.3.107 @see \KerkEnIT\DateTime::ISO8601()
	 *
	 * @param  mixed $datetime
	 * @return	string
	 */
	public static function jsDateTime($datetime)
	{
		return self::ISO8601($datetime);
	}

	/**
	 * Gets a order by based on the TimeStamp
	 *
	 * @param  mixed $datetime
	 * @param	int $order
	 * @return int 2016355094200
	 */
	public static function TimeStampOrder($datetime, $order)
	{
		return (int)self::GetDate($datetime)->format('yzHi') . str_pad($order, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Get The Unix Timestamp from a datetime object
	 *
	 * @param	object datetime
	 * @return int 1450600974
	 */
	public static function TimeStamp($datetime) :int
	{
		return self::GetDate($datetime)->getTimeStamp();
	}


	/**
	 * Added minutes to the start time to calculate the end time
	 *
	 * @param  mixed $datetime
	 * @param	int $duration
	 * @return	string
	 */
	public static function jsEndDateTime($datetime, $duration)
	{
		if (empty($duration)) {
			$duration = 60;
		}
		$DateTime = self::GetDate($datetime);
		$DateTime->add(new \DateInterval('PT' . $duration . 'M'));
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
		$datetimeObj1 = new \DateTime($date);
		$datetimeObj2 = new \DateTime();
		$interval = $datetimeObj1->diff($datetimeObj2);
		$dateDiff = $interval->format('%R%a');

		return $dateDiff > 0;
	}

	/**
	 * Get the SQL DateTime
	 *
	 * @param  mixed $datetime
	 * @return	string 2016-12-20 09:42:00
	 */
	public static function sqlDateTime($datetime)
	{
		return self::GetDate($datetime)->format('Y-m-d H:i:s');
	}

	/**
	 * Get the SQL Date
	 *
	 * @param  mixed $datetime
	 * @return	string 2016-12-20
	 */
	public static function sqlDate($datetime)
	{
		return self::GetDate($datetime)->format('Y-m-d');
	}

	/**
	 * Get the SQL Time
	 *
	 * @param  mixed $datetime
	 * @return	string 09:42:00
	 */
	public static function sqlTime($datetime)
	{
		return self::GetDate($datetime)->format('H:i:s');
	}
}
