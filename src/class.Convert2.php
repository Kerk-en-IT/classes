<?php
namespace KerkEnIT;

if(!defined('JPG_QUALITY')) :
	define('JPG_QUALITY', 85);
endif;
if(!defined('WEBP_QUALITY')) :
	define('WEBP_QUALITY', 90);
endif;
if(!defined('AVIF_QUALITY')) :
	define('AVIF_QUALITY', 95);
endif;
if(!defined('MAX_WIDTH')) :
	define('MAX_WIDTH', 0);
endif;
if(!defined('MAX_HEIGHT')) :
	define('MAX_HEIGHT', 3840);
endif;
if(!defined('MY_MAGICK_MEMORY_LIMIT')) :
	define('MY_MAGICK_MEMORY_LIMIT', 2147483648);
endif;
if (!defined('DEBUG')) :
	define('DEBUG', PHP_OS == 'Darwin' || isset($_SERVER) && isset($_SERVER["argv"]) && is_array($_SERVER["argv"]) && isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "0.0.0");
	if (!defined('DEBUG')) :
		define('DEBUG', false);
	endif;
endif;
if (!defined('REGENERATE')) :
	if(DEBUG) :
		define('REGENERATE', false);
	else :
		define('REGENERATE', false);
	endif;
endif;
// maximum amount of memory map to allocate for the pixel cache
if (class_exists('\Imagick')) :
	\Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
	\Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
	\Imagick::setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
	\Imagick::setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
	\Imagick::setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
	\Imagick::setResourceLimit(\Imagick::RESOURCETYPE_THREAD, -1);
endif;

