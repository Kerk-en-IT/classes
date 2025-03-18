<?php

namespace KerkEnIT;

/**
 * ColorPalette Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage ColorPalette
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2024-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.1.0
 **/
class ColorPalette
{
	public $color;

	/**
	 * __construct
	 *
	 * @param  mixed $color
	 * @return void
	 */
	public function __construct($color)
	{
		$this->color = $color;
	}


	/**
	 * Modify Color
	 *
	 * @param  string $hex Hex color code
	 * @param  int $diff (positive or negative) Min 0, Max 255
	 * @return string Modified color code
	 */
	public function color_mod(string $hex, int $diff): string
	{
		$rgb = str_split(trim($hex, '# '), 2);

		foreach ($rgb as &$hex) {
			$dec = hexdec($hex);
			if ($diff >= 0) {
				$dec += $diff;
			} else {
				$dec -= abs($diff);
			}
			$dec = max(0, min(255, $dec));
			$hex = str_pad(dechex($dec), 2, '0', STR_PAD_LEFT);
		}
		return '#' . implode($rgb);
	}

	/**
	 * Create Color Palette
	 *
	 * @param  int $colorCount Number of colors in palette
	 * @return array Color palette
	 */
	public function createPalette($colorCount = 5): array
	{
		$newColor = '';
		$colorPalette = array();
		for ($i = 1; $i <= $colorCount; $i++) {
			if ($i == 1) {
				$color = $this->color;
				$colorVariation = - (($i * 4) * 15);
			}
			if ($i == 2) {
				$color = $this->color;
				$colorVariation = - (($i * 2) * 15);
			}
			if ($i == 3) {
				$color = $this->color;
				$colorVariation = 0;
			}
			if ($i == 4) {
				$color = $this->color;
				$colorVariation = + (($i * 2) * 15);
			}
			if ($i == 5) {
				$color = $this->color;
				$colorVariation = + (($i * 3) * 15);
			}

			$newColor = $this->color_mod($color, $colorVariation);

			array_push($colorPalette, $newColor);
		}
		return $colorPalette;
	}
}
