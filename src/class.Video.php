<?php

namespace KerkEnIT;

/**
 * Video Class File for Kerk en IT Framework
 *
 * Convert video files with ffmpeg in various formats which are perfect for the web.
 *
 * PHP versions 8.4
 *
 * @package		KerkEnIT
 * @subpackage	Video
 * @author		Marco van 't Klooster <info@kerkenit.nl>
 * @copyright	2025-2025 © Kerk en IT
 * @license		https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link		https://www.kerkenit.nl
 * @since		Class available since Release 1.2.0
 */
class Video
{
	/**
	 * The video path
	 *
	 * @var string
	 */
	protected ?string $_videoPath = null;
	/**
	 * The color space
	 *
	 * @var string
	 */
	protected ?string $_colorSpace = null;
	/**
	 * The hlg
	 *
	 * @var bool
	 */
	protected ?bool $_hlg = false;

	/**
	 * The poster
	 *
	 * @var string
	 */
	protected ?string $_poster = null;

	/**
	 * Create a new Video object with the source video path to convert video files with ffmpeg
	 *
	 * @param	string $source The source video path
	 * @return void
	 */
	public function __construct(string $source)
	{
		if (file_exists($source)) :
			$this->source = $source;
		endif;
	}

	/**
	 * Create a watermark on the video
	 *
	 * @param	string $watermark The watermark image path
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function watermark(string $watermark, ?string $destinationPath): string
	{
		if ($destinationPath !== null) :
			$filter = null;
			//$img = new \Imagick();
			//$img->readImage($this->_poster);
			//$width = $img->getImageWidth();
			//$height = $img->getImageHeight();
			////-filter_complex '[0:v:0]colorspace=ispace=bt709:itrc=bt709:iprimaries=bt709:range=tv:primaries=bt2020:space=bt2020ncl:trc=bt2020-10:dither=none, zscale=transferin=2020_10, tonemap=tonemap=gamma:tonemap=0.25, zscale=transfer=2020_10[Output]'
			//if ($width != 0 && $height !== 0) :
			//	if ($width > $height) :
			//		//Landscape
			//		$filter = '[1][0]scale2ref=oh*mdar:ih*' . (string)((($width / 100) / ($height / 6.00))) . '[logo][video];[video][logo]overlay=(main_w-overlay_w):(main_h-overlay_h):format=auto,format=' . ($this->hlg ? 'yuv420p10le' : 'yuv420p');
			//	else :
			//		//Portrait
			//		$filter = '[1][0]scale2ref=oh*mdar:iw*' . (string)(((($width / 100) / ($height / 6.00))) * 2.34) . '[logo][video];[video][logo]overlay=(main_w-overlay_w):(main_h-overlay_h):format=auto,format=' . ($this->hlg ? 'yuv420p10le' : 'yuv420p');
			//	endif;
			//endif;
			$filter = '[1]scale=iw/2:-1[b];[0:v][b] overlay=x=(main_w-overlay_w):y=(main_h-overlay_h)';

			//$img_poster = new \Imagick($this->_poster);
			//$watermark = Convert2::watermarks($img_poster, $this->_poster);

			$watermark = realpath($watermark);
			if ($watermark === false) :
				throw new \Exception('Failed to create watermark. File not found');
			endif;
			$command = "ffmpeg -i '{$this->source}' -i '{$watermark}' -filter_complex \"{$filter}\" -c:a copy '{$destinationPath}' -y";
			//Disable Watermark for protecting copyright
			//$command = "cp '{$vidpath} '{$destinationPath}'";
			if (shell_exec($command) === FALSE) :
				throw new \Exception('Failed to create watermark. ' . $command);
			endif;
		endif;
		if ($destinationPath === null || !file_exists($destinationPath)) :
			throw new \Exception('Failed to create watermark. File not found');
		else:
			$this->source = $destinationPath;
		endif;
		return $destinationPath;
	}

	/**
	 * The getter for the video path
	 *
	 * @param	string $name
	 * @return	string $videoPath or throw an \Exception
	 */
	public string $source {
		get {
			if ($this->_videoPath === null) {
				throw new \Exception('No video path set');
			}
			return $this->_videoPath;
		}
		set(string $value) {
			$this->_videoPath = $value;
		}
	}

