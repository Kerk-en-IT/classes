<?php
namespace KerkEnIT;
@ini_set('allow_url_fopen', '1');
/**
 * Meta Class File for Kerk en IT Framework
 *
 * PHP versions 8.0 or higher (minimum: 8.0, required for union types)
 *
 * @package    KerkEnIT
 * @subpackage Meta
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2026 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.2.1
 *
 * @requires   PHP extension "curl" (ext-curl)      getRedirectUrl(), loadUrl()
 * @requires   PHP extension "dom"  (ext-dom)       loadMetaTags() — DOMDocument/DOMXPath
 * @requires   PHP extension "xml"  (ext-xml)       required by ext-dom
 * @requires   PHP extension "filter" (ext-filter)  filter_var()/FILTER_VALIDATE_URL
 * @requires   PHP extension "gd"   (ext-gd)        getImageWidth/Height/Type() via getimagesize()
 * @requires   INI setting "allow_url_fopen" = On   loadUrl() remote fetch via file_get_contents()
 * @depends    \KerkEnIT\Networking  getRandomUserAgent() used by cURL requests
 * @depends    \KerkEnIT\DateTime    GetDate() used to parse published/updated/modified times
 * @depends     \Memcache (optional)  ext-memcache; loadUrl() caches via the global \Memcache
 *                                   class when present (a local dummy fallback exists otherwise)
 **/
class Meta
{
	protected ?string $html;
	protected ?string $url;

	protected ?string $title = null;
	protected ?string $description = null;
	protected ?string $image = null;
	protected ?array $image_size = null;
	protected ?int $image_width = null;
	protected ?int $image_height = null;
	protected ?string $image_alt = null;
	protected ?string $image_type = null;
	protected ?\DateTime $created_at = null;
	protected ?\DateTime $published_time = null;
	protected ?\DateTime $modified_time = null;
	protected ?\DateTime $updated_time = null;
	protected ?string $author = null;

	/**
	 * Constructor for the Meta class.
	 *
	 * @param string|null $html The HTML content to extract meta tags from.
	 * @param string|null $url  The URL of the page.
	 */
	public function __construct(?string $html, ?string $url)
	{
		if(!empty($html)) :
			$this->html = $html;
		endif;
		if(!empty($url) && \filter_var($url, \FILTER_VALIDATE_URL) !== false) :
			$this->url = $url;
		endif;

		$this->loadMetaTags();
	}

	/**
	 * Get the title of the meta.
	 *
	 * @return string|null
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 * Get the description of the meta.
	 *
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * Get the image URL of the meta.
	 *
	 * @return string|null
	 */
	public function getImageURL(): ?string
	{
		return $this->image;
	}

	/**
	 * Get the width of the image.
	 *
	 * @return int|null
	 */
	public function getImageWidth(): ?int
	{
		if(!$this->image_size && $this->image) {
			$this->image_size = @\getimagesize($this->image);
		}
		return $this->image_size[0] ?? null;
	}

	/**
	 * Get the height of the image.
	 *
	 * @return int|null
	 */
	public function getImageHeight(): ?int
	{
		if(!$this->image_size && $this->image) {
			$this->image_size = @\getimagesize($this->image);
		}
		return $this->image_size[1] ?? null;
	}


	/**
	 * Get the alt text of the image.
	 *
	 * @return string|null
	 */
	public function getImageAlt(): ?string
	{
		return $this->image_alt;
	}

	/**
	 * Get the MIME type of the image.
	 *
	 * @return string|null
	 */
	public function getImageType(): ?string
	{
		if(!$this->image_type && $this->image) {
			$this->image_type = @\getimagesize($this->image)['mime'] ?? null;
		}
		return $this->image_type;
	}

	/**
	 * Get the author of the meta.
	 *
	 * @return string|null
	 */
	public function getAuthor(): ?string
	{
		return $this->author;
	}

	/**
	 * Get the URL of the meta.
	 *
	 * @return string|null
	 */
	public function getUrl(): ?string
	{
		return self::getRedirectUrl($this->url);
	}

