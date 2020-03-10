<?php

namespace Bertvthul\Cropper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CropperController extends Controller
{
    public function call(Request $request)
    {
        $success = true;
        $requestType = request('type') ?? 'upload';
        $name = request('name');
        $modelName = request('model');
        $model = app()->make($modelName);

        if ($requestType == 'initModal') {
             $html = $model->getCropperModal($name);
        } elseif ($requestType == 'upload') {

            $quicksave = request('quicksave') ?? false;
            if (request('file')) {
                $imageName = $model->xhrUploadCropper();
                $html = $model->getCropperSliceHtml($name, $imageName);
            } elseif($quicksave) {
                // quicksave
                $id = request('id');
                $model->startId = (int)$id;
                $success = $model->cropAndUpload($name);
                $html = 'quicksave ' . $name . ' ' . ($success ? 'success' : 'failed');
            } else {
                // not possible
                return;
            }
        }
   
        return response()->json([
            'success'   => $success,
            'image'     => $imageName ?? '',
            'html'      => $html,
            'name'      => $name,
        ]);
    }
}