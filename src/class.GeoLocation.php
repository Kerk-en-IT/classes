<?php

namespace KerkEnIT;

/**
 * GeoLocation Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4, 8.5
 *
 * @package    KerkEnIT
 * @subpackage GeoLocation
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2022-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/
class GeoLocation
{

	/**
	 * Formatted Address
	 *
	 * @var string
	 */
	private ?string $_address = NULL;

	/**
	 * Latitude
	 *
	 * @var float
	 */
	public float $latitude = 0.000000;

	/**
	 * Longitude
	 *
	 * @var float
	 */
	public float $longitude = 0.000000;

	/**
	 * Street
	 *
	 * @var string
	 * @deprecated please use `address` or `road` instead
	 */
	public ?string $street = NULL;

	/**
	 * Road
	 *
	 * @var string
	 */
	public ?string $road = NULL;

	/**
	 * Address
	 *
	 * @var string
	 */
	public ?string $address = NULL;

	/**
	 * Zipcode
	 *
	 * @var string
	 */
	public ?string $postalCode = NULL;
	/**
	 * City
	 *
	 * @var string
	 */
	public ?string $city = NULL;
	/**
	 * Country
	 *
	 * @var string
	 */
	public ?string $country = NULL;

	/**
	 * Constructor
	 */
	public function __construct()
	{
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
		if (!empty(($address ?? '') . ($zipcode ?? '') . ($city ?? ''))) :
			$address = urlencode($this->_address);
			ini_set('safe_mode', false);
			$url = "https://nominatim.openstreetmap.org/search?q=" . $address . "&format=json&polygon=1&addressdetails=1";
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);

			$data = curl_exec($ch);
			ini_set('safe_mode', true);
			$geo_json = json_decode($data, true);
			if (!is_array($geo_json) || !isset($geo_json[0])) :
				return false;
			endif;

			$this->latitude = (float)($geo_json[0]['lat'] ?? 0.0);
			$this->longitude = (float)($geo_json[0]['lon'] ?? 0.0);
			$this->road = $geo_json[0]['address']['road'] ?? null;
			if ($this->road !== null):
				$this->address = $this->road . ' ' . ($geo_json[0]['address']['house_number'] ?? '1');
			endif;
			$this->street = $this->address;
			$this->postalCode = $geo_json[0]['address']['postcode'] ?? null;
			$this->city = $geo_json[0]['address']['city'] ?? null;
			if ($this->city === null) :
				$this->city = $geo_json[0]['address']["village"] ?? null;
			endif;
			$this->country = $geo_json[0]['address']['country'] ?? null;
			return true;
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
	public static function Zipcode(string|null $zipcode): ?string
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
	public function matrix(float $lat_origins, float $lng_origins, float $lat_destinations, float $lng_destinations): array|bool
	{
		if (!is_numeric($lat_origins) || !is_numeric($lng_origins) || !is_numeric($lat_destinations) || !is_numeric($lng_destinations)) :
			return false;
		endif;

		$distanceMeters = (int)round(self::haversineGreatCircleDistance(
			latitudeFrom:(float)$lat_origins,
			longitudeFrom:(float)$lng_origins,
			latitudeTo:(float)$lat_destinations,
			longitudeTo:(float)$lng_destinations
		));
		$durationSeconds = (int)round((($distanceMeters / 1000) / 50) * 3600);
		$durationMinutes = max(1, (int)round($durationSeconds / 60));
		$distanceText = $distanceMeters >= 1000
			? number_format($distanceMeters / 1000, 1, ',', '.') . ' km'
			: $distanceMeters . ' m';

		return [
			'destination_addresses' => [self::latlon((float)$lat_destinations) . ',' . self::latlon((float)$lng_destinations)],
			'origin_addresses' => [self::latlon((float)$lat_origins) . ',' . self::latlon((float)$lng_origins)],
			'rows' => [[
				'elements' => [[
					'distance' => [
						'text' => $distanceText,
						'value' => $distanceMeters,
					],
					'duration' => [
						'text' => $durationMinutes . ' mins',
						'value' => $durationSeconds,
					],
					'status' => 'OK',
				]],
			]],
			'status' => 'OK',
		];
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
		float $latitudeFrom,
		float $longitudeFrom,
		float $latitudeTo,
		float $longitudeTo,
		float $earthRadius = 6371000
	): float {
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
	 * @return float Distance between points in [m] (same as earthRadius)
	 */
	public static function haversineGreatCircleDistance(
		float $latitudeFrom,
		float $longitudeFrom,
		float $latitudeTo,
		float $longitudeTo
	): float {
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
	public static function getCenterLatLng(array $coordinates): ?array
	{
		$latitudes = array_map(function ($coordinate): mixed {
			return $coordinate->latitude;
		}, $coordinates);
		if (count($latitudes) > 0) :
			$min_latitude = min($latitudes);
			$max_latitude = max($latitudes);

			$longitudes = array_map(function ($coordinate): mixed {
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
