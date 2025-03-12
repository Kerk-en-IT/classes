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
if(DEBUG) :
	define('REGENERATE', false);
else :
	define('REGENERATE', false);
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
 * Convert2
 *
 * Formatting various objects into the expected output.
 *
 * @package    Marcos
 * @subpackage Convert2
 * @author     Marco van 't Klooster <info@marcovantklooster.nl>
 * @copyright  2022 Marco van 't Klooster
 * @license    http://www.apache.org/licenses/   Apache License Version 2.0
 * @link       https://www.marcovantklooster.nl
 * @since      Class available since Release 1.0.59
 */
class Convert2
{

	public static function watermarks(&$img, $input_file)
	{
		if(realpath(__DIR__ . "/watermark.png") !== false) :
			$watermark = new \Imagick();
			$watermark->readImage(realpath(__DIR__ . "/watermark.png"));

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

			$avg_color = self::get_average_color($input_file, true, (int)$watermark->getImageWidth(), (int)$watermark->getImageHeight(), (int)$x, (int)$y);
			$rgb = self::hex_to_rgb($avg_color);

			$composite = \Imagick::COMPOSITE_LIGHTEN;

			$watermark = new \Imagick();
			if($rgb->avg < 96 && $rgb->g > 64 && $rgb->b < 64 && $rgb->r < 64) :
				$png = __DIR__ . "/watermark_dark.png";
			elseif ($rgb->avg > 96 && $rgb->g > 64 && $rgb->b < 64 && $rgb->r < 64) :
				$png = __DIR__ . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg < 128 && $rgb->g > 64 && $rgb->b < 128 && $rgb->b > 64 && $rgb->r >128 && $rgb->r < 144) :
				$png = __DIR__ . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg < 128 && $rgb->g > 128 && $rgb->b < 128 && $rgb->r > 96) :
				$png = __DIR__ . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg > 128 && $rgb->avg < 196 && $rgb->g >128 && $rgb->g < 196 && $rgb->b < 128 && $rgb->b > 96 && $rgb->r >128 && $rgb->r < 144) :
				$png = __DIR__ . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			elseif ($rgb->avg < 20 && $rgb->g < 25 && $rgb->b < 25 && $rgb->r < 25) :
				$png = __DIR__ . "/watermark_dark.png";
			elseif ($rgb->avg > 235 && $rgb->g > 230 && $rgb->b > 230 && $rgb->r > 230) :
				$png = __DIR__ . "/watermark_light.png";
				$composite = \Imagick::COMPOSITE_DARKEN;
			else :
				$png = __DIR__ . "/watermark.png";
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
	 * @param string $input_file Source image
	 * @param string $output_file  Destination image
	 * @param int $width Size in pixels what the expected width should be.
	 * @param int $height Size in pixels what the expected height should be. Default 0 for auto height
	 * @return mixed when succeed the file path; otherwise FALSE
	 */
	public static function shrink(string $input_file, string $output_file, int $width, int $height = 0)
	{
		// check if file exists
		if (!file_exists($input_file)) :
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
					if(unlink($output_file)) :
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
	 * @return string|array
	 */
	public static function get_average_color(string $filename, bool $as_hex_string = true, int $width = 0, int $height = 0, int $x = 0, int $y = 0)
	{
		if(!empty($filename)) :
			$crop = false;
			if (($width + $height + $x + $y) !== 0) :
				$crop = true;
			endif;
			$json = null;
			try
			{
				$filename = realpath($filename);
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				$json = str_replace('.' . $extension, ($crop ? '.crop' : '.color'), $filename);
				if(file_exists($json)) :
					// Return the HEX color
					return json_decode(file_get_contents($json));
				endif;
			} catch (Exception $e) {
				$json = null;
			}
			//if(DEBUG) :
			//	return 'transparent';
			//endif;
			try {
				// Read image file with Image Magick
				$image = new \Imagick($filename);

				if($crop) :
					$image->cropImage($width, $height, $x, $y);
				endif;
				// Scale down to 1x1 pixel to make \Imagick do the average
				$image->scaleimage(1, 1);
				/** @var \ImagickPixel $pixel */
				if (!$pixels = $image->getimagehistogram()) :
					// Return transparent if the function has not returned a value
					return 'transparent';
				endif;
			} catch (\ImagickException $e) {
				// Image Magick Error!
				return 'transparent';
			} catch (Exception $e) {
				// Unknown Error!
				return 'transparent';
			}

			$pixel = reset($pixels);
			$rgb = $pixel->getcolor();

			if ($as_hex_string) :
				$hex = '#' . sprintf('%02X%02X%02X', $rgb['r'], $rgb['g'], $rgb['b']);
				if($json != null) :
					file_put_contents($json, json_encode($hex));
				endif;
				// Return the HEX color
				return $hex;
			endif;
			if ($json != null) :
				file_put_contents($json, json_encode($rgb));
			endif;
			// Return the RGB array
			return $rgb;
		endif;
		// Return transparent if the function has not returned a value
		return 'transparant';
	}

	/**
	 * Get the dimension on an image
	 *
	 * @param  string $filename
	 * @return object $size The dimension object
	 */
	public static function get_dimension(string $filename)
	{
		$size = null;
		$json = null;
		try {
			$filename = realpath($filename);
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			$json = str_replace('.' . $extension, '.dimension', $filename);
			if (file_exists($json)) :
				$size = (object)json_decode(file_get_contents($json));
				$size->dimension = str_replace(',', '.', (string)$size->dimension);
				return $size;
			endif;
		} catch (Exception $e) {
			$json = null;
		}
		try {
			// Read image file with Image Magick
			$img = new \Imagick($filename);

			// Scale down to 1x1 pixel to make \Imagick do the average
			$size = array(
				'width' => $img->getImageWidth(),
				'height' => $img->getImageHeight()
			);
			$size['dimension'] = str_replace(',', '.', (string)floatval($size['height']) / floatval($size['width']));
		} catch (\ImagickException $e) {
			// Image Magick Error!
			return null;
		} catch (Exception $e) {
			// Unknown Error!
			return null;
		}

		if ($json != null) :
			// Save the dimension
			file_put_contents($json, json_encode($size));
		endif;
		// Return the dimension object
		return (object)$size;
	}


	/**
	 * Get the contrast color of a given HEX color
	 *
	 * @param  string $hexColor The HEX color
	 * @return string $contrastColor The contrast color of the given HEX color
	 */
	public static function get_contrast_color(string $hexColor) :string
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
	 * @param  string $hexColor
	 * @return object $rgb The RGB object
	 */
	public static function hex_to_rgb(string $hexColor)
	{
		$hexColor = ltrim($hexColor, '#');
		list($r, $g, $b) = array((double)hexdec(substr($hexColor, 0, 2)), (double)hexdec(substr($hexColor, 2, 2)), (double)hexdec(substr($hexColor, 4, 2)));

		return (object)array('r' => $r, 'g' => $g, 'b' => $b, 'avg' => ($r + $g + $b) / 3.00);
	}


	/**
	 * Replaces the extension of a file
	 *
	 * @param  string $filename The filename
	 * @param  string $new_extension The new extension
	 * @return string The new filename with the new extension
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