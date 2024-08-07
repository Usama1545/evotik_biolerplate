<?php

namespace App\Http\Controllers;

use App\Http\Resources\AutocompleteResource;
use App\Models\Gig;

class Controller
{
    protected string $model;
    protected string $request;
    protected $put_request = null;
    protected array $with = [];
    protected array $additions = [];
    protected $filter = null;
    protected int $paginationLength = 10;

    public function __construct()
    {
        if ($this->filter) {
            $this->filter = new $this->filter(request());
        }
    }

    public function index()
    {
        $data = $this->model::query();
        if ($this->filter) {
            $data = $data->filter($this->filter);
        }
        if ($this->with) {
            $data = $data->with($this->with);
        }
        $data = $data->paginate(request()->per_page ?? $this->paginationLength);

        if (!empty ($this->additions)) {
            $data = collect($this->additions)->mapWithKeys(function ($q, $k) {
                if (gettype($q) == 'string') {
                    $q = $q::query();
                }
                if (is_array($q)) {
                    return [
                        $k => $q
                    ];
                }
                return [
                    $k => AutocompleteResource::collection($q->get())
                ];
            })->merge($data);
        }
        return response()->json(
            $data
        );
    }

    public function store()
    {
        $data = app($this->request)->validated();

        $created = $this->model::create($data);
        $created->refresh();

        return response()->json([
            'message' => __('response.created', ['object' => __("models.{$this->model}")]),
            'data' => $created,
        ]);
    }

    public function show($id)
    {
        $record = $this->model::findOrFail($id);
        if ($this->with)
            $record->load($this->with);
        return response()->json([
            'data' => $record,
        ]);
    }


    public function update($id)
    {
        $record = $this->model::withoutGlobalScopes()->findOrFail($id);
        $data = app($this->put_request ?? $this->request)->validated();

        $record->update($data);

        return response()->json([
            'message' => __('response.updated', ['object' => __("models.{$this->model}")]),
        ]);
    }

    public function destroy($id)
    {
        $record = $this->model::withoutGlobalScopes()->findOrFail($id);
        $record->delete();

        return response()->json([
            'message' => __('response.deleted', ['object' => __("models.{$this->model}")]),
        ]);
    }
}