	/**
	 * Get the Open Graph meta tags as an associative array.
	 *
	 * @return array
	 */
	public function og(bool $includeImage = true): array
	{
		return [
			'og:title' => $this->getTitle(),
			'og:description' => $this->getDescription(),
			'og:image' => $includeImage ? $this->getImageURL() : null,
			'og:image:width' => $includeImage ? $this->getImageWidth() : null,
			'og:image:height' => $includeImage ? $this->getImageHeight() : null,
			'og:image:alt' => $includeImage ? $this->getImageAlt() : null,
			'og:image:type' => $includeImage ? $this->getImageType() : null,
			'og:author' => $this->getAuthor(),
			'og:url' => $this->getUrl(),
			'og:published_time' => $this->getPublishedTime(),
			'og:modified_time' => $this->getModifiedTime(),
			'og:updated_time' => $this->getUpdatedTime(),
		];
	}

	/**
	 * Get the published time of the meta.
	 *
	 * @return \DateTime|null
	 */
	public function getPublishedTime(): ?\DateTime
	{
		$date = max([$this->created_at, $this->published_time]);
		if ($date instanceof \DateTime) {
			return $date;
		}
		$date = min([$this->created_at, $this->published_time, $this->modified_time, $this->updated_time]);
		if ($date instanceof \DateTime) {
			return $date;
		}
		return null;
	}

	/**
	 * Get the modified time of the meta.
	 *
	 * @return \DateTime|null
	 */
	public function getModifiedTime(): ?\DateTime
	{
		$date = max([$this->created_at, $this->published_time, $this->modified_time, $this->updated_time]);
		return $date ?: null;
	}
	/**
	 * Get the updated time of the meta.
	 *
	 * @return \DateTime|null
	 */
	public function getUpdatedTime(): ?\DateTime
	{
		$date = max([$this->created_at, $this->published_time, $this->modified_time, $this->updated_time]);
		return $date ?: null;
	}

