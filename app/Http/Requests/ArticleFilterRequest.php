<?php

namespace App\Http\Requests;

use App\Rules\DateRange;
use App\Rules\ValidSearchQuery;
use Illuminate\Foundation\Http\FormRequest;

class ArticleFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $minDate = now()->subYears(2)->format('Y-m-d'); // 2 years ago
        $maxDate = now()->format('Y-m-d'); // today

        return [
            'search' => ['nullable', 'string', new ValidSearchQuery(2)],
            'source_id' => 'nullable|integer|exists:sources,id',
            'category' => 'nullable|string|max:50',
            'author' => 'nullable|string|max:100',
            'date_from' => ['nullable', 'date', new DateRange($minDate, null)],
            'date_to' => ['nullable', 'date', new DateRange(null, $maxDate)],
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'source_id.exists' => 'The selected source does not exist.',
            'per_page.max' => 'You cannot request more than 100 items per page.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert query to search if it exists
        if ($this->has('query') && !$this->has('search')) {
            $this->merge([
                'search' => $this->query('query')
            ]);
        }
    }
}
