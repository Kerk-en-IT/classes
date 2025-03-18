<?php
namespace KerkEnIT;

/**
 * Math Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Math
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2024-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.1.0
 **/
class Math
{

	/**
	 * calculate Median of array
	 *
	 * @param  mixed $array
	 * @return float
	 */
	public static function calculateMedian(array $array): float
	{
		if (!$array) {
			throw new LengthException('Cannot calculate median because Argument #1 ($array) is empty');
		}
		sort($array);
		$middleIndex = count($array) / 2;
		if (is_float($middleIndex)) {
			return $array[(int) $middleIndex];
		}
		return ($array[$middleIndex] + $array[$middleIndex - 1]) / 2;
	}


	/**
	 * calculate Average of array
	 *
	 * @param  mixed $array
	 * @return float
	 */
	public static function calculateAverage(array $array): float
	{
		if (!$array) {
			throw new LengthException('Cannot calculate median because Argument #1 ($array) is empty');
		}
		return array_sum($array) / count($array);
	}

	/**
	 * Round Up To Any
	 *
	 * @param  int:float $n
	 * @param  int $x
	 * @return int
	 */
	public static function roundUpToAny($n, $x = 5): int
	{
		return (int)(floor($n) % $x === 0) ? floor($n) : floor(($n + $x / 2) / $x) * $x;
	}

	public static function FractionToDecimal($fraction): string
	{
		$numbers =  array_map('floatval', explode("/", $fraction));
		if ($numbers[1] < 1) :
			return $fraction;
		else :
			return str_replace('.00', '', number_format(($numbers[0] / $numbers[1]), 2, '.', ''));
		endif;
	}

	public static function idiv($a, $b)
	{
		return floor($a / $b);
	}
}

?>