/**
 * Convert2 Class File for Kerk en IT Framework
 *
 * Formatting various objects into the expected output.
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Convert2
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2022-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/
class Convert2
{

	public static function watermarks(&$img, $input_file)
	{
		if(realpath(\get_include_path() . "/watermark.png") !== false) :
			$watermark = new \Imagick();
			$watermark->readImage(realpath(\get_include_path() . "/watermark.png"));

			$width = $img->getImageWidth();
			$height = $img->getImageHeight();

			if ($width > $height) :
				//Landscape
				$watermark->resizeimage(
					$width / 6.00,
					$width / 6.00,
					\Imagick::FILTER_LANCZOS,
					1,
					true
				);
			else :
				//Portrait
				$watermark->resizeimage(
					$height / 6.00,
					$height / 6.00,
					\Imagick::FILTER_LANCZOS,
					1,
					true
				);
			endif;
			$x = (int)round($width - $watermark->getImageWidth());
			$y = (int)round($height - $watermark->getImageHeight());

			$avg_color = ColorPalette::get_average_color($input_file, true, (int)$watermark->getImageWidth(), (int)$watermark->getImageHeight(), (int)$x, (int)$y);
			$rgb = ColorPalette::hex_to_rgb($avg_color);

			$composite = \Imagick::COMPOSITE_LIGHTEN;

			$watermark = new \Imagick();
			if($rgb->avg < 96 && $rgb->g > 64 && $rgb->b < 64 && $rgb->r < 64) :
				$png = \get_include_path() . "/watermark_dark.png";
			elseif ($rgb->avg > 96 && $rgb->g > 64 && $rgb->b < 64 && $rgb->r < 64) :
				$png = \get_include_path() . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg < 128 && $rgb->g > 64 && $rgb->b < 128 && $rgb->b > 64 && $rgb->r >128 && $rgb->r < 144) :
				$png = \get_include_path() . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg < 128 && $rgb->g > 128 && $rgb->b < 128 && $rgb->r > 96) :
				$png = \get_include_path() . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg > 128 && $rgb->avg < 196 && $rgb->g >128 && $rgb->g < 196 && $rgb->b < 128 && $rgb->b > 96 && $rgb->r >128 && $rgb->r < 144) :
				$png = \get_include_path() . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg < 20 && $rgb->g < 25 && $rgb->b < 25 && $rgb->r < 25) :
				$png = \get_include_path() . "/watermark_dark.png";
			elseif ($rgb->avg > 235 && $rgb->g > 230 && $rgb->b > 230 && $rgb->r > 230) :
				$png = \get_include_path() . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			else :
				$png = \get_include_path() . "/watermark.png";
				$composite = \Imagick::COMPOSITE_OVER;
			endif;
			$watermark->readImage($png);
			if ($width > $height) :
				//Landscape
				$watermark->resizeimage(
					$width / 6.00,
					$width / 6.00,
					\Imagick::FILTER_LANCZOS,
					1,
					true
				);
			else :
				//Portrait
				$watermark->resizeimage(
					$height / 6.00,
					$height / 6.00,
					\Imagick::FILTER_LANCZOS,
					1,
					true
				);
			endif;
			$img->compositeImage($watermark, $composite, $x, $y);
			return $png;
		else :
			return $img;
		endif;
	}
	/**
	 *
	 * Convert image to JPG
	 *
	 * @param string $input_file Source image
	 * @param string $output_file  Destination image
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function jpg($input_file, $output_file)
	{
		// check if file exists
		if (!file_exists($input_file)) :
			return false;
		endif;
		if (!REGENERATE && file_exists($output_file)) :
			return $output_file;
		endif;
		if((strpos($input_file, 'cover') !== false || strpos($output_file, 'images/201') !== false)) :
			if (file_exists($output_file)) :
				return $output_file;
			endif;
		endif;

		if (class_exists('\Imagick')) :
			$img = new \Imagick();
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
			$img->readImage($input_file);
			if ($img->getImageHeight() > MAX_HEIGHT) :
				$img->scaleImage(MAX_WIDTH, MAX_HEIGHT);
			endif;
			$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
			$img->setImageCompressionQuality(JPG_QUALITY);
			$img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
			if ((strpos($input_file, 'cover') === false && strpos($output_file, 'images/199') === false && strpos($output_file, 'images/200') === false && strpos($output_file, 'images/201') === false)) :
				self::watermarks($img, $input_file);
			endif;
			//$img->resampleImage(72, 72, \Imagick::FILTER_SINC, 1);
			$img->setImageResolution(72, 72);
			$img->setFormat("jpg");
			//$img->stripImage();
			if($img->writeImage($output_file)) :
				$img->clear();
				return $output_file;
			endif;
		else :
			if(copy($input_file, $output_file)) :
				// Return the output file
				if(file_exists($output_file)) :
					return $output_file;
				else :
					// Return false if the file does not exist
					return false;
				endif;
			endif;
		endif;
		// Return false if the function has not returned a value
		return false;
	}

	/**
	 *
	 * Convert image to WebP
	 *
	 * @param string $input_file Source image
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function webp($input_file)
	{
		// check if file exists
		if (!file_exists($input_file)) :
			return false;
		endif;
		// get the file type
		$file_type = exif_imagetype($input_file);
		//https://www.php.net/manual/en/function.exif-imagetype.php
		//exif_imagetype($input_file);
		// 1    IMAGETYPE_GIF
		// 2    IMAGETYPE_JPEG
		// 3    IMAGETYPE_PNG
		// 6    IMAGETYPE_BMP
		// 15   IMAGETYPE_WBMP
		// 16   IMAGETYPE_XBM
		$output_file =  self::replace_extension($input_file, 'webp');
		if (!REGENERATE && file_exists($output_file)) :
			return $output_file;
		endif;

		// check if the file is a cover image
		if ((strpos($input_file, 'cover') !== false || strpos($output_file, 'images/201') !== false)) :
			if (file_exists($output_file)) :
				return $output_file;
			endif;
		endif;

		if (class_exists('\Imagick')) :
			$img = new \Imagick();
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
			$img->readImage($input_file);
			if ($img->getImageHeight() > MAX_HEIGHT) :
				$img->scaleImage(MAX_WIDTH, MAX_HEIGHT);
			endif;
			if ((strpos($input_file, 'cover') === false && strpos($output_file, 'images/199') === false && strpos($output_file, 'images/200') === false && strpos($output_file, 'images/201') === false)) :
				self::watermarks($img, $input_file);
			endif;
			if ($file_type === IMAGETYPE_JPEG || $file_type === IMAGETYPE_PNG) {
				$img->setImageFormat('webp');
				$img->setImageCompressionQuality(WEBP_QUALITY);
				$img->setOption('webp:lossless', 'false');
			}
			if ($img->writeImage($output_file)) :
				$img->clear();
				return $output_file;
			endif;
		elseif (function_exists('imagewebp')) :
			switch ($file_type) {
				case IMAGETYPE_GIF:
					$image = imagecreatefromgif($input_file);
					break;
				case IMAGETYPE_JPEG:
					$image = imagecreatefromjpeg($input_file);
					break;
				case IMAGETYPE_PNG:
					$image = imagecreatefrompng($input_file);
					imagepalettetotruecolor($image);
					imagealphablending($image, true);
					imagesavealpha($image, true);
					break;
				case IMAGETYPE_BMP:
					$image = imagecreatefrombmp($input_file);
					break;
				case IMAGETYPE_WEBP:
					$image = imagecreatefromwebp($input_file);
					break;
				case IMAGETYPE_XBM:
					$image = imagecreatefromxbm($input_file);
					break;
				default:
					return false;
			}

			// Save the image
			$result = imagewebp($image, $output_file, WEBP_QUALITY);
			if (false === $result) :
				return false;
			endif;

			// Free up memory
			imagedestroy($image);
			// Return the output file
			if (file_exists($output_file)) :
				return $output_file;
			else :
				// Return false if the file does not exist
				return false;
			endif;
		endif;

		// Return false if the function has not returned a value
		return false;
	}

	/**
	 *
	 * Convert image to AVIF
	 *
	 * @param string $input_file Source image
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function avif($input_file)
	{
		// check if file exists
		if (!file_exists($input_file)) :
			return false;
		endif;
		// get the file type
		$file_type = exif_imagetype($input_file);
		//https://www.php.net/manual/en/function.exif-imagetype.php
		//exif_imagetype($input_file);
		// 1    IMAGETYPE_GIF
		// 2    IMAGETYPE_JPEG
		// 3    IMAGETYPE_PNG
		// 6    IMAGETYPE_BMP
		// 15   IMAGETYPE_WBMP
		// 16   IMAGETYPE_XBM
		$output_file =  self::replace_extension($input_file, 'avif');
		if (!REGENERATE && file_exists($output_file)) :
			return $output_file;
		endif;

		// check if the file is a cover image
		if ((strpos($input_file, 'cover') !== false || strpos($output_file, 'images/201') !== false)) :
			if (file_exists($output_file)) :
				return $output_file;
			endif;
		endif;

		if (class_exists('\Imagick')) :
			$img = new \Imagick();
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
			$img->readImage($input_file);
			if ($img->getImageHeight() > MAX_HEIGHT) :
				$img->scaleImage(MAX_WIDTH, MAX_HEIGHT);
			endif;
			if ((strpos($input_file, 'cover') === false && strpos($output_file, 'images/199') === false && strpos($output_file, 'images/200') === false && strpos($output_file, 'images/201') === false)) :
				self::watermarks($img, $input_file);
			endif;
			try{
				if ($file_type === IMAGETYPE_JPEG || $file_type === IMAGETYPE_PNG) {
					$img->setImageFormat('avif');
					$img->setImageCompressionQuality(AVIF_QUALITY);
				}
				if ($img->writeImage($output_file)) :
					$img->clear();
				endif;
			} catch(Exception $e) {
				return false;
			} finally {
				if(file_exists($output_file)) :
					return $output_file;
				else  :
					return false;
				endif;
			}
		elseif (function_exists('imageavif')) :
			try {
				switch ($file_type) {
					case IMAGETYPE_GIF:
						$image = imagecreatefromgif($input_file);
						break;
					case IMAGETYPE_JPEG:
						$image = imagecreatefromjpeg($input_file);
						break;
					case IMAGETYPE_PNG:
						$image = imagecreatefrompng($input_file);
						imagepalettetotruecolor($image);
						imagealphablending($image, true);
						imagesavealpha($image, true);
						break;
					case IMAGETYPE_BMP:
						$image = imagecreatefrombmp($input_file);
						break;
					case IMAGETYPE_WEBP:
						$image = imagecreatefromwebp($input_file);
						break;
					case IMAGETYPE_XBM:
						$image = imagecreatefromxbm($input_file);
						break;
					default:
						return false;
				}

				// Save the image
				$result = imageavif($image, $output_file, AVIF_QUALITY);
				if (false === $result) {
					return false;
				}
				// Free up memory
				imagedestroy($image);

				// Return the output file
				if (file_exists($output_file)) :
					return $output_file;
				else :
					// Return false if the file does not exist
					return false;
				endif;
			} catch (Exception $e) {
				// Return false if an exception is thrown
				return false;
			}
		endif;
		// Return false if the function has not returned a value
		return false;
	}

	/**
	 *
	 * Convert image to JPG
	 *
	 * @param string|null $input_file Source image
	 * @param string $output_file  Destination image
	 * @param int $width Size in pixels what the expected width should be.
	 * @param int $height Size in pixels what the expected height should be. Default 0 for auto height
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function shrink(string|null $input_file, string $output_file, int $width, int $height = 0)
	{
		// check if file exists
		if ($input_file === null || !file_exists($input_file)) :
			return false;
		endif;
		$image_dir = pathinfo($output_file, PATHINFO_DIRNAME);
		if (!file_exists($image_dir)) :
			mkdir($image_dir, 0755, true);
		endif;
		if (!REGENERATE && file_exists($output_file)) :
			$size = getimagesize($output_file);
			if (true || $size[0] === $width) :
				return $output_file;
			endif;
		endif;

		if ((strpos($input_file, 'cover') !== false || strpos($output_file, 'images/201') !== false)) :
			if (file_exists($output_file)) :
				$size = getimagesize($output_file);
				if (true || $size[0] === $width) :
					return $output_file;
				endif;
			endif;
		endif;

		if (class_exists('\Imagick')) :
			$img = new \Imagick();
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
			$img->readImage($input_file);
			if ((strpos($input_file, 'cover') === false && strpos($output_file, 'images/199') === false && strpos($output_file, 'images/200') === false && strpos($output_file, 'images/201') === false)) :
				self::watermarks($img, $input_file);
			endif;
			switch (strtolower(pathinfo($output_file, PATHINFO_EXTENSION))):
				case 'webp':
					$img->setImageFormat('webp');
					$img->setImageCompressionQuality(WEBP_QUALITY);
					$img->setOption('webp:lossless', 'false');
					break;
				case 'jpeg':
				case 'jpg':
					$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
					$img->setImageCompressionQuality(JPG_QUALITY);
					$img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);

					$img->setImageResolution(72, 72);
					$img->setFormat("jpg");
					break;
				case 'avif':
					$img->setImageFormat('avif');
					$img->setImageCompressionQuality(AVIF_QUALITY);
					break;
			endswitch;
			if($height != 0) :
				if(round($width) > 0 && round($height) > 0) :
					$img->cropThumbnailImage($width, $height);
				endif;
			endif;
			if (round($width) > 0 && round($height) > 0) :
				$img->scaleImage($width, $height);
			endif;
			//$img->stripImage();
			if ($img->writeImage($output_file)) :
				$img->clear();
				return $output_file;
			endif;
		else :
			if (copy($input_file, $output_file)) :
				if(file_exists($output_file)) :
					// Return the output file
					return $output_file;
				else :
					// Return false if the file does not exist
					return false;
				endif;
			endif;
		endif;
		// Return false if the function has not returned a value
		return false;
	}

	/**
	 *
	 * Create thumbnail (JPG) from a image
	 *
	 * @param string $input_file Source image
	 * @param string $output_file  Destination image. Should be JPG
	 * @param int $width Size in pixels what the expected width should be.
	 * @param int $height  Size in pixels what the expected height should be.
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function thumbnail(string $input_file, string $output_file, int $width, int $height)
	{
		// check if file exists
		if (!file_exists($input_file)) :
			return false;
		endif;

		if (file_exists($output_file) && filesize($output_file) > 0) :
			$size = getimagesize($output_file);

			if(true || ($size == NULL) || ($size[0]+ $size[1] == 0) || ($size[0] === $width && $size[1] === $height)) :
				return $output_file;
			endif;
		endif;
		//instantiate the image magick class
		$img = new \Imagick();
		$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
		$img->readImage($input_file);
		if ($img->getImageHeight() > MAX_HEIGHT) :
			$img->scaleImage(MAX_WIDTH, MAX_HEIGHT);
		endif;

		//crop and resize the image
		$img->cropThumbnailImage($width, $height);
		switch(strtolower(pathinfo($output_file, PATHINFO_EXTENSION))) :
			case 'webp':
				$img->setImageFormat('webp');
				$img->setImageCompressionQuality(WEBP_QUALITY);
				$img->setOption('webp:lossless', 'true');
				break;
			case 'jpeg':
			case 'jpg':
				$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
				$img->setImageCompressionQuality(JPG_QUALITY);
				$img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);

				$img->setImageResolution(72, 72);
				$img->setFormat("jpg");
				break;
			case 'avif':
				try {
					$img->setImageFormat('avif');
					$img->setImageCompressionQuality(AVIF_QUALITY);
				} catch (Exception $e) {
				}
				break;
			break;
		endswitch;

		if($img->writeImage($output_file)) :
			$img->clear();
			if(file_exists($output_file)) :
				// Return the output file
				return $output_file;
			else :
				// Return false if the file does not exist
				return false;
			endif;
		endif;
		// Return false if the function has not returned a value
		return false;
	}

	/**
	 *
	 * Create social media (JPG) from a image
	 *
	 * @param string $input_file Source image
	 * @param string $output_file  Destination image. Should be JPG
	 * @param int $width Size in pixels what the expected width should be.
	 * @param int $height  Size in pixels what the expected height should be.
	 * @param int $max_bytes  Maximum of bytes. Default 300KB (307200 bytes)
	 * @param int $quality  Default Image Compression Quality. Default 80%
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function social(string $input_file, string $output_file, int $width, int $height, ?int $max_bytes = NULL, $quality = NULL)
	{
		// check if file exists
		if (!file_exists($input_file)) :
			return false;
		endif;

		if (file_exists($output_file) && filesize($output_file) > 0) :
			$size = getimagesize($output_file);

			if (true || ($size == NULL) || ($size[0] + $size[1] == 0) || ($size[0] === $width && $size[1] === $height)) :
				return $output_file;
			endif;
		endif;
		//instantiate the image magick class
		$img = new \Imagick();
		$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
		$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
		$img->readImage($input_file);

		//crop and resize the image
		$img->cropThumbnailImage($width, $height);
		switch (strtolower(pathinfo($output_file, PATHINFO_EXTENSION))):
			case 'webp':
				$img->setImageFormat('webp');
				$img->setImageCompressionQuality(WEBP_QUALITY);
				$img->setOption('webp:lossless', 'true');
				break;
			case 'jpeg':
			case 'jpg':
				$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
				$img->setImageCompressionQuality($quality ?? 85);
				$img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);

				$img->setImageResolution(72, 72);
				$img->setResolution(72, 72);
				$img->setFormat("jpg");
				$img->stripImage();
				$img->setColorspace(\Imagick::COLORSPACE_SRGB);
				break;
			case 'avif':
				$img->setImageFormat('avif');
				$img->setImageCompressionQuality(AVIF_QUALITY);
				break;
				break;
		endswitch;

		if ($img->writeImage($output_file)) :
			$img->clear();
			if($max_bytes == NULL) :
				return $output_file;
			else :
				if(filesize($output_file) <= ($max_bytes ?? 307200)) :
					return $output_file;
				else :
					$quality--;
					if((\file_exists($output_file) && unlink($output_file)) || \true) :
						return self::social($input_file, $output_file, $width, $height, $max_bytes, $quality);
					else :
						return $output_file;
					endif;
				endif;
			endif;
		endif;

		// Return false if the function has not returned a value
		return false;
	}

	/**
	 *
	 * Create thumbnail (JPG) from a image
	 *
	 * @param string $input_file Source image
	 * @param string $output_file  Destination image. Should be JPG
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function squareThumbnail(string $input_file, string $output_file, ?int $size = null)
	{
		if ($size == null) :
			$info = getimagesize($input_file);
			if ($info[0] !== null && $info[1] !== null) :
				$width = $info[0];
				$height = $info[1];
				$size = $height;
			endif;
			if ($width < $height) :
				$size = $width;
			endif;
		endif;
		return Convert2::thumbnail($input_file, $output_file, $size, $size);
	}

	/**
	 *
	 * Convert image to JPG
	 *
	 * @param string $input_file Source image
	 * @param string $output_file  Destination image
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function SocialMedia($input_file, $output_file): string|bool
	{
		if ((!REGENERATE && file_exists($output_file))) :
			return $output_file;
		endif;

		// check if file exists
		if (!file_exists($input_file)) :
			return false;
		endif;

		// check if the file is a cover image
		if ((strpos($input_file, 'cover') !== false || strpos($output_file, 'images/201') !== false || strpos($output_file, 'images/19') !== false)) :
			if (file_exists($output_file)) :
				return $output_file;
			endif;
		endif;

		if (class_exists('\Imagick')) :
			$img = new \Imagick();
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_MAP, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_AREA, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_FILE, MY_MAGICK_MEMORY_LIMIT);
			$img->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
			$img->readImage($input_file);
			$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
			$img->setImageCompressionQuality(100);
			if ((strpos($input_file, 'cover') === false && strpos($output_file, 'images/199') === false && strpos($output_file, 'images/200') === false && strpos($output_file, 'images/201') === false)) :
				self::watermarks($img, $input_file);
			endif;
			$img->setFormat("jpg");
			//$img->stripImage();
			if ($img->writeImage($output_file)) :
				$img->clear();
				if(file_exists($output_file)) :
					// Return the output file
					return $output_file;
				endif;
			endif;
		else :
			if (copy($input_file, $output_file)) :
				if(file_exists($output_file)) :
					// Return the output file
					return $output_file;
				endif;
			endif;
		endif;
		// Return false if the function has not returned a value
		return false;
	}

	/**
	 * Get the average pixel color from the given file using Image Magick
	 *
	 * @param  mixed $filename
	 * @param  mixed $as_hex_string	Set to true, the function will return the 6 character HEX value of the color.
	 * 								If false, an array will be returned with r, g, b components.
	 * @param  mixed $width
	 * @param  mixed $height
	 * @param  mixed $x
	 * @param  mixed $y
	 * @return	string|array
	 */
	#[\Deprecated(message: 'See ColorPalette::get_average_color() instead', since: '1.3.0')]
	public static function get_average_color(string $filename, bool $as_hex_string = true, int $width = 0, int $height = 0, int $x = 0, int $y = 0)
	{
		return ColorPalette::get_average_color($filename, $as_hex_string, $width, $height, $x, $y);
	}

	/**
	 * Get the dimension on an image
	 *
	 * @param	string $filename
	 * @return object $size The dimension object.
	 */
	public static function get_dimension(string $filename) :object
	{
		$url = null;
		if (!empty($filename) && !file_exists($filename) && filter_var($filename, FILTER_VALIDATE_URL) && str_starts_with($filename, 'http')) :
			$url = $filename;
			$filename = null;
		endif;

		$width = (\defined('MAX_WIDTH') ? \MAX_WIDTH : 1);
		$height = (\defined('MAX_HEIGHT') ? \MAX_HEIGHT : 1);
		if($width == 0) :
			$width = $height;
		endif;
		if($height == 0) :
			$height = $width;
		endif;
		$size = array(
			'width' => $width,
			'height' => $height,
			'dimension' => $width / $height
		);
		$json = null;
		try {
			if ($filename !== null) :
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				$json = str_replace('.' . $extension, '.dimension', $filename);
				if (file_exists($json)) :
					$size = (object)json_decode(file_get_contents($json));
					$size->dimension = (float)str_replace(',', '.', (string)$size->dimension);
					return $size;
				endif;
			else :
				$name = md5($url);
				$json = sys_get_temp_dir() . '/' . $name . '.dimension';
				if (file_exists($json)) :
					$size = (object)json_decode(file_get_contents($json));
					$size->dimension = (float)str_replace(',', '.', (string)$size->dimension);
					return $size;
				endif;
			endif;
		} catch (Exception $e) {
			$json = null;
		}
		try {
			if(file_exists($filename)) :
				// Read image file with Image Magick
				$img = new \Imagick($filename);

				// Scale down to 1x1 pixel to make \Imagick do the average
				$size = array(
					'width' => $img->getImageWidth(),
					'height' => $img->getImageHeight()
				);
				$size['dimension'] = str_replace(',', '.', (string)floatval($size['height']) / floatval($size['width']));
			endif;
		} catch (\ImagickException $e) {
			// Image Magick Error!
			return (object)$size;
		} catch (Exception $e) {
			// Unknown Error!
			return (object)$size;
		}
		if($size != null) :
			if ($json != null) :
				// Save the dimension
				file_put_contents($json, json_encode($size));
			endif;
			// Return the dimension object
			return (object)$size;
		endif;
		// Return null if the function has not returned a value
		return (object)$size;
	}



	/**
	 * Replaces the extension of a file
	 *
	 * @param	string $filename The filename
	 * @param	string $new_extension The new extension
	 * @return	string The new filename with the new extension
	 */
	private static function replace_extension(string $filename, string $new_extension)
	{
		$info = pathinfo($filename);
		return ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '')
			. $info['filename']
			. '.'
			. $new_extension;
	}
}
?>