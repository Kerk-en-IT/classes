<?php

namespace KerkEnIT;

/**
 * Image Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Image
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2024-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/

enum ImageMimeTypes: string
{
	case JPG = 'image/jpg';
	case JPEG = 'image/jpeg';
	case PNG = 'image/png';
	case GIF = 'image/gif';
}

class Image
{

	/**
	 * Create an HTML Img Tag with Base64 Image Data
	 *
	 * @param  resource|\GdImage $image
	 * @param  string $format Image Mimetype @see ```ImageMimeTypes```
	 * @return string|null Base64 image with correct mimetype
	 */
	public static function ToBase64($image, $format = ImageMimeTypes::JPG)
	{
		if ($image != false) :
			// Validate Format
			if (in_array($format, array(ImageMimeTypes::JPG, ImageMimeTypes::JPEG,  ImageMimeTypes::PNG,  ImageMimeTypes::GIF))) :

				ob_start();

				if ($format == ImageMimeTypes::JPG || $format == ImageMimeTypes::JPEG) :
					imagejpeg($image);
				elseif ($format == ImageMimeTypes::PNG) :
					imagepng($image);
				elseif ($format == ImageMimeTypes::GIF) :
					imagegif($image);
				endif;

				$data = ob_get_contents();
				ob_end_clean();

				// Check for gd errors / buffer errors
				if (!empty($data)) :

					$data = base64_encode($data);

					// Check for base64 errors
					if ($data !== false) :
						// Success
						return $data;
					endif;
				endif;
			endif;
		endif;

		// Failure
		return null;
	}

	/**
	 * Resize the image to the correct size
	 *
	 * @param  string $file
	 * @param  int|float $w Destination width
	 * @param  int|float $h Destination height
	 * @param  bool $crop Crops the extra information of the image
	 * @param  string $format Image Mimetype @see ```ImageMimeTypes``
	 * @return resource|\GdImage|false
	 */
	public static function Resize($file, $w, $h, $crop = FALSE, $format = ImageMimeTypes::JPG)
	{
		$image = NULL;
		if (file_exists($file)) :
			if ($format == ImageMimeTypes::JPG || $format == ImageMimeTypes::JPEG) :
				$image = imagecreatefromjpeg($file);
			elseif ($format == ImageMimeTypes::PNG) :
				$image = imagecreatefrompng($file);
			elseif ($format == ImageMimeTypes::GIF) :
				$image = imagecreatefromgif($file);
			endif;
		endif;
		if ($image != NULL) :
			$thumb_width = $w;
			$thumb_height = $h;

			$width = imagesx($image);
			$height = imagesy($image);

			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;

			if ($original_aspect >= $thumb_aspect) {
				// If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);
			} else {
				// If the thumbnail is wider than the image
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}

			$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
			if ($format == ImageMimeTypes::PNG) :
				imagealphablending($thumb, false);
				imagesavealpha($thumb, true);
			endif;
			// Resize and crop
			imagecopyresampled(
				$thumb,
				$image,
				0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
				0 - ($new_height - $thumb_height) / 2, // Center the image vertically
				0,
				0,
				$new_width,
				$new_height,
				$width,
				$height
			);
			return $thumb;
		endif;
		return FALSE;
	}

	public static function getimagesize($image): array|false
	{
		try {
			$mem_var = new \Memcached();
			$mem_var->addServer("127.0.0.1", 11211);
			$size = $mem_var->get("image_size_" . md5($image));
			if ($size == false) :
				$user_agents = array(
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/16.16299",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/15.15063",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/14.14393",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/13.10586",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/12.10240",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/11.0",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/10.0",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36 Edge/9.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9",
					"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.6399.1311 Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2077.1331 Mobile Safari/537.36",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.1074.1436 Mobile Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2724.1566 Mobile Safari/537.36",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.4733.1228 Mobile Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.5967.1173 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.5967.1173 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2121.1897 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.5038.1665 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.6399.1311 Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2077.1331 Mobile Safari/537.36",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.1074.1436 Mobile Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2724.1566 Mobile Safari/537.36",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.4733.1228 Mobile Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko)",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.51",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.5967.1173 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.5967.1173 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2121.1897 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.5038.1665 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.6399.1311 Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2077.1331 Mobile Safari/537.36",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.1074.1436 Mobile Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2724.1566 Mobile Safari/537.36",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.4733.1228 Mobile Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko)",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko)",
					"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko)"
				);
				$user_agent = $user_agents[array_rand($user_agents)];

				//$size = getimagesize($image);
				$ch = curl_init($image);
				curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL, $image);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
				$raw = curl_exec($ch);
				curl_close($ch);
				$fp = tmpfile();
				fwrite($fp, $raw);
				$size = getimagesize(stream_get_meta_data($fp)['uri']);
				fclose($fp);
			endif;
		} catch (\Exception $e) {
			$size = false;
		} finally {
			if ($size !== false) :
				$mem_var->set("image_size_" . md5($image), $size, 7200);
			endif;
			return $size;
		}
	}
}
