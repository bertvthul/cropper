<?php

namespace Bertvthul\Cropper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CropperController extends Controller
{
    public function call(Request $request)
    {
        $modelName = request('model');
        $name = request('name');
        $quicksave = request('quicksave') ?? false;
        $model = app()->make($modelName);
        if (request('file')) {
            $imageName = $model->xhrUploadCropper();
            $cropHtml = $model->getCropperSliceHtml($name, $imageName);
        } elseif($quicksave) {
            // quicksave
            $id = request('id');
            $model->startId = (int)$id;
            $success = $model->cropAndUpload($name);
            $cropHtml = 'quicksave ' . $name . ' ' . ($success ? 'success' : 'failed');
        } else {
            // not possible
            return;
        }
   
        return response()->json([
            'success'   => true,
            'image'     => $imageName ?? '',
            'html'      => $cropHtml,
            'name'      => $name,
        ]);
    }
}