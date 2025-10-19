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
	 * The source video path
	 * This is the path to the video file that should be converted
	 * @var string|null
	 */
	protected ?string $_videoPath = null;

	/**
	 * The color space of the video
	 * @var string|null
	 */
	protected ?string $_colorSpace = null;

	/**
	 * If the video is in HLG (Hybrid Log-Gamma) format
	 * This is used to determine if the video should be processed with HLG settings
	 * HLG is a high dynamic range (HDR) format that is used for broadcasting and streaming
	 * It is used to create videos with a wider color gamut and higher brightness levels
	 * @var bool
	 */
	protected ?bool $_hlg = false;

	/**
	 * The poster image path
	 * This is the path to the poster image that is created from the video file
	 * The poster image is a still image that is used as a preview of the video
	 * @var string|null
	 */
	protected ?string $_poster = null;

	/**
	 * If the ffmpeg command should use multi-core processing
	 * This is only available on macOS (Darwin) and not on Linux or Windows
	 * This is used to speed up the processing of the video files
	 * @var bool
	 */
	protected bool $_multicore = false;

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
	 * The getter and setter for the multi-core processing
	 * This is only available on macOS (Darwin) and not on Linux or Windows
	 * This is used to speed up the processing of the video files
	 *
	 * @return bool $multicore
	 */
	public bool $multicore {
		get {
			if (PHP_OS != 'Darwin') {
				return false;
			}
			return $this->_multicore;
		}
		set(bool $value) {
			$this->_multicore = $value;
		}
	}

	/**
	 * Create a watermark on the video
	 *
	 * @param	string $watermark The watermark image path
	 * @param	string|null $destinationPath The requested destination path
	 * @return	string $destinationPath or throw an \Exception if the file is not found or the command failed
	 */
	public function watermark(string $watermark, ?string $destinationPath = null): string
	{
		if ($destinationPath !== null && !is_file($destinationPath)) :
			$filter = null;
			$filter = '[1]scale=iw/2:-1[b];[0:v][b] overlay=x=(main_w-overlay_w):y=(main_h-overlay_h)';

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
	 * The getter and setter for the video path
	 * This is the path to the video file that should be converted
	 * If the video path is not set, an exception will be thrown
	 *
	 * @return string $source The video path or throw an \Exception if the path is not set
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
	 * The getter for the color space of the video
	 *
	 * @return string $colorSpace The color space of the video or throw an \Exception if the command failed
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
			$command = array();
			if ($this->multicore) :
				$command[] = 'nohup ';
			endif;
			$command[] = "ffmpeg";
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
			if ($this->multicore) :
				$command[] = ' </dev/null >/dev/null 2>&1 &';
			endif;
			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create poster. ' . implode(' ', $command));
			endif;
		endif;
		if (!$this->multicore && !file_exists($destinationPath)) :
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
			$command = array();
			if ($this->multicore) :
				$command[] = 'nohup ';
			endif;
			$command[] = "ffmpeg";
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
			if ($this->multicore) :
				$command[] = ' </dev/null >/dev/null 2>&1 &';
			endif;

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create ogv. ' . implode(' ', $command));
			endif;
		endif;

		if (!$this->multicore && !file_exists($destinationPath)) :
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
			$command = array();
			if ($this->multicore) :
				$command[] = 'nohup ';
			endif;
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
			if ($this->multicore) :
				$command[] = ' </dev/null >/dev/null 2>&1 &';
			endif;

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create mp4. ' . implode(' ', $command));
			endif;
		endif;
		if (!$this->multicore && !file_exists($destinationPath)) :
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
			$command = array();
			if ($this->multicore) :
				$command[] = 'nohup ';
			endif;
			$command[] = "ffmpeg";
			$command[] = "-y -i '$this->source' -pass 1";

			$command[] = "-c:v libx265";
			$command[] = "-movflags faststart";
			$command[] = "-preset slower";
			$command[] = "-tag:v hvc1";

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

			$command[] = "-c:a aac";
			$command[] = "-b:a 128K";
			$command[] = "-f mp4";
			$command[] = "'$destinationPath'";
			if ($this->multicore) :
				$command[] = ' </dev/null >/dev/null 2>&1 &';
			endif;

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create h265. ' . implode(' ', $command));
			endif;
		endif;
		if (!$this->multicore && !file_exists($destinationPath)) :
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
			$command = array();
			if ($this->multicore) :
				$command[] = 'nohup ';
			endif;
			$command[] = "ffmpeg";
			$command[] = "-y -i '$this->source' -pass 1";

			$command[] = "-c:v libvpx-vp9";
			$command[] = "-b:v 12M";
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

			$command[] = "-c:a libopus";
			$command[] = "-b:a 64k";

			$command[] = "-f webm";
			$command[] = "/dev/null";

			$command[] = "&&";
			$command[] = "ffmpeg";
			$command[] = "-y -i '$this->source' -pass 2";

			$command[] = "-c:v libvpx-vp9";
			$command[] = "-b:v 12M";

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

			$command[] = "-c:a libopus";
			$command[] = "-b:a 64k";
			$command[] = "-f webm";
			$command[] = "'$destinationPath'";
			if ($this->multicore) :
				$command[] = ' </dev/null >/dev/null 2>&1 &';
			endif;

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create webm. ' . implode(' ', $command));
			endif;
		endif;
		if (!$this->multicore && !file_exists($destinationPath)) :
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
			$command = array();
			if ($this->multicore) :
				$command[] = 'nohup ';
			endif;
			$command[] = "ffmpeg";
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
			if ($this->multicore) :
				$command[] = ' </dev/null >/dev/null 2>&1 &';
			endif;

			if (shell_exec(implode(' ', $command)) === FALSE) :
				throw new \Exception('Failed to create vp8. ' . implode(' ', $command));
			endif;
		endif;
		if (!$this->multicore && !file_exists($destinationPath)) :
			throw new \Exception('Failed to create vp8. File not found');
		endif;
		return $destinationPath;
	}
}