	/**
	 * The getter for the color space
	 *
	 * @return	string $colorSpace or an empty string
	 */
	private string $colorSpace {
		get {
			if ($this->_colorSpace === null) {
				try {
					$this->_colorSpace = shell_exec("ffprobe -show_streams -v error '{$this->source}' | egrep \"^color_transfer|^color_space=|^color_primaries=\" | head -3");
				} catch (\Exception $e) {
					throw new \Exception('Failed to get color space. ' . $e->getMessage());
				}
			}
			return $this->_colorSpace ?? '';
		}
	}

	/**
	 * The getter for the hlg
	 *
	 * @return bool $hlg or false
	 */
	public bool $hlg {
		get {
			if ($this->_hlg === null) {
				$this->_hlg = false;
				if (strpos($this->colorSpace, 'color_space=bt2020nc') !== false) :
					$this->_hlg = true;
				endif;
			}
			return $this->_hlg ?? false;
		}
	}

	/**
	 * Create a poster image of the video
	 *
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function poster(?string $destinationPath): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$command = array("ffmpeg");
			$command[] = " -y -i '{$this->source}'";
			$command[] = "-frames:v 1";
			$command[] = "-ss `ffmpeg -i '{$this->source}' 2>&1 | grep Duration | awk '{print $2}' | tr -d , | awk -F ':' '{print ($3+$2*60+$1*3600)/2}' | sed 's/,/./g'`";
			$command[] = "-c:v mjpeg";
			$command[] = "-qscale:v 1";
			$command[] = "-qmin 1";
			if ($this->hlg) :
				$command[] = "-vf 'zscale=t=linear:npl=250,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p'";
			endif;
			$command[] = "-pix_fmt yuvj444p";
			$command[] = "-f mjpeg";
			$command[] = "'{$destinationPath}'";
			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create poster. ' . implode(' ', $command));
			endif;
		endif;
		if (!file_exists($destinationPath)) :
			throw new \Exception('Failed to create poster. File not found');
		endif;
		$this->_poster = $destinationPath;
		return $this->_poster;
	}

	/**
	 * Create a ogv video of the source video file
	 * The video is converted to the theora codec
	 * The audio is converted to the vorbis codec
	 *
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function ogv(?string $destinationPath): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$command = array("ffmpeg");
			$command[] = "-y -i '$this->source'";

			$command[] = "-b:v 10M";
			$command[] = "-c:v libtheora";
			if ($this->hlg) :
				$command[] = "-vf 'zscale=t=linear:npl=250,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p'";
				$command[] = "-pix_fmt yuv444p";
			endif;
			$command[] = "-c:a libvorbis";
			$command[] = "-b:a 128K";
			$command[] = "-g 30";

			$command[] = "'$destinationPath'";

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create ogv. ' . implode(' ', $command));
			endif;
		endif;

		if (!file_exists($destinationPath)) :
			throw new \Exception('Failed to create ogv. File not found');
		endif;
		return $destinationPath;
	}

	/**
	 * Create a mp4 video of the source video file
	 * The video is converted to the h264 codec
	 * The audio is converted to the aac codec
	 *
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function mp4(?string $destinationPath): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$command = array("ffmpeg");
			$command[] = "-i '$this->source'";

			$command[] = "-c:v libx264";
			$command[] = "-b:v 6M";
			$command[] = "-pix_fmt yuv420p";
			$command[] = "-profile:v baseline";
			$command[] = "-level 4";
			$command[] = "-minrate 4M";
			$command[] = "-maxrate 8M";
			$command[] = "-bufsize 16M";

			$command[] = "-c:a aac";
			$command[] = "-b:a 98K";
			$command[] = "-pass 1";
			$command[] = "-f null";
			$command[] = "/dev/null";

			$command[] = "&&";
			$command[] = "ffmpeg";
			$command[] = "-i '$this->source'";

			$command[] = "-c:v libx264";
			$command[] = "-b:v 6M";
			$command[] = "-pix_fmt yuv420p";
			$command[] = "-profile:v baseline";
			$command[] = "-level 4";
			$command[] = "-minrate 4M";
			$command[] = "-maxrate 8M";
			$command[] = "-bufsize 16M";

			$command[] = "-c:a aac";
			$command[] = "-b:a 98K";
			$command[] = "-pass 2";
			$command[] = "-f mp4";
			$command[] = "-y '$destinationPath'";

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create mp4. ' . implode(' ', $command));
			endif;
		endif;
		if (!file_exists($destinationPath)) :
			throw new \Exception('Failed to create mp4. File not found');
		endif;
		return $destinationPath;
	}

	/**
	 * Create a h265 video of the source video file
	 * The video is converted to the h265 codec
	 * The audio is converted to the aac codec
	 *
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function h265(?string $destinationPath): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$command = array("ffmpeg");
			$command[] = "-y -i '$this->source' -pass 1";

			$command[] = "-c:v libx265";
			$command[] = "-movflags faststart";
			$command[] = "-preset slower";
			$command[] = "-tag:v hvc1";
			//$command[] = "-strict";
			//$command[] = "-x265-params pass=1";

			$command[] = "-c:a aac";
			$command[] = "-b:a 128K";
			$command[] = "-f mp4";
			$command[] = "/dev/null";

			$command[] = "&&";
			$command[] = "ffmpeg";
			$command[] = "-y -i '$this->source' -pass 2";

			$command[] = "-c:v libx265";
			$command[] = "-movflags faststart";
			$command[] = "-preset slower";
			$command[] = "-tag:v hvc1";
			//$command[] = "-strict";
			//$command[] = "-x265-params pass=2";

			$command[] = "-c:a aac";
			$command[] = "-b:a 128K";
			$command[] = "-f mp4";
			$command[] = "'$destinationPath'";

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create h265. ' . implode(' ', $command));
			endif;
		endif;
		if (!file_exists($destinationPath)) :
			throw new \Exception('Failed to create h265. File not found');
		endif;
		return $destinationPath;
	}

	/**
	 * Create a webm video of the source video file
	 * The video is converted to the vp9 codec
	 * The audio is converted to the opus codec
	 *
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function webm(?string $destinationPath): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$command = array("ffmpeg");
			$command[] = "-y -i '$this->source' -pass 1";

			$command[] = "-c:v libvpx-vp9";
			$command[] = "-b:v 12M";
			//$command[] = "-speed 4";
			if ($this->hlg) :
				$command[] = "-pix_fmt yuv420p10le";
				$command[] = "-color_primaries 9";
				$command[] = "-color_trc 18";
				$command[] = "-colorspace 9";
				$command[] = "-color_range 1";
			else :
				$command[] = "-pix_fmt yuv420p";
				$command[] = "-tile-columns 0";
				$command[] = "-frame-parallel 0";
				$command[] = "-auto-alt-ref 1";
				$command[] = "-lag-in-frames 25";
				$command[] = "-g 9999";
				$command[] = "-aq-mode 0";
			endif;

			$command[] = "-minrate 6M";
			$command[] = "-maxrate 18M";
			$command[] = "-bufsize 24M";
			//$command[] = "-profile:v 2";

			$command[] = "-c:a libopus";
			$command[] = "-b:a 64k";

			$command[] = "-f webm";
			$command[] = "/dev/null";

			$command[] = "&&";
			$command[] = "ffmpeg";
			$command[] = "-y -i '$this->source' -pass 2";

			$command[] = "-c:v libvpx-vp9";
			$command[] = "-b:v 12M";
			//$command[] = "-speed 4";
			if ($this->hlg) :
				$command[] = "-pix_fmt yuv420p10le";
				$command[] = "-color_primaries 9";
				$command[] = "-color_trc 18";
				$command[] = "-colorspace 9";
				$command[] = "-color_range 1";
			else :

				$command[] = "-pix_fmt yuv420p";
				$command[] = "-tile-columns 0";
				$command[] = "-frame-parallel 0";
				$command[] = "-auto-alt-ref 1";
				$command[] = "-lag-in-frames 25";
				$command[] = "-g 9999";
				$command[] = "-aq-mode 0";
			endif;
			$command[] = "-minrate 6M";
			$command[] = "-maxrate 18M";
			$command[] = "-bufsize 24M";
			//$command[] = "-profile:v 2";

			$command[] = "-c:a libopus";
			$command[] = "-b:a 64k";
			$command[] = "-f webm";
			$command[] = "'$destinationPath'";

			if (exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create webm. ' . implode(' ', $command));
			endif;
		endif;
		if (!file_exists($destinationPath)) :
			throw new \Exception('Failed to create webm. File not found');
		endif;
		return $destinationPath;
	}

	/**
	 * Create a vp8 video of the source video file
	 * The video is converted to the vp8 codec
	 * The audio is converted to the vorbis codec
	 *
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function vp8(?string $destinationPath): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$command = array("ffmpeg");
			$command[] = "-y -i '{$this->source}' -pass 1";

			$command[] = "-c:v libvpx";
			$command[] = "-b:v 5M";
			if ($this->hlg) :
				if ($this->hlg) :
					$command[] = "-vf 'zscale=t=linear:npl=250,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p'";
					$command[] = "-pix_fmt yuv420p";
				else :
					$command[] = "-pix_fmt yuv420p10le";
					$command[] = "-color_primaries 9";
					$command[] = "-color_trc 18";
					$command[] = "-colorspace 9";
					$command[] = "-color_range 1";
				endif;
			endif;
			$command[] = "-threads 1";
			$command[] = "-speed 4";
			$command[] = "-tile-columns 0";
			$command[] = "-frame-parallel 0";
			$command[] = "-auto-alt-ref 1";
			$command[] = "-lag-in-frames 25";
			$command[] = "-g 9999";
			$command[] = "-aq-mode 0";

			$command[] = "-minrate 1M";
			$command[] = "-maxrate 10M";
			$command[] = "-bufsize 15M";
			$command[] = "-profile:v 2";
			$command[] = "-max_muxing_queue_size 9999";

			$command[] = "-c:a libvorbis";
			$command[] = "-b:a 96k";
			$command[] = "-f webm";
			$command[] = "/dev/null";
			$command[] = "&&";
			$command[] = "ffmpeg -y -i '{$this->source}' -pass 2";

			$command[] = "-c:v libvpx";
			$command[] = "-b:v 5M";
			if ($this->hlg) :
				if ($this->hlg) :
					$command[] = "-vf 'zscale=t=linear:npl=250,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p'";
					$command[] = "-pix_fmt yuv420p";
				else :
					$command[] = "-pix_fmt yuv420p10le";
					$command[] = "-color_primaries 9";
					$command[] = "-color_trc 18";
					$command[] = "-colorspace 9";
					$command[] = "-color_range 1";
				endif;
			endif;
			$command[] = "-threads 1";
			$command[] = "-speed 4";
			$command[] = "-tile-columns 0";
			$command[] = "-frame-parallel 0";
			$command[] = "-auto-alt-ref 1";
			$command[] = "-lag-in-frames 25";
			$command[] = "-g 9999";
			$command[] = "-aq-mode 0";

			$command[] = "-minrate 1M";
			$command[] = "-maxrate 10M";
			$command[] = "-bufsize 15M";
			$command[] = "-profile:v 2";
			$command[] = "-max_muxing_queue_size 9999";

			$command[] = "-c:a libvorbis";
			$command[] = "-b:a 96k";
			$command[] = "-f webm";
			$command[] = "'{$destinationPath}'";

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create vp8. ' . implode(' ', $command));
			endif;
		endif;
		if (!file_exists($destinationPath)) :
			throw new \Exception('Failed to create vp8. File not found');
		endif;
		return $destinationPath;
	}
}
