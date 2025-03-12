<?php
namespace KerkEnIT;

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