<?php

namespace Modules\Batch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchStudentsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->can('assignStudentsToBatch');
    }
}
