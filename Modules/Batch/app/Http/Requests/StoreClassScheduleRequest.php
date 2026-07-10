<?php

namespace Modules\Batch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassScheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'class_date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'recorded_video_link' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->can('addClassSchedule');
    }
}
