<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IssueReportFilter extends Filter
{
    public function status(string $value): Builder
    {
        if (!$value) return $this->builder;

        return $this->builder->where('status', $value);
    }
    public function search(string $value = null): Builder
    {
        if (!$value) return $this->builder;
        return $this->builder->where(function ($query) use ($value) {
            $query->where('uid', 'like', "%$value%")
                ->orWhere(DB::raw('lower(issue)'), 'like', '%' . strtolower($value) . '%')
                ->orWhereHas('model', function ($query) use ($value) {
                    $query->where('username', 'like', '%' . $value . '%');
                });
        });
    }
}
