<?php

namespace Bertvthul\Cropper;

class PixabayMedia {

	private static $api_key;
	private static $api_route = 'https://pixabay.com/api/';
	private static $client;
	private static $options = [];

	public function __construct()
	{
		// get params from config
	}

	public static function findImages(string $q)
	{
		self::$api_key = \Config::get('cropper.stock_library.providers.pixabay.api_key');

		$query = http_build_query(['key' => self::$api_key, 'lang' => 'nl', 'q' => $q]);
		$url = self::$api_route . '?' . $query;
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
		]);

		if (!$response = curl_exec($curl)) {
		    dd('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
		}
		$images = json_decode($response, true);
		curl_close($curl);

		return $images;
	}

	public static function listImages(string $q) 
	{
		$images = self::findImages($q);

		if (empty($images)) {
			return;
		}

		$r = '';
		foreach($images['hits'] as $image) {
			$r .= '<img src="' . $image['previewURL'] . '" width="' . $image['previewWidth'] . '" height="' . $image['previewHeight'] . '">';
		}

		return $r;
	}
}