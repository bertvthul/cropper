<?php
return [
	'media_library' => [
		'active' => false,
	],
	'stock_library' => [
		'active' => true,
		'providers' => [
			'pixabay' => [
				'name' => 'Pixabay',
				'api_key' => '1134851-2c788f7848d5dc368fdddb8ca',
			],
		],
	],
	'defaultMedia' => 'library',
];