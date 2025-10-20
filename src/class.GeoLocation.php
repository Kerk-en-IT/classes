<?php
namespace KerkEnIT;

/**
 * GeoLocation Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage GeoLocation
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2022-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/
class GeoLocation {

	/**
	 * Formatted Address
	 *
	 * @var string
	 */
	private $_address = NULL;

	/**
	 * Google Maps API Key
	 *
	 * @var string
	 */
	private $GOOGLE_MAPS_API_KEY = NULL;

	/**
	 * Latitude
	 *
	 * @var float
	 */
	public $latitude = 0.000000;

	/**
	 * Longitude
	 *
	 * @var float
	 */
	public $longitude = 0.000000;

	/**
	 * Street
	 *
	 * @var string
	 * @deprecated please use `address` or `road` instead
	 */
	public $street = NULL;

	/**
	 * Road
	 *
	 * @var string
	 */
	public $road = NULL;

	/**
	 * Address
	 *
	 * @var string
	 */
	public $address = NULL;

	/**
	 * Zipcode
	 *
	 * @var string
	 */
	public $postalCode = NULL;
	/**
	 * City
	 *
	 * @var string
	 */
	public $city = NULL;
	/**
	 * Country
	 *
	 * @var string
	 */
	public $country = NULL;
/*
	public $street = NULL;
	public $zipcode = NULL;
	public $city = NULL;
	public $state = NULL;
	public $country = NULL;
*/

	function __construct()
	{
		$this->GOOGLE_MAPS_API_KEY = getenv('GOOGLE_MAPS_API_KEY');
	}

	/**
	 * Gets the GPS Latitude and Longitude of a given address
	 *
	 * @param	string $address
	 * @param	string $zipcode
	 * @param	string $city
	 * @param	string $country
	 * @return bool
	 */
	public function search(?string $address, ?string $zipcode = null, ?string $city = null, ?string $country = null): bool
	{
		$this->_address = trim(trim(trim(trim(($address ?? '') . ', ' . ($zipcode  !== null ? ($zipcode ?? '') . ', ' : '') . ($city ?? ''), ',')), ',')) . ($country  !== null ? ', ' . ($country ?? '') : '');
		if(!empty(($address ?? '') . ($zipcode ?? '') . ($city ?? ''))) :
			$address = urlencode($this->_address);
			if(!empty($this->GOOGLE_MAPS_API_KEY)) :

				$url = "https://maps.google.com/maps/api/geocode/json?address=".$address ."&key=".$this->GOOGLE_MAPS_API_KEY;
				ini_set('safe_mode', false);
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER,0);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$data = curl_exec($ch);
				curl_close($ch);
				ini_set('safe_mode', true);
				$geo_json = json_decode($data, true);

				$this->latitude = $geo_json['results'][0]['geometry']['location']["lat"];
				$this->longitude = $geo_json['results'][0]['geometry']['location']["lng"];

				return true;
			else :
				ini_set('safe_mode', false);
				$url = "https://nominatim.openstreetmap.org/search?q=".$address."&format=json&polygon=1&addressdetails=1";
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);

				$data = curl_exec($ch);
				curl_close($ch);
				ini_set('safe_mode', true);
				$geo_json = json_decode($data, true);
				$this->latitude = $geo_json[0]['lat'];
				$this->longitude = $geo_json[0]['lon'];
				$this->road = $geo_json[0]['address']['road'] ?? null;
				if($this->road !== null):
					$this->address = $this->road . ' ' . ($geo_json[0]['address']['house_number'] ?? '1');
				endif;
				$this->street = $this->address;
				$this->postalCode = $geo_json[0]['address']['postcode'] ?? null;
				$this->city = $geo_json[0]['address']['city'] ?? null;
				if($this->city === null) :
					//var_dump($geo_json[0]['address']);
					//die();
					$this->city = $geo_json[0]['address']["village"] ?? null;
				endif;
				$this->country = $geo_json[0]['address']['country'] ?? null;
				return true;
			endif;
		else :
			return false;
		endif;
	}


	/**
	 * Formats a latitude or longitude value to be used in a URL
	 *
	 * @param  float $value Latitude or Longitude value
	 * @return string Formatted Latitude or Longitude value
	 */
	public static function latlon(float $value): string
	{
		return str_replace(',', '.', (string)$value);
	}


	/**
	 * Format the zipcode to the correct format
	 *
	 * @param	string|null $zipcode
	 * @return	string|null
	 */
	public static function Zipcode(string|null $zipcode)
	{
		if ($zipcode === null || $zipcode === '' || empty($zipcode)) :
			return null;
		endif;
		$zipcodeStrip = preg_replace('/[^0-9a-zA-Z]/', '', $zipcode);
		if (strlen($zipcodeStrip) == 6 && !str_contains($zipcodeStrip, ' ')) :
			if (\is_numeric(substr($zipcodeStrip, 0, 4)) && !\is_numeric(substr($zipcodeStrip, 4, 2))) :
				// When the first 4 characters are numbers and the last 2 characters are numbers
				$zipcode = substr($zipcodeStrip, 0, 4) . ' ' . strtoupper(substr($zipcodeStrip, 4, 2));
			endif;
		endif;
		return $zipcode;
	}

	/**
	 * Gets the distance between a user and a church
	 *
	 * @param  float $lat_origins Origin latitude
	 * @param  float $lng_origins Origin longitude
	 * @param  float $lat_destinations Destination latitude
	 * @param  float $lng_destinations Destination longitude
	 * @return	array|bool
	 */
	public function matrix($lat_origins, $lng_origins, $lat_destinations, $lng_destinations) :array|bool
	{
		if(!empty($this->GOOGLE_MAPS_API_KEY)) :
			$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $lat_origins . ',' . $lng_origins . '&destinations=' . $lat_destinations . ',' . $lng_destinations . '&key=' . $this->GOOGLE_MAPS_API_KEY;
			ini_set('safe_mode', false);
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);

			$data = curl_exec($ch);
			curl_close($ch);
			ini_set('safe_mode', true);
			$geo_json = json_decode($data, true);
			#todo: Finish GPS distance
			return $geo_json;
		else :
			return false;
		endif;
	}



	/**
	 * Calculates the great-circle distance between two points, with the Vincenty formula.
	 *
	 * @param float $latitudeFrom Latitude of start point in [deg decimal]
	 * @param float $longitudeFrom Longitude of start point in [deg decimal]
	 * @param float $latitudeTo Latitude of target point in [deg decimal]
	 * @param float $longitudeTo Longitude of target point in [deg decimal]
	 * @param float $earthRadius Mean earth radius in [m]
	 * @return float Distance between points in [m] (same as earthRadius)
	 */
	public static function vincentyGreatCircleDistance(
		$latitudeFrom,
		$longitudeFrom,
		$latitudeTo,
		$longitudeTo,
		$earthRadius = 6371000
	) {
		// convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);

		$lonDelta = $lonTo - $lonFrom;
		$a = pow(cos($latTo) * sin($lonDelta), 2) +
			pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

		$angle = atan2(sqrt($a), $b);
		return $angle * $earthRadius;
	}


	/**
	 * Calculates the great-circle distance between two points, with
	 * the Haversine formula.
	 * @param float $latitudeFrom Latitude of start point in [deg decimal]
	 * @param float $longitudeFrom Longitude of start point in [deg decimal]
	 * @param float $latitudeTo Latitude of target point in [deg decimal]
	 * @param float $longitudeTo Longitude of target point in [deg decimal]
	 * @param float $earthRadius Mean earth radius in [m]
	 * @return float Distance between points in [m] (same as earthRadius)
	 */
	public static function haversineGreatCircleDistance(
		$latitudeFrom,
		$longitudeFrom,
		$latitudeTo,
		$longitudeTo
	) {
		$theta = $longitudeFrom - $longitudeTo;
		$distance = (sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo))) + (cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta)));
		$distance = acos($distance);
		$distance = rad2deg($distance);
		$distance = $distance * 60 * 1.1515;

		$distance = $distance * 1.609344;
		$distance = $distance * 1000;
		return $distance;
	}

	/**
	 * Returns the center latitude and longitude of a set of coordinates.
	 *
	 * @param array $coordinates Array of objects with latitude and longitude properties.
	 * @return array|null Array with center latitude and longitude or null if no coordinates are provided.
	 */
	public static function getCenterLatLng($coordinates)
	{
		$latitudes = array_map(function ($coordinate) {
			return $coordinate->latitude;
		}, $coordinates);
		if (count($latitudes) > 0) :
			$min_latitude = min($latitudes);
			$max_latitude = max($latitudes);

			$longitudes = array_map(function ($coordinate) {
				return $coordinate->longitude;
			}, $coordinates);

			$min_longitude = min($longitudes);
			$max_longitude = max($longitudes);

			$x = $y = $z = 0;
			$n = count($coordinates);
			foreach ($coordinates as $point) {
				$lt = $point->latitude * pi() / 180;
				$lg = $point->longitude * pi() / 180;
				$x += cos($lt) * cos($lg);
				$y += cos($lt) * sin($lg);
				$z += sin($lt);
			}
			$x /= $n;
			$y /= $n;

			return [atan2(($z / $n), sqrt($x * $x + $y * $y)) * 180 / pi(), atan2($y, $x) * 180 / pi(), self::haversineGreatCircleDistance($min_latitude, $min_longitude, $max_latitude, $max_longitude)];
		else :
			return null;
		endif;
	}
}

?>