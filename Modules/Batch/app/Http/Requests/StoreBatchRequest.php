<?php

namespace Modules\Batch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'class_days' => ['required', 'array', 'min:1'],
            'class_days.*' => ['required', 'string', 'in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday'],
            'class_time' => ['required', 'string', 'max:255'],
            'live_class_link' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', 'in:upcoming,running,completed'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->can('addBatch');
    }
}
