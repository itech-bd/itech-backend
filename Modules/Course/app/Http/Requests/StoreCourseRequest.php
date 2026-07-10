<?php

namespace Modules\Course\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Course\Models\Course;

class StoreCourseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Course::normalizeSlug((string) $this->input('slug', $this->input('title', ''))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('courses', 'slug')],
            'description' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $wordCount = count(preg_split('/\s+/u', trim(strip_tags((string) $value)), -1, PREG_SPLIT_NO_EMPTY));
                    if ($wordCount > 10000) {
                        $fail('The description field must not exceed 10,000 words.');
                    }
                },
            ],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:old_price'],
            'online_old_price' => ['nullable', 'numeric', 'min:0'],
            'online_discount_price' => ['nullable', 'numeric', 'min:0', 'lte:online_old_price'],
            'offline_old_price' => ['nullable', 'numeric', 'min:0'],
            'offline_discount_price' => ['nullable', 'numeric', 'min:0', 'lte:offline_old_price'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->can('addCourse');
    }
}