	/**
	 * Follows the URL and returns the final destination after all redirects.
	 *
	 * @param string|false $url
	 * @return string
	 */
	public static function getRedirectUrl($url): string|false
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, Networking::getRandomUserAgent());
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_DNS_SERVERS, "1.1.1.1");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) :
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		endif;

		curl_exec($ch);
		$info = curl_getinfo($ch);
		if (filter_var($info['url'], FILTER_VALIDATE_URL)) :
			return $info['url'];
		elseif (filter_var($url, FILTER_VALIDATE_URL)) :
			return $url;
		else :
			return false;
		endif;
	}

	/**
	 * Get the HTML content of the meta.
	 *
	 * @return string|false
	 */
	private function getHtml(): string|false
	{
		if ($this->html === null) {
			$this->html = self::loadUrl($this->url);
		}
		return $this->html;
	}

	/**
	 * Load the content of a URL.
	 *
	 * @param string $url
	 * @return string|false
	 */
	public static function loadUrl(string $url): string|false
	{
		$data = false;
		if (class_exists('\Memcache')) :
			$memcache = new \Memcache();
			$memcache->connect('localhost', 11211);
			$data = $memcache->get(md5($url));
		endif;
		//file_get_contents($url);
		if ($data === false || empty($data)) :
			if (is_callable('curl_init')) :
				try {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_USERAGENT, Networking::getRandomUserAgent());
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch, CURLOPT_DNS_SERVERS, "1.1.1.1");
					if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) :
						curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
					endif;
					$data = curl_exec($ch);
				} catch (Exception $e) {
					$data = false;
				}
			endif;
			if ($data === false || empty($data) || !is_callable('curl_init')) :
				$opts = array(
					'http' => [
						'header' => 'Connection: close'
					],
					'ssl' => [
						'verify_peer' => true,
						'verify_peer_name' => true
					]
				);
				$context = stream_context_create($opts);
				$data = file_get_contents($url, false, $context);
			endif;
		endif;
		return $data;
	}

	/**
	 * Load the meta tags from the HTML content.
	 *
	 * @return void
	 */
	private function loadMetaTags(): void
	{
		$this->html = $this->getHtml();
		if ($this->html === false) :
			return;
		endif;
		$doc = new DOMDocument();
		@$doc->loadHTML($this->html);
		$xp = new DOMXPath($doc);
		$this->title = $xp->query('//title')->item(0)->nodeValue ?? null;

		if(!$this->title) :
			foreach ($xp->query('//meta[@property="og:title"]/@content') as $el) :
				$this->title = $el->nodeValue;
			endforeach;
		endif;
		if (!$this->description) :
			foreach ($xp->query('//meta[@name="description"]/@content') as $el) :
				if (!$this->description && !empty($el->nodeValue)) :
					$this->description = $el->nodeValue;
				endif;
			endforeach;
		endif;
		if (!$this->description) :
			foreach ($xp->query('//meta[@property="og:description"]/@content') as $el) :
				if (!$this->description && !empty($el->nodeValue)) :
					$this->description = $el->nodeValue;
				endif;
			endforeach;
		endif;
		if (!$this->image) :
			foreach ($xp->query('//meta[@property="og:image"]/@content') as $el) :
				if(!empty($el->nodeValue)) :
					$this->image = $el->nodeValue;
				endif;
			endforeach;
			foreach ($xp->query('//meta[@property="og:image:secure_url"]/@content') as $el) :
				if(!empty($el->nodeValue)) :
					$this->image = $el->nodeValue;
				endif;
			endforeach;
			foreach ($xp->query('//meta[@property="og:image:alt"]/@content') as $el) :
				if(!empty($el->nodeValue)) :
					$this->image_alt = $el->nodeValue;
				endif;
			endforeach;
			foreach ($xp->query('//meta[@property="og:image:type"]/@content') as $el) :
				if(!empty($el->nodeValue)) :
					$this->image_type = $el->nodeValue;
				endif;
			endforeach;
			foreach ($xp->query('//meta[@property="og:image:width"]/@content') as $el) :
				if(!empty($el->nodeValue) && is_numeric($el->nodeValue)) :
					$this->image_width = \intval($el->nodeValue);
				endif;
			endforeach;
			foreach ($xp->query('//meta[@property="og:image:height"]/@content') as $el) :
				if(!empty($el->nodeValue) && is_numeric($el->nodeValue)) :
					$this->image_height = \intval($el->nodeValue);
				endif;
			endforeach;
		endif;

		if (!$this->published_time) :
			foreach ($xp->query('//meta[@name="article:published_time"]/@content') as $el) :
				$this->published_time = DateTime::GetDate($el->nodeValue);
			endforeach;
		endif;
		if (!$this->published_time) :
			foreach ($xp->query('//meta[@name="og:published_time"]/@content') as $el) :
				$this->published_time = DateTime::GetDate($el->nodeValue);
			endforeach;
		endif;
		if (!$this->updated_time) :
			foreach ($xp->query('//meta[@property="og:updated_time"]/@content') as $el) :
				$this->updated_time = DateTime::GetDate($el->nodeValue);
			endforeach;
		endif;
		if (!$this->modified_time) :
			foreach ($xp->query('//meta[@name="og:modified_time"]/@content') as $el) :
				$this->modified_time = DateTime::GetDate($el->nodeValue);
			endforeach;
		endif;

		if (!$this->author) :
			foreach ($xp->query('//meta[@name="author"]/@content') as $el) :
				if(!empty($el->nodeValue)) :
					$this->author = $el->nodeValue;
				endif;
			endforeach;
		endif;

		if (!$this->url) :
			foreach ($xp->query('//meta[@property="og:url"]/@content') as $el) :
				if(!empty($el->nodeValue) && filter_var($el->nodeValue, FILTER_VALIDATE_URL)) :
					$this->url = $el->nodeValue;
				endif;
			endforeach;
		endif;

		if(!$this->title) :
			$matches_title = array();
			if (preg_match('/<title>(.*?)<\/title>/', $this->html, $matches_title)) :
				$this->title = trim(explode(':', $matches_title[1])[0]);
			endif;
		endif;

		if(!$this->description) :
			$matches_description = array();
			if (preg_match('/<meta name="description" content="(.*?)"/', $this->html, $matches_description)) :
				$this->description = trim($matches_description[1]);
			endif;
		endif;
	}
}
