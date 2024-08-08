<?php

namespace App\Http\Requests\Tenant\TenantBaseRequests;

use App\Models\Tenant\TenantBaseModels\User;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|required',
            'description' => 'string|required',
            'status' => 'string|required',
            'attachment' => 'nullable|file',
            'created_by' => 'nullable',
            'department' => 'string|required',
            'priority' => 'string|required',
            'opening_date' => 'date|required',
            'closing_date' => 'date|nullable',
            'closed_by' => 'nullable',
            'category' => 'required|string'
        ];
    }

    protected function prepareForValidation()
    {
        $user = User::find(auth()->id());
        $this->merge([
            'created_by' => $user->id,
        ]);


    }
}
