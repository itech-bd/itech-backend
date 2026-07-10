<?php

namespace Modules\Batch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchMentorsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mentor_ids' => ['required', 'array'],
            'mentor_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->can('assignMentorsToBatch');
    }
}
