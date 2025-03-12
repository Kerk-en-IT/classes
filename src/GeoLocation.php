<?php

/**
 * GeoLocation
 *
 * @author     Marco van 't Klooster, Kerk en IT <info@kerkenit.nl>
 */
class GeoLocation {

	/**
	 * Formatted Address
	 *
	 * @var string
	 */
	private $address = NULL;

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
	 * @param  string $address
	 * @param  string $zipcode
	 * @param  string $city
	 * @return bool
	 */
	public function search($address, $zipcode, $city): bool
	{
		$this->address = $address . ', ' . $zipcode . ', ' . $city;
		if($this->address != '' && !empty($this->GOOGLE_MAPS_API_KEY))
		{
			$address = urlencode($this->address);

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
		} else {
			return false;
		}
	}

	/**
	 * Gets the distance between a user and a church
	 *
	 * @param  float $lat_origins Origin latitude
	 * @param  float $lng_origins Origin longitude
	 * @param  float $lat_destinations Destination latitude
	 * @param  float $lng_destinations Destination longitude
	 * @return array|bool
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
}

?>