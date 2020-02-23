<?php

namespace Bertvthul\Cropper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CropperController extends Controller
{
    public function call(Request $request)
    {
        $modelName = request('model');
        $model = app()->make($modelName);
        $imageName = $model->xhrUploadCropper();
        $name = request('name');
        $cropHtml = $model->getCropperSliceHtml($name, $imageName);
   
        return response()->json([
            'success'   => true,
            'image'     => $imageName,
            'html'      => $cropHtml,
            'name'      => $name,
        ]);
    }
}