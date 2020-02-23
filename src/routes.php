<?php
Route::group(['middleware' => ['web']], function () {
	Route::post('cropperxhrRequest', 'Bertvthul\Cropper\CropperController@call');
});