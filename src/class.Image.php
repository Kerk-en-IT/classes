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

enum ImageMimeTypes:string
{
	case JPG = 'image/jpg';
	case JPEG = 'image/jpeg';
	case PNG = 'image/png';
	case GIF = 'image/gif';
}

class Image {

	/**
	 * Create an HTML Img Tag with Base64 Image Data
	 *
	 * @param  resource|\GdImage $image
	 * @param  string $format Image Mimetype @see ```ImageMimeTypes```
	 * @return string|null Base64 image with correct mimetype
	 */
	public static function ToBase64($image, $format=ImageMimeTypes::JPG ) {
		if($image != false) :
			// Validate Format
			if( in_array( $format, array(ImageMimeTypes::JPG, ImageMimeTypes::JPEG,  ImageMimeTypes::PNG,  ImageMimeTypes::GIF ) ) ) :

				ob_start();

				if( $format == ImageMimeTypes::JPG || $format == ImageMimeTypes::JPEG ) :
					imagejpeg($image );
				elseif( $format == ImageMimeTypes::PNG) :
					imagepng($image );
				elseif( $format == ImageMimeTypes::GIF ) :
					imagegif($image );
				endif;

				$data = ob_get_contents();
				ob_end_clean();

				// Check for gd errors / buffer errors
				if( !empty( $data ) ) :

					$data = base64_encode( $data );

					// Check for base64 errors
					if ( $data !== false ) :
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
	public static function Resize($file, $w, $h, $crop=FALSE, $format=ImageMimeTypes::JPG) {
		$image = NULL;
		if(file_exists($file)) :
			if( $format == ImageMimeTypes::JPG || $format == ImageMimeTypes::JPEG ) :
				$image = imagecreatefromjpeg($file);
			elseif( $format == ImageMimeTypes::PNG ) :
				$image = imagecreatefrompng($file);
			elseif( $format ==ImageMimeTypes::GIF ) :
				$image = imagecreatefromgif($file);
			endif;
		endif;
		if($image != NULL) :
			$thumb_width = $w;
			$thumb_height = $h;

			$width = imagesx($image);
			$height = imagesy($image);

			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;

			if ( $original_aspect >= $thumb_aspect )
			{
			   // If image is wider than thumbnail (in aspect ratio sense)
			   $new_height = $thumb_height;
			   $new_width = $width / ($height / $thumb_height);
			}
			else
			{
			   // If the thumbnail is wider than the image
			   $new_width = $thumb_width;
			   $new_height = $height / ($width / $thumb_width);
			}

			$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );
			if( $format == ImageMimeTypes::PNG ) :
				imagealphablending( $thumb, false );
				imagesavealpha( $thumb, true );
			endif;
			// Resize and crop
			imagecopyresampled($thumb,
			                   $image,
			                   0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
			                   0 - ($new_height - $thumb_height) / 2, // Center the image vertically
			                   0, 0,
			                   $new_width, $new_height,
			                   $width, $height);
			 return $thumb;
		 endif;
		 return FALSE;
	}
}

?>