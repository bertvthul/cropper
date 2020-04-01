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

        if ($requestType == 'imageUrlUpload') {
            $imageUrl = request('imageUrl');
            $imageName = $model->uploadCropperImageByUrl($imageUrl);
            $html = $model->getCropperSliceHtml($name, $imageName);
        } elseif ($requestType == 'imagePathUpload') {
            $imagePath = request('imagePath');
            $imageName = $model->uploadCropperImageByPath($imagePath);
            $html = $model->getCropperSliceHtml($name, $imageName);
        } elseif ($requestType == 'stockSearch') {
            $searchTerm = request('q');
            $images = PixabayMedia::findImages($searchTerm);

            $html = view('cropper::modal_stock_list')->withImages($images)->withSearchTerm($searchTerm)->render();
        } elseif ($requestType == 'initModal') {
            $html = $model->getCropperModal($name, $modelName);
         } elseif ($requestType == 'modalNav') {
            $content = request('content');
            $html = $model->getModalTabContent($content, $name);
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