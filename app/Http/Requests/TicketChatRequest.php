<?php

namespace App\Http\Requests\Tenant\TenantBaseRequests;

use Illuminate\Foundation\Http\FormRequest;

class TicketChatRequest extends FormRequest
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
            'ticket_id' => 'required|numeric|exists:tickets,id',
            'created_by' => 'required|numeric|exists:users,id',
            'message' => 'required|min:3',
            'attachment' => 'nullable|file'
        ];
    }
}
