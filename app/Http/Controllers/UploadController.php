<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Models\Upload;
use App\Services\UploadService;
use Illuminate\Support\Facades\Storage;


class UploadController
{
    protected string $model = Upload::class;

    protected string $request = UploadRequest::class;

    protected array $with = ['model'];


    /**
     * Store uploaded files using the specified directory and associate them with the given model.
     *
     * @param \App\Http\Requests\UploadRequest $request The upload request object.
     * @param \App\Services\UploadService $uploader The upload service instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeFiles(UploadRequest $request, UploadService $uploader)
    {
        $data = $request->validated();
        $file = $data['files'];
        $directory = isset($data['directory']) ? $data['directory'] : null;
        $data = $uploader->publicUploader($file, $data['model_type'] ?? null, $data['model_id'] ?? null, $directory);
        $message = trans('response.created', ['object' => __('messages.files')]);
        return response()->json([
            'data' => $data,
            'message' => $message,
            'success' => true,
        ]);
    }

    /**
     * Delete the specified upload record and associated file.
     *
     * @param int $id The ID of the upload record to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $record = Upload::findOrFail($id);
        if (Storage::exists($record->path)) {
            Storage::delete($record->path);
        }
        $record->delete();
        $response['message'] = trans('response.deleted', ['object' => __('messages.files')]);
        return response()->json($response);
    }

}
