<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'status' => __($this->status),
            'issue' => $this->issue,
            'description' => $this->description,
            'username' => $this->username ?? 'N/A',
            'uid' => $this->uid,
            "uploads" => $this->uploads,
            'created_at' => $this->created_at->translatedFormat('H:i Y-m-d'),
            'updated_at' => $this->updated_at->translatedFormat('H:i Y-m-d'),

        ];
    }
}
