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
	 * @param	string $hex Hex color code
	 * @param	int $diff (positive or negative) Min 0, Max 255
	 * @return	string Modified color code
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
	 * @param	int $colorCount Number of colors in palette
	 * @return	array Color palette
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


	/**
	 * Get the contrast color of a given HEX color
	 *
	 * @param	string $hexColor The HEX color
	 * @return	string $contrastColor The contrast color of the given HEX color
	 */
	public static function get_contrast_color(string $hexColor): string
	{
		// hexColor RGB
		$R1 = hexdec(substr($hexColor, 1, 2));
		$G1 = hexdec(substr($hexColor, 3, 2));
		$B1 = hexdec(substr($hexColor, 5, 2));

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
		if ($L1 > $L2) :
			$contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
		else :
			$contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
		endif;


		// If contrast is more than 5, return black color
		if ($contrastRatio > 3) :
			return '#000000';
		else :
			// if not, return white color.
			return '#FFFFFF';
		endif;
	}


	/**
	 * Converts HEX color to RGB
	 *
	 * @param	string $hexColor
	 * @return object $rgb The RGB object
	 */
	public static function hex_to_rgb(string $hexColor)
	{
		$hexColor = ltrim($hexColor, '#');
		list($r, $g, $b) = array((float)hexdec(substr($hexColor, 0, 2)), (float)hexdec(substr($hexColor, 2, 2)), (float)hexdec(substr($hexColor, 4, 2)));

		return (object)array('r' => $r, 'g' => $g, 'b' => $b, 'avg' => ($r + $g + $b) / 3.00);
	}
}
