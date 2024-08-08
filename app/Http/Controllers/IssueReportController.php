<?php

namespace App\Http\Controllers;

use App\Enums\IssueReportEnum;
use App\Http\Filters\IssueReportFilter;
use App\Http\Requests\IssueReportRequest;
use App\Http\Requests\IssueReportStatusRequest;
use App\Http\Resources\IssueReportResource;
use App\Models\IssueReport;
use App\Models\Upload;
use App\Models\User;
use App\Models\Visitor;

class IssueReportController extends Controller
{
    protected string $model = IssueReport::class;

    protected string $request = IssueReportRequest::class;

    protected $filter = IssueReportFilter::class;
    protected int $paginationLength = 10;

    public function index()
    {
        $data = $this->model::query()->filter($this->filter)->with(['uploads'])->latest()->paginate(request()->per_page ?? $this->paginationLength);
        return IssueReportResource::collection($data)->additional([
            "status" => IssueReportEnum::options(),
        ]);
    }

    public function create(IssueReportRequest $request)
    {
        $data = $request->validated();

        $created = $this->model::create([
            'path' => $data['path'],
            'model_type' => auth('user')->user() instanceof User ? User::class : Visitor::class,
            'model_id' => auth()->id(),
            'issue' => $data['issue'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => IssueReportEnum::NEW,
        ]);

        if (!empty($data['screenshot_id'])) {
            Upload::where('id', $data['screenshot_id'])->update([
                'model_id' => $created->id,
                'model_type' => $this->model,
            ]);
        }

        return response()->json([
            'item' => $created,
            'message' => __('response.created', ['object' => __("models.{$this->model}")]),
        ]);
    }

    public function update_status($id, IssueReportStatusRequest $request)
    {
        $data = $request->validated();

        $issueReport = $this->model::findOrFail($id);
        $issueReport->update($data);
        return response()->json([
            'message' => __('response.updated', ['object' => __('messages.data')]),
        ]);
    }

    public function updateReport(IssueReport $issueReport)
    {
        $data = request()->validate([
            'issue' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $issueReport->update(
            array_filter($data)
        );

        return response()->json(['success' => true]);
    }
}
