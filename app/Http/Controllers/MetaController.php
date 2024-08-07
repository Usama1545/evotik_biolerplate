<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetaRequest;
use App\Models\Meta;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MetaController extends Controller
{

    protected string $model = Meta::class;

    protected string $request = MetaRequest::class;

    public function store()
    {
        $data = app($this->request)->validated();

        $created = $this->model::create($data);
        if(isset($data['upload'])) {
            $this->storeMetaUpload($data['upload'], $created);
        }
        return response()->json([
            'message' => __('response.created', ['object' => __("models.{$this->model}")]),
            'data' => $created,
        ]);
    }

    public function storeMetaUpload(array $uploads, Model $created)
    {
        if (count($uploads)) {
            foreach ($uploads as $img) {
                if (!empty($img['upload_id'])) {
                    Upload::where('id', $img['upload_id'])->update(['model_id' => $created->id, 'model_type' => $this->model]);
                }
            }
        }
    }
}
