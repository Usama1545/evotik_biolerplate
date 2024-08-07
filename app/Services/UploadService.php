<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class UploadService
{

    /**
     * store files or download & store urls
     */
    public function publicUploader($file, $model = null, $modelId = null, $directory = null)
    {
        $data['model_type'] = $model;
        $data['model_id'] = $modelId;

        if ($file instanceof UploadedFile) {
            $data['name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getClientMimeType();
            $data['path'] = Storage::put($directory, $file);
        } else {
            $file_content = file_get_contents($file);
            $data['path'] = $directory . "/" . uniqid() . '.jpg';
            Storage::put($data['path'], $file_content);
        }

        if (!App::environment('local')) {
            ImageOptimizer::optimize(storage_path('app/public/' . $data['path'])); //replaces origin with compressed
        }

        return Upload::create($data);
    }
}